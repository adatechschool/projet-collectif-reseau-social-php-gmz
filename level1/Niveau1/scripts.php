<?php

function displayTags($string)
{
    include "./sqlConnection.php";

    $allTagsQuery =
        "
        SELECT posts.content,
            posts.created,
            users.id,
            users.alias as author_name,  
            count(likes.id) as like_number,  
            GROUP_CONCAT(DISTINCT tags.id) AS tagidlist,
            GROUP_CONCAT(DISTINCT tags.label) AS taglist 
        FROM posts
        JOIN users ON  users.id=posts.user_id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
        LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
        LEFT JOIN likes      ON likes.post_id  = posts.id 
        GROUP BY posts.id
        ORDER BY posts.created DESC  
        LIMIT 5
        ";
    $data = $mysqli->query($allTagsQuery);

    if (count($taglist) > 1) {
        foreach ($taglist as $tag) { ?>
            <a href="./tags.php?tag_id=<?php echo $post['tagidlist'] ?>">#
                <?php
                $hashtag = str_replace(',', ', #', $post['taglist']);
                echo $hashtag  ?></a>
<?php
        }
    }
}

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
