<?php
session_start();
// $_SESSION["connected_id"] = 5;
// unset($_SESSION["connected_id"]);
echo "<pre>" . print_r($_SESSION, 1) . "</pre>";

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
    <title>ReSoC - Mur</title>
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
        // Connexion à la base de données
        include './sqlConnection.php';

        // Récupérer l'ID de l'utilisateur dont le mur est affiché
        $userId = intval($_GET['user_id']);
        if (!$userId && $sessionId) {
            Header("Location: ./wall.php?user_id=$sessionId");
            exit();
        }
        /**
         * Etape 2: se connecter à la base de donnée
         */
        //$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
        include './sqlConnection.php';

        // ==== Recupération de l'alias vis à vis de l'id dans l'URL
        $laQuestionEnSql = "SELECT alias FROM users WHERE id=$userId";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>

        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $user['alias'] ?>
                    (n° <?php echo $userId ?>)
                </p>
            </section>


            <?php

            if ($userId != $sessionId) {
                if (isset($_POST['subscribe'])) {
                    // Ajouter un abonnement
                    $ajoutFollowersSql = "INSERT INTO followers (id, followed_user_id, following_user_id) 
                                            VALUES (NULL, $userId, '$sessionId')";
                    if (!$mysqli->query($ajoutFollowersSql)) {
                        echo "Erreur lors de l'ajout de l'abonnement: " . $mysqli->error;
                    }
                } elseif (isset($_POST['unsubscribe'])) {
                    // Supprimer un abonnement
                    $suppressionFollowersSql = "DELETE FROM followers 
                                                WHERE followed_user_id = $userId 
                                                AND following_user_id = $sessionId";
                    if (!$mysqli->query($suppressionFollowersSql)) {
                        echo "Erreur lors de la suppression de l'abonnement: " . $mysqli->error;
                    }
                }

                // Vérifier si l'utilisateur est abonné
                $subscriptionsQuestion = "
                    SELECT users.*
                    FROM followers 
                    LEFT JOIN users ON users.id=followers.followed_user_id 
                    WHERE followers.following_user_id='$sessionId'
                    AND followers.followed_user_id='$userId'
                ";
                $InfoSubscriptions = $mysqli->query($subscriptionsQuestion);

                if ($InfoSubscriptions->num_rows == 0) {
            ?>
                    <form method="post" action="">
                        <input type="hidden" name="subscribe" value="true">
                        <button type="submit" id="subscribeButton" class="subscribe">S'abonner</button>
                    </form>
                <?php
                } else {
                ?>
                    <form method="post" action="">
                        <input type="hidden" name="unsubscribe" value="true">
                        <button type="submit" id="subscribeButton" class="unsubscribe">Se désabonner</button>
                    </form>
                <?php

                }
            }

            if ($userId == $sessionId) {
                ?>
                <!-- Formulaire pour écrire un message sur son propre mur -->
                <article>
                    <h2>Poster un message</h2>

                    <?php

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
                    $enCoursDeTraitement = isset($_POST['message']);
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

                        // ==== Appel function pour POST la relation tag et post id
                        include("./scripts.php");
                        $queryIdFromPost = "
                        SELECT id FROM posts WHERE posts.content= '$postContent'
                        ";
                        $status = $mysqli->query($queryIdFromPost);
                        $response = $status->fetch_assoc();
                        $idFromPost = $response["id"];

                        echo "<pre>" . print_r($idFromPost, 1) . "</pre>";

                        // === Function qui scan le contenu et met à jour la DB tags
                        // == Avec relation posts_tags
                        detectTags($postContent, $idFromPost);
                    }
                    ?>
                    <form action="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>" method="post">
                        <input type='hidden'>
                        <dl>
                            <dt><label for='message'>Message</label></dt>
                            <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>


                <?php

            }
                ?>
                </article>
        </aside>

        <main>

            <?php

            /**
             * Etape 3: récupérer tous les messages de l'utilisatrice
             */
            $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, 
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
                echo "<pre>" . print_r($post, 1) . "</pre>";

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
                        $newtagidlist = explode(",", $post['tagidlist'] ?? '');
                        $newtaglist = explode(",", $post['taglist'] ?? '');

                        echo "<pre>" . print_r($newtagidlist, 1) . "</pre>";
                        echo "<pre>" . print_r($newtaglist, 1) . "</pre>";

                        if (count($newtagidlist) > 1) {
                            for ($i = 0; $i < count($newtagidlist); $i++) {
                        ?>
                                <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>">
                                    <?php
                                    // $hashtag = str_replace(',', ', #', $newtaglist[$i]);
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
            <?php } ?>


        </main>
    </div>
</body>

</html>