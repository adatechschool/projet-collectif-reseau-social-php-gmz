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

    if (count($taglist) > 1 ){
        foreach ($taglist as $tag){?>
            <a href="./tags.php?tag_id=<?php echo $post['tagidlist'] ?>">#<?php 
                                           $hashtag = str_replace(',', ', #', $post['taglist']);
                                           echo $hashtag  ?></a><?php
        }
    }

    }

