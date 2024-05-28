<?php
include "./tags-script.php";
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