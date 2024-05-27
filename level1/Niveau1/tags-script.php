<?php

$tagId = intval($_GET['tag_id']);
include 'sqlConnection.php';

$tagName = getKeyword($tagId);

function getKeyword($tagNameId)
{
    include "./sqlConnection.php";

    $laQuestionEnSql = "SELECT * FROM tags WHERE id= '$tagNameId' ";
    $lesInformations = $mysqli->query($laQuestionEnSql);
    $tag = $lesInformations->fetch_assoc();
    //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par le label et effacer la ligne ci-dessous
    // echo "<pre>" . print_r($tag, 1) . "</pre>";

    return $tag["label"];
}
