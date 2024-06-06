<?php
session_start();

if (isset($_SESSION["connected_id"])) {
    $sessionId = $_SESSION["connected_id"];
    header('Location: ./wall.php');
    exit();
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Connexion</title>
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
            <h2>Présentation</h2>
            <p>Bienvenue sur notre réseau social.</p>
        </aside>
        <main>
            <article>
                <h2>Connexion</h2>
                <?php
                include "./sqlConnection.php";

                /**
                 * TRAITEMENT DU FORMULAIRE
                 */
                // Etape 1 : vérifier si on est en train d'afficher ou de traiter le formulaire
                // si on recoit un champs email rempli il y a une chance que ce soit un traitement

                if (isset($_POST['email']) && isset($_POST['motpasse'])) {
                    // on ne fait ce qui suit que si un formulaire a été soumis.

                    // Etape 2: récupérer ce qu'il y a dans le formulaire @todo: c'est là que votre travaille se situe
                    // observez le résultat de cette ligne de débug (vous l'effacerez ensuite)

                    $emailAVerifier = $mysqli->real_escape_string($_POST['email']);
                    $passwdAVerifier = $mysqli->real_escape_string($_POST['motpasse']);
                    $passwdAVerifier = md5($passwdAVerifier);
                    //Etape 3 : Ouvrir une connexion avec la base de donnée.



                    //Etape 4 : Petite sécurité
                    // pour éviter les injection sql : https://www.w3schools.com/sql/sql_injection.asp

                    // // on crypte le mot de passe pour éviter d'exposer notre utilisatrice en cas d'intrusion dans nos systèmes

                    // NB: md5 est pédagogique mais n'est pas recommandée pour une vraies sécurité


                    //Etape 5 : construction de la requete
                    $lInstructionSql = "SELECT * "
                        . "FROM users "
                        . "WHERE "
                        . "email LIKE '" . $emailAVerifier . "'";


                    // Etape 6: Vérification de l'utilisateur
                    $res = $mysqli->query($lInstructionSql);
                    $user = $res->fetch_assoc();

                    if (!$user || $user["password"] != $passwdAVerifier) {
                        echo "La connexion a échouée. ";
                    } else {
                        echo "Votre connexion est un succès : " . $user['alias'] . ".";
                        // Etape 7 : Se souvenir que l'utilisateur s'est connecté pour la suite
                        $_SESSION['connected_id'] = $user['id'];
                        $transferId = $user['id'];
                        Header("Location: ./wall.php?user_id=$transferId");
                        exit();
                    }
                }

                if (isset($_POST['email2']) && isset($_POST['newMotpasse'])) {
                    $emailAVerifier = $mysqli->real_escape_string($_POST['email2']);
                    $newPassword = $mysqli->real_escape_string($_POST['newMotpasse']);
                    $newPassword = md5($newPassword);

                    // on crypte le mot de passe pour éviter d'exposer notre utilisatrice en cas d'intrusion dans nos systèmes

                    $updatePasswordSql = "
                                                UPDATE users
                                                SET password = '$newPassword'
                                                WHERE email = '$emailAVerifier';
                        ";
                    if ($mysqli->query($updatePasswordSql)) {
                        echo "Le mot de passe a été mis à jour avec succès.";
                    } else {
                        echo "Erreur lors de la mise à jour du mot de passe.";
                    }
                }

                if (!isset($_POST['password'])) {
                ?>
                    <form action="login.php" method="post">
                        <input type='hidden'>
                        <dl>
                            <dt><label for='email'>E-Mail</label></dt>
                            <dd><input type='email' name='email'></dd>
                            <dt><label for='motpasse'>Mot de passe</label></dt>
                            <dd><input type='password' name='motpasse'></dd>
                        </dl>
                        <input type='submit' value='Connexion'>
                    </form>
                    <form action="" method="post">
                        <input type="hidden" name="password" value="true">
                        <button type="submit" id="passwordButton" class="password">Modifier mot de passe</button>
                    </form>

                <?php
                } else {
                ?>
                    <form action="login.php" method="post">
                        <input type='hidden'>
                        <dl>
                            <dt><label for='email2'>E-Mail</label></dt>
                            <dd><input type='email' name='email2'></dd>
                            <dt><label for='newMotpasse'>Nouveau mot de passe </label></dt>
                            <dd><input type='password' name='newMotpasse'></dd>
                        </dl>
                        <input type='submit' value='Mettre à jour le mot de passe'>

                    </form><br>


                <?php
                }
                ?>

                <p><br>
                    Pas de compte?
                    <a href='registration.php'>Inscrivez-vous.</a>
                </p>

            </article>
        </main>
    </div>
</body>

</html>