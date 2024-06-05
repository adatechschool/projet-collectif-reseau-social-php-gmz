<?php
session_start();

if (!isset($_SESSION["connected_id"])) {
    header('Location: ./login.php');
    exit();
} else {
    $sessionId = $_SESSION["connected_id"];
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Flux</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social" />
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php">Mur</a>
            <a href="feed.php">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="followers.php">Mes suiveurs</a></li>
                <li><a href="subscriptions.php">Mes abonnements</a></li>
            </ul>

        </nav>
    </header>
    <div id="wrapper">
        <?php
        /**
         * Cette page est TRES similaire à wall.php. 
         * Vous avez sensiblement à y faire la meme chose.
         * Il y a un seul point qui change c'est la requete sql.
         */
        /**
         * Etape 1: Le mur concerne un utilisateur en particulier
         */
        $userId = intval($_GET['user_id']);
        if ((!$userId || $userId != $sessionId)  && $sessionId) {
            Header("Location: ./feed.php?user_id=$sessionId");
            exit();
        }
        ?>
        <?php
        /**
         * Etape 2: se connecter à la base de donnée
         */
        //$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
        include 'sqlConnection.php';
        ?>

        <aside>
            <?php
            /**
             * Etape 3: récupérer le nom de l'utilisateur
             */
            $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            $user = $lesInformations->fetch_assoc();
            //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
            //echo "<pre>" . print_r($user, 1) . "</pre>";
            ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les message des utilisatrices
                    auxquel est abonnée l'utilisatrice <?php echo $user['alias'] ?>
                    (n° <?php echo $userId ?>)
                </p>

            </section>
        </aside>
        <main>
            <?php
            include './scripts.php';
            /**
             * Etape 3: récupérer tous les messages des abonnements
             */
            $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    posts.id as message_id,
                    users.id,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.id ORDER BY tags.id ASC) AS tagidlist,
                    GROUP_CONCAT(DISTINCT tags.label ORDER BY tags.id ASC) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
            }
            while ($feed = $lesInformations->fetch_assoc()) {
                //echo "<pre>" . print_r($feed, 1) . "</pre>";
                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 * A vous de retrouver comment faire la boucle while de parcours...
                 */
                $authorId = $feed["id"];
                $dateFr = createDate($feed['created'])
            ?>
                <article>
                    <h3>
                        <time><?php echo $dateFr ?></time>
                    </h3>
                    <address>par <a href="./wall.php?user_id=<?php echo $authorId ?>"><?php echo $feed['author_name'] ?></a></address>
                    <div>
                        <p><?php echo $feed['content'] ?></p>
                    </div>
                    <footer>
                    <?php
                        $messageid = $feed['message_id'];
                        $newtagidlist = explode(",", $feed['tagidlist'] ?? '');
                        $newtaglist = explode(",", $feed['taglist'] ?? '');

                        $divide =  count($newtagidlist);
                        if (count($newtagidlist)==0){
                            $divide =1;
                        }
                        $feed['like_number'] = intval($feed['like_number']) / $divide;

                        if (isset($_POST['unlike' . $messageid])) {
                            $feed['like_number'] = $feed['like_number'] - 1;
                        } elseif (isset($_POST['like' . $messageid])) {
                            $feed['like_number'] = $feed['like_number'] + 1;
                        }
                        ?>

                        <small>♥ <?php echo $feed['like_number'] ?></small>
                        <?php
                        echo "<pre>" . print_r($messageid, 1) . "</pre>";

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

                        // Vérifier si l'utilisateur a liké le post
                        $esketulike = "SELECT * FROM likes WHERE post_id='$messageid' AND user_id='$sessionId';";
                        $likes = $mysqli->query($esketulike);
                        echo "<pre>" . print_r($likes, 1) . "</pre>";

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
            } // et de pas oublier de fermer ici vote while
            ?>


        </main>
    </div>
</body>

</html>