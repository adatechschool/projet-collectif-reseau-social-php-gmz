<?php
session_start();
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
    <title>ReSoC - Mes abonnés </title>
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
                <p>Sur cette page vous trouverez la liste des personnes qui
                    suivent les messages de l'utilisatrice
                    n° <?php echo intval($_GET['user_id']) ?></p>

            </section>
        </aside>
        <main class='contacts'>
            <?php
            // Etape 1: récupérer l'id de l'utilisateur
            $userId = intval($_GET['user_id']);
            if ((!$userId || $userId != $sessionId) && $sessionId) {
                Header("Location: ./followers.php?user_id=$sessionId");
                exit();
            }
            // Etape 2: se connecter à la base de donnée
            //$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
            include 'sqlConnection.php';
            // Etape 3: récupérer le nom de l'utilisateur
            $laQuestionEnSql = "
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id='$userId'
                    GROUP BY users.id
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            // Etape 4: à vous de jouer
            //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
            while ($followers = $lesInformations->fetch_assoc()) {
                $authorId = $followers["id"];

            ?>
                <article>
                    <img src="user.jpg" alt="blason" />
                    <h3><a href="./wall.php?user_id=<?php echo $authorId ?>"><?php echo $followers['alias'] ?></a></h3>
                    <p>id:<?php echo $followers['id'] ?></p>
                </article>
            <?php
                // avec le <?php ci-dessus on retourne en mode php 
            } // cette accolade ferme et termine la boucle while ouverte avant.
            ?>

        </main>
    </div>
</body>

</html>