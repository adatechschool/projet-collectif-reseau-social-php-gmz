<?php

function displayTags($string)
{
    include "./sqlConnection.php";

    $allTagsQuery =
        "
        SELECT * FROM tags;
        ";
    $data = $mysqli->query($allTagsQuery);


    $tagsArray = explode(",", $string);

    for ($i = 0; $i < count($tagsArray); $i++) {




?><a href="./tags.php?tag_id=<?php ?>">
            #<?php echo $tagsArray[$i] ?>
        </a>

<?php
    }
}
