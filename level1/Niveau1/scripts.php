<?php

function detectTags($thePostContent, $thePostId)
{
    include "./sqlConnection.php";

    // // ==== Delete les " ' "
    // $newString = explode("'", $thePostContent);
    // $stringAgain = implode(" ", $newString);

    // // ==== Delete les " , "
    // $newString = explode(",", $stringAgain);

    $hastagRegex = "/[:.,'\r\n]/";
    $newString = str_replace('\r\n', ' ', $thePostContent);
    $stringAgain = preg_replace($hastagRegex, " ", $newString);

    $newStringArray = explode(" ", $stringAgain);

    // === Array qui va contenir les mots avec #
    foreach ($newStringArray as $word) {
        if (str_contains($word, "#")) {

            $hastagRegex = "/[ #!?\/\$*§%\^_]/";
            $word = preg_replace($hastagRegex, "", $word);
            $word = trim($word);
            // $word = explode("#", $word);
            // $word = implode("", $word);

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
