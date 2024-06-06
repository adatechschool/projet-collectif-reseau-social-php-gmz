<?php
/* GESTION DE SESSION */
session_start();

if (!isset($_SESSION["connected_id"])) {
    header('Location: ./login.php');
    exit();
} else {
    $sessionId = $_SESSION["connected_id"];
    if ($sessionId != 8/* ID DE L ADMIN*/) {
        header('Location: ./wall.php');
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Administration</title>
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

    <?php
    /* CONNEXION A LA BASE DE DONNEES */
    include 'sqlConnection.php';
    // -- Vérification de la connexion --
    if ($mysqli->connect_errno) {
        echo ("Échec de la connexion : " . $mysqli->connect_error);
        exit();
    }
    ?>
    <div id="wrapper" class='admin'>
        <aside>
            <h2>Mots-clés</h2>
            <?php
            /* REQUETE SUR LES MOTS CLES */
            $laQuestionEnSql = "SELECT * FROM `tags` LIMIT 50";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            // -- Vérification de la requête --
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
                exit();
            }

            /* AFFICHAGE DES MOTS CLES */
            while ($tag = $lesInformations->fetch_assoc()) {
                $tagId = $tag["id"];
            ?>
                <article>
                    <a href="./tags.php?tag_id=<?php echo $tagId ?>">
                        <h3>#<?php echo $tag['label'] ?></h3>
                        <p>id:<?php echo $tag['id'] ?></p>
                    </a>
                    <nav>
                        <a href="tags.php?tag_id=<?php echo $tag['id'] ?>">Messages</a>
                    </nav>
                </article>
            <?php } ?>
        </aside>
        <main>
            <h2>Utilisatrices</h2>
            <?php
            /* REQUETE SUR LES UTILISATRICES */
            $laQuestionEnSql = "SELECT * FROM `users` LIMIT 50";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            // -- Vérification  de la requete --
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
                exit();
            }

            /* AFFICHAGE DES UTILISATRICES */
            while ($tag = $lesInformations->fetch_assoc()) {
                $authorId = $tag["id"];
            ?>
                <article>
                    <a href="./wall.php?user_id=<?php echo $authorId ?>">
                        <h3><?php echo $tag['alias'] ?></h3>
                        <p>id:<?php echo $tag['id'] ?></p>
                    </a>
                    <nav>
                        <a href="wall.php?user_id=<?php echo $tag['id'] ?>">Mur</a>
                        | <a href="feed.php?user_id=<?php echo $tag['id'] ?>">Flux</a>
                        | <a href="settings.php?user_id=<?php echo $tag['id'] ?>">Paramètres</a>
                        | <a href="followers.php?user_id=<?php echo $tag['id'] ?>">Suiveurs</a>
                        | <a href="subscriptions.php?user_id=<?php echo $tag['id'] ?>">Abonnements</a>
                    </nav>
                </article>
            <?php } ?>
        </main>
    </div>
</body>

</html>