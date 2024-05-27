<?php

$tagId = intval($_GET['tag_id']);
include 'sqlConnection.php';

$tagName = getKeyword($tagId);
$lesInformations = getInformationsFromPosts();

// echo "<pre>" . print_r($post, 1) . "</pre>";

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

function getInformationsFromPosts()
{
    include './sqlConnection.php';
    $tagId = intval($_GET['tag_id']);

    /**
     * Etape 3: récupérer tous les messages avec un mot clé donné
     */
    $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts_tags as filter 
                    JOIN posts ON posts.id=filter.post_id
                    JOIN users ON users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE filter.tag_id = '$tagId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
    $lesInformations = $mysqli->query($laQuestionEnSql);
    if (!$lesInformations) {
        echo ("Échec de la requete : " . $mysqli->error);
    }

    return $lesInformations;
}


// /**
//  * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
//  */

function displayPosts()
{
    $lesInformations = getInformationsFromPosts();
    // $post = $lesInformations->fetch_assoc();

    while ($post = $lesInformations->fetch_assoc()) {

        echo "<pre>" . print_r($post, 1) . "</pre>";

        // ==== Get date and different element for the posts
        $date = $post["created"];
        // 2020-11-20 18:26:50
        $dateParsed = date_parse_from_format("Y-m-d H:i:s", $date);

        echo "<pre>" . print_r($dateParsed, 1) . "</pre>";

        $monthNum = $dateParsed["month"];
        $dateObj   = DateTime::createFromFormat('!m', $monthNum);
        $monthName = $dateObj->format('F');
        $dayNumber = $dateParsed["day"];
        $year = $dateParsed["year"];
        $hour = $dateParsed["hour"];
        $minute = $dateParsed["minute"];
        $second = $dateParsed["second"];

        $finalStringDate = "$dayNumber $monthName $year à $hour" . "h" . "$minute";
        // == END of Date

?>
        <article>
            <h3>
                <time datetime='2020-02-01 11:12:13'><?php echo $finalStringDate ?></time>
            </h3>
            <address>par <?php echo $post["author_name"] ?></address>
            <div>
                <p><?php echo $post["content"] ?></p>
            </div>
            <footer>
                <small>♥ <?php echo $post["like_number"] ?></small>
                <a href="">#lorem</a>,
                <a href="">#piscitur</a>,
            </footer>
        </article>
<?php }
}
