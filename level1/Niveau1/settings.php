<?php
include "./settings-script.php"
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Paramètres</title>
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
    <div id="wrapper" class='profile'>


        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les informations de l'utilisatrice
                    n° <?php echo $userId ?></p>

            </section>
        </aside>
        <main>
            <article class='parameters'>
                <h3>Mes paramètres</h3>
                <dl>
                    <dt>Pseudo</dt>
                    <dd>
                        <?php echo $alias ?>
                    </dd>
                    <dt>Email</dt>
                    <dd>
                        <?php echo $email ?>
                    </dd>
                    <dt>Nombre de message</dt>
                    <dd>
                        <?php echo $totalPost ?>
                    </dd>
                    <dt>Nombre de "J'aime" donnés </dt>
                    <dd>
                        <?php echo $likesGiven ?>
                    </dd>
                    <dt>Nombre de "J'aime" reçus</dt>
                    <dd>
                        <?php echo $likesReceived ?>
                    </dd>
                </dl>

            </article>
        </main>
    </div>
</body>

</html>