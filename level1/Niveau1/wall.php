<?php
session_start();
$_SESSION["connected_id"] = 5;
$sessionId = $_SESSION["connected_id"];
echo "<pre>" . print_r($_SESSION, 1) . "</pre>";
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title>
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
         * Etape 1: Le mur concerne un utilisateur en particulier
         * La première étape est donc de trouver quel est l'id de l'utilisateur
         * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
         * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
         * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
         */
        $userId = intval($_GET['user_id']);
        ?>
        <?php
        /**
         * Etape 2: se connecter à la base de donnée
         */
        //$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
        include './sqlConnection.php';
        ?>

        <aside>
            <?php
            /**
             * Etape 3: récupérer le nom de l'utilisateur
             */
            $laQuestionEnSql = "SELECT users.alias FROM users WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            $user = $lesInformations->fetch_assoc();
            //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
            // echo "<pre>" . print_r($user, 1) . "</pre>";
            ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $user['alias'] ?>
                    (n° <?php echo $userId ?>)
                </p>
            </section>


            <?php

            if ($userId == $sessionId) {
            ?>

                <!-- Formulaire pour écrire un message sur son propre mur -->
                <article>
                    <h2>Poster un message</h2>
                    <?php


                    /**
                     * BD
                     */
                    include './sqlConnection.php';
                    // $mysqli = new mysqli("localhost", "root", "root", "socialnetwork_tests");


                    /**
                     * Récupération de la liste des auteurs
                     */
                    $listAuteurs = [];
                    $laQuestionEnSql = "SELECT * FROM users";
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                    while ($user = $lesInformations->fetch_assoc()) {
                        $listAuteurs[$user['id']] = $user['alias'];
                    }


                    /**
                     * TRAITEMENT DU FORMULAIRE
                     */
                    // Etape 1 : vérifier si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs auteur rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['auteur']);
                    if ($enCoursDeTraitement) {
                        // on ne fait ce qui suit que si un formulaire a été soumis.


                        // Etape 2: récupérer ce qu'il y a dans le formulaire @todo: c'est là que votre travaille se situe
                        // observez le résultat de cette ligne de débug (vous l'effacerez ensuite)
                        echo "<pre>" . print_r($_POST, 1) . "</pre>";
                        // et complétez le code ci dessous en remplaçant les ???

                        // ==== Changement de author_id par l'ID de la SESSION en cours 
                        // => La table 'posts' attribue bien le message à l'utilisateur de la SESSION
                        // $authorId = $_POST['auteur'];
                        $authorId = $_SESSION['connected_id'];
                        $_SESSION['connected_id'] = 5;
                        $postContent = $_POST['message'];


                        //Etape 3 : Petite sécurité
                        // pour éviter les injection sql : https://www.w3schools.com/sql/sql_injection.asp
                        $authorId = intval($mysqli->real_escape_string($authorId));
                        $postContent = $mysqli->real_escape_string($postContent);


                        //Etape 4 : construction de la requete
                        $lInstructionSql = "INSERT INTO posts "
                            . "(id, user_id, content, created) "
                            . "VALUES (NULL, "
                            . $authorId . ", "
                            . "'" . $postContent . "', "
                            . "NOW()
                            );";
                        // echo $lInstructionSql;


                        // Etape 5 : execution
                        $ok = $mysqli->query($lInstructionSql);
                        if (!$ok) {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else {
                            echo "Message posté en tant que :" . $listAuteurs[$authorId];
                        }
                    }
                    ?>
                    <form action="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>" method="post">
                        <input type='hidden'>
                        <dl>
                            <dt><label for='auteur'>Auteur</label></dt>
                            <dd><select name='auteur'>
                                    <?php
                                    foreach ($listAuteurs as $id => $alias)
                                        echo "<option value='$id'>$alias</option>";
                                    ?>
                                </select></dd>
                            <dt><label for='message'>Message</label></dt>
                            <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                </article>

            <?php } else {

                // ==== Si on est PAS abonné =>

                echo "<pre>" . print_r("abonne toi", 1) . "</pre>";
                // ==== Faire un bouton pour s'abonner 
                // => Doit faire un post en DB

                // ==== Si on EST abonné =>
                // == Faire un bouton pour se désabonné
                // => Doit faire un put en DB ?

            }


            ?>



        </aside>
        <main>
            <?php
            /**
             * Etape 3: récupérer tous les messages de l'utilisatrice (+ ajout de posts.id)
             */
            $laQuestionEnSql = "
                    SELECT posts.id, posts.content, posts.created, users.alias as author_name, 
                    COUNT(likes.id) as like_number, 
                    GROUP_CONCAT(DISTINCT tags.id ORDER BY tags.id ASC) AS tagidlist,
                    GROUP_CONCAT(DISTINCT tags.label ORDER BY tags.id ASC) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
            }

            /**
             * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
             */
            while ($post = $lesInformations->fetch_assoc()) {

            ?>
                <article>
                    <h3>
                        <time><?php echo $post['created'] ?></time>
                    </h3>
                    <address><?php echo $post['author_name'] ?></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                    </div>
                    <footer>
                        <small>♥ <?php echo $post['like_number'] ?></small>
                        <?php
                        $messageid = $post['id'];
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
                        $esketulike = "SELECT * FROM likes WHERE post_id='$post[id]' AND user_id='$sessionId';";
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


                        $newtagidlist = explode(",", $post['tagidlist']);
                        $newtaglist = explode(",", $post['taglist']);


                        if (count($newtagidlist) > 1) {
                            for ($i = 0; $i < count($newtagidlist); $i++) {
                        ?>
                                <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>"><?php
                                                                                            // $hashtag = str_replace(',', ', #', $newtaglist[$i]);
                                                                                            echo '#' . $newtaglist[$i]  ?></a><?php
                                                                                                                            }
                                                                                                                        } elseif (strlen($newtagidlist[0]) == 1) {
                                                                                                                                ?>
                            <a href="./tags.php?tag_id=<?php echo $newtagidlist[0] ?>"><?php echo '#' . $newtaglist[0] ?></a><?php
                                                                                                                            }
                                                                                                                                ?>
                    </footer>
                </article>
            <?php } ?>


        </main>
    </div>
</body>

</html>