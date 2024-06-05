<?php


function detectTags($thePostContent, $thePostId)
{
    include "./sqlConnection.php";

    // ==== Delete les " ' "
    $newString = explode("'", $thePostContent);
    $stringAgain = implode(" ", $newString);

    // ==== Delete les " , "
    $newString = explode(",", $stringAgain);
    $stringAgain = implode(" ", $newString);

    $newStringArray = explode(" ", $stringAgain);

    // === Array qui va contenir les mots avec #
    foreach ($newStringArray as $word) {
        if (str_contains($word, "#")) {
            $word = explode("#", $word);
            $word = implode("", $word);
            $word = trim($word);

            $queryTagId = "
            SELECT id FROM tags WHERE tags.label='$word';
            ";
            $tagRequest = $mysqli->query($queryTagId);
            if ($tagRequest->num_rows == 0) {

                $updateTag = "
                INSERT INTO tags(id,label) VALUES(NULL,'$word');
                ";
                $ok = $mysqli->query($updateTag);
                if (!$ok) {
                    echo "Impossible d'ajouter le TAG";
                } else {
                    echo "TAG updated";
                }
            }

            $queryString = "
            INSERT INTO posts_tags(post_id, tag_id)
            VALUES ('$thePostId',(SELECT id FROM tags WHERE tags.label='$word'));
            ";

            $ok = $mysqli->query($queryString);
            if (!$ok) {
                echo "Impossible d'ajouter le message: " . $mysqli->error;
            } else {
                echo "DB updated";
            }

            echo "<pre>" . print_r($word, 1) . "</pre>";
        }
    }
}

function createDate($timePost)
{
    // Tableau des mois en français
    $moisFr = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
    ];

    // // Convertir la chaîne de caractères en objet DateTime
    $timeObj = DateTime::createFromFormat('Y-m-d H:i:s', $timePost);
    echo "<pre>" . print_r($timeObj, 1) . "</pre>";
    $dayNumber = $timeObj->format('d');
    $monthName = $moisFr[(int)$timeObj->format('m')];
    $year = $timeObj->format('Y');
    $hour = $timeObj->format('H');
    $minute = $timeObj->format('i');
    $second = $timeObj->format('s');

    // // Formater la date en français
    return "$dayNumber $monthName $year à $hour" . "h" . "$minute";
}
