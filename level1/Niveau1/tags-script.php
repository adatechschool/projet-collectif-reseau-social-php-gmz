<?php
include "./sqlConnection.php";
//include "./requetes.php";


$tagId = intval($_GET['tag_id']);


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
    include "./requetes.php";

    /**
     * Etape 3: récupérer tous les messages avec un mot clé donné
     */
    $laQuestionEnSql =  $requestPostsFromTag;
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
    include "./scripts.php";
    $lesInformations = getInformationsFromPosts();
    // $post = $lesInformations->fetch_assoc();
    // echo "<pre>" . print_r($lesInformations, 1) . "</pre>";

    while ($post = $lesInformations->fetch_assoc()) {

        // ==== Get date and different element for the posts
        $date = $post["created"];
        // 2020-11-20 18:26:50
        $dateParsed = date_parse_from_format("Y-m-d H:i:s", $date);

        // echo "<pre>" . print_r($dateParsed, 1) . "</pre>";

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
        $authorId = $post["id"];
        detectTags($post["content"]);
?>
        <article>
            <h3>
                <time datetime='2020-02-01 11:12:13'><?php echo $finalStringDate ?></time>
            </h3>
            <address>par <a href="./wall.php?user_id=<?php echo $authorId ?>"><?php echo $post["author_name"] ?></a></address>
            <div>
                <p><?php echo $post["content"] ?></p>
            </div>
            <footer>
                <small>♥ <?php echo $post["like_number"] ?></small>

                <?php
                $newtagidlist = explode(",", $post['tagidlist']);
                $newtaglist = explode(",", $post['taglist']);


                if (count($newtagidlist) > 1) {
                    for ($i = 0; $i < count($newtagidlist); $i++) {
                ?>
                        <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>"><?php
                                                                                    // $hashtag = str_replace(',', ', #', $newtaglist[$i]);
                                                                                    echo '#' . $newtaglist[$i]  ?></a><?php
                                                                                                                    }
                                                                                                                } elseif (strlen($newtagidlist[0]) == 1) {
                                                                                                                        ?>
                    <a href="./tags.php?tag_id=<?php echo $newtagidlist[0] ?>"><?php echo '#' . $newtaglist[0] ?></a><?php
                                                                                                                    }
                                                                                                                        ?>
            </footer>
        </article>

<?php }
}
