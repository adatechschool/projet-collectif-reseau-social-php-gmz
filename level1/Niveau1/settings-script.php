<?php
session_start();

if (!isset($_SESSION["connected_id"])) {
    header('Location: ./login.php');
    exit();
} else {
    $sessionId = $_SESSION["connected_id"];
}
/**
 * Etape 1: Les paramètres concernent une utilisatrice en particulier
 * La première étape est donc de trouver quel est l'id de l'utilisatrice
 * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
 * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
 * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
 */
$userId = intval($_GET['user_id']);
if ((!$userId || $userId != $sessionId) && $sessionId) {
    Header("Location: ./settings.php?user_id=$sessionId");
    exit();
}
/**
 * Etape 2: se connecter à la base de donnée
 */
//$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
include 'sqlConnection.php';

/**
 * Etape 3: récupérer le nom de l'utilisateur
 */
$laQuestionEnSql = "
                    SELECT users.*, 
                    count(DISTINCT posts.id) as totalpost, 
                    count(DISTINCT given.post_id) as totalgiven, 
                    count(DISTINCT recieved.user_id) as totalrecieved 
                    FROM users 
                    LEFT JOIN posts ON posts.user_id=users.id 
                    LEFT JOIN likes as given ON given.user_id=users.id 
                    LEFT JOIN likes as recieved ON recieved.post_id=posts.id 
                    WHERE users.id = '$userId' 
                    GROUP BY users.id
                    ";
$lesInformations = $mysqli->query($laQuestionEnSql);
if (!$lesInformations) {
    echo ("Échec de la requete : " . $mysqli->error);
}
$user = $lesInformations->fetch_assoc();

/**
 * Etape 4: à vous de jouer
 */
//@todo: afficher le résultat de la ligne ci dessous, remplacer les valeurs ci-après puiseffacer la ligne ci-dessous
// echo "<pre>" . print_r($user, 1) . "</pre>";

$alias = $user["alias"];
$totalPost = $user["totalpost"];
$email = $user["email"];
$likesGiven = $user["totalgiven"];
$likesReceived = $user["totalrecieved"];

$isLoggedOut = isset($_POST["logout"]);
if ($isLoggedOut) {
    unset($_SESSION["connected_id"]);
    if (!isset($_SESSION["connected_id"])) {
        header('Location: ./login.php');
        exit();
    }
}

function displayLogOut()
{
?>
    <form action="" method="post">
        <input type="submit" name="logout" value="Log out">
    </form>
<?php

}
