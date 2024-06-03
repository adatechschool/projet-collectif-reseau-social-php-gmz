<?php
include "./tags-script.php";

session_start();
echo "<pre>" . print_r($_SESSION, 1) . "</pre>";

if (!isset($_SESSION["connected_id"])) {
    header('Location: ./login.php');
} else {
    $sessionId = $_SESSION["connected_id"];
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Les message par mot-clé</title>
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
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les derniers messages comportant
                    le mot-clé '<?php echo $tagName ?>'
                    (n° <?php echo $tagId ?>)
                </p>

            </section>
        </aside>
        <main>
            <?php
            displayPosts();
            ?>
        </main>
    </div>
</body>

</html>