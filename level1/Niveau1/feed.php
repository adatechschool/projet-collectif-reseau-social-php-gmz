<?php
session_start();

$sessionId = $_SESSION["connected_id"];
echo "<pre>" . print_r($_SESSION, 1) . "</pre>";

if (!$sessionId) {
    header('Location: ./login.php');
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
            <a href="wall.php?user_id=5">Mur</a>
            <a href="feed.php?user_id=5">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=5">Paramètres</a></li>
                <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
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
            /**
             * Etape 3: récupérer tous les messages des abonnements
             */
            $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
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
            ?>
                <article>
                    <h3>
                        <time><?php echo $feed['created'] ?></time>
                    </h3>
                    <address>par <a href="./wall.php?user_id=<?php echo $authorId ?>"><?php echo $feed['author_name'] ?></a></address>
                    <div>
                        <p><?php echo $feed['content'] ?></p>
                    </div>
                    <footer>
                        <small>♥ <?php echo $feed['like_number'] ?></small>
                        <?php
                        $newtagidlist = explode(",", $feed['tagidlist']);
                        $newtaglist = explode(",", $feed['taglist']);


                        if (count($newtagidlist) > 1) {
                            for ($i = 0; $i < count($newtagidlist); $i++) {

                        ?>
                                <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>"><?php
                                                                                            echo '#' . $newtaglist[$i]  ?></a><?php
                                                                                                                            }
                                                                                                                        } elseif (strlen($newtagidlist[0]) == 1) {
                                                                                                                                ?>
                            <a href="./tags.php?tag_id=<?php echo $newtagidlist[0] ?>"><?php echo '#' . $newtaglist[0] ?></a><?php
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