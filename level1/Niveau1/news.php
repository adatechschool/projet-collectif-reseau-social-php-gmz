<?php
session_start();

if (isset($_SESSION["connected_id"])) {
    $sessionId = $_SESSION["connected_id"];
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <a href='admin.php'><img src="resoc.jpg" alt="Logo de notre réseau social" /></a>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php">Mur</a>
            <a href="feed.php">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">▾ Profil</a>
            <ul>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="followers.php">Mes suiveurs</a></li>
                <li><a href="subscriptions.php">Mes abonnements</a></li>
            </ul>
        </nav>
    </header>
    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les derniers messages de
                    tous les utilisatrices du site.</p>
            </section>
        </aside>
        <main>
            <!-- L'article qui suit est un exemple pour la présentation et 
                  @todo: doit etre retiré -->


            <?php
            include './scripts.php';
            /*
                  // C'est ici que le travail PHP commence
                  // Votre mission si vous l'acceptez est de chercher dans la base
                  // de données la liste des 5 derniers messsages (posts) et
                  // de l'afficher
                  // Documentation : les exemples https://www.php.net/manual/fr/mysqli.query.php
                  // plus généralement : https://www.php.net/manual/fr/mysqli.query.php
                 */

            // Etape 1: Ouvrir une connexion avec la base de donnée.
            //$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
            include 'sqlConnection.php';
            //verification
            if ($mysqli->connect_errno) {
                echo "<article>";
                echo ("Échec de la connexion : " . $mysqli->connect_error);
                echo ("<p>Indice: Vérifiez les parametres de <code>new mysqli(...</code></p>");
                echo "</article>";
                exit();
            }

            // Etape 2: Poser une question à la base de donnée et récupérer ses informations
            // cette requete vous est donnée, elle est complexe mais correcte, 
            // si vous ne la comprenez pas c'est normal, passez, on y reviendra
            // ajout de la ligne 89 (users.id) dans la requête pour les utliser dans les balises <a>
            $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    posts.id as message_id,
                    users.id,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.id ORDER BY tags.id ASC) AS tagidlist,
                    GROUP_CONCAT(DISTINCT tags.label ORDER BY tags.id ASC) AS taglist  
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    LIMIT 5
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            // Vérification
            if (!$lesInformations) {
                echo "<article>";
                echo ("Échec de la requete : " . $mysqli->error);
                echo ("<p>Indice: Vérifiez la requete  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
                exit();
            }

            // Etape 3: Parcourir ces données et les ranger bien comme il faut dans du html
            // NB: à chaque tour du while, la variable post ci dessous reçoit les informations du post suivant.
            while ($post = $lesInformations->fetch_assoc()) {
                //la ligne ci-dessous doit etre supprimée mais regardez ce 
                //qu'elle affiche avant pour comprendre comment sont organisées les information dans votre 
                

                // @todo : Votre mission c'est de remplacer les AREMPLACER par les bonnes valeurs
                // ci-dessous par les bonnes valeurs cachées dans la variable $post 
                // on vous met le pied à l'étrier avec created
                // 
                // avec le ? > ci-dessous on sort du mode php et on écrit du html comme on veut... mais en restant dans la boucle
                $authorId = $post["id"];

                $dateFr = createDate($post['created'])

            ?>
                <article>
                    <h3>
                        <time><?php echo $dateFr ?></time>
                    </h3>
                    <address><a href="./wall.php?user_id=<?php echo $authorId ?>"><?php echo $post['author_name'] ?></a></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                    </div>
                    <footer>

                        <?php
                        $messageid = $post['message_id'];
                        $newtagidlist = explode(",", $post['tagidlist'] ?? '');
                        $newtaglist = explode(",", $post['taglist'] ?? '');

                        $divide =  count($newtagidlist);
                        if (count($newtagidlist)==0){
                            $divide =1;
                        }
                        $post['like_number'] = intval($post['like_number']) / $divide;

                        if (isset($_POST['unlike' . $messageid])) {
                            $post['like_number'] = $post['like_number'] - 1;
                        } elseif (isset($_POST['like' . $messageid])) {
                            $post['like_number'] = $post['like_number'] + 1;
                        }
                        ?>

                        <small>♥ <?php echo $post['like_number'] ?></small>
                        <?php
                        if (isset($_POST['like' . $messageid])) {
                            // Ajouter un like
                            $ajoutLikeSql = "INSERT INTO likes (id, user_id, post_id) VALUES (NULL, $sessionId, $messageid)";
                            if (!$mysqli->query($ajoutLikeSql)) {
                                echo "Erreur lors de l'ajout du like: " . $mysqli->error;
                            }
                        } elseif (isset($_POST['unlike' . $messageid])) {
                            // Supprimer un like
                            $suppressionLikeSql = "DELETE FROM likes WHERE user_id = $sessionId AND post_id = $messageid";
                            if (!$mysqli->query($suppressionLikeSql)) {
                                echo "Erreur lors de la suppression du like: " . $mysqli->error;
                            }
                        }
                    if (isset($_SESSION['connected_id'])) {
                        // Vérifier si l'utilisateur a liké le post
                        $esketulike = "SELECT * FROM likes WHERE post_id='$messageid' AND user_id='$sessionId';";
                        $likes = $mysqli->query($esketulike);

                        if ($likes->num_rows == 0) {
                        ?>
                            <form method="post" action="">
                                <input type="hidden" name="like<?php echo $messageid ?>" value="true">
                                <button type="submit" id="likeButton" class="like">Like</button>
                            </form>
                        <?php
                        } else {
                        ?>
                            <form method="post" action="">
                                <input type="hidden" name="unlike<?php echo $messageid ?>" value="true">
                                <button type="submit" id="likeButton" class="unlike">Unlike</button>
                            </form>
                            <?php
                        }
                    } // Fermeture accolade session active

                        if (count($newtagidlist) > 1) {
                            for ($i = 0; $i < count($newtagidlist); $i++) {
                            ?>
                                <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>">
                                    <?php
                                    echo '#' . $newtaglist[$i]  ?></a>
                            <?php
                            }
                        } elseif (strlen($newtagidlist[0]) >= 1) {
                            ?>
                            <a href="./tags.php?tag_id=<?php echo $newtagidlist[0] ?>"><?php echo '#' . $newtaglist[0] ?></a>
                        <?php
                        }
                        ?>
                    </footer>
                </article>
            <?php
                // avec le <?php ci-dessus on retourne en mode php 
            } // cette accolade ferme et termine la boucle while ouverte avant.
            ?>

        </main>
    </div>
</body>

</html>