<?php
include "./sqlConnection.php";



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
   

if (isset($_SESSION["connected_id"])) {
    $sessionId = $_SESSION["connected_id"];
}
include "./sqlConnection.php";
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
                

                <?php
                $messageid = $post['message_id'];
                $newtagidlist = explode(",", $post['tagidlist'] ?? '');
                $newtaglist = explode(",", $post['taglist'] ?? '');
                $post['like_number'] = intval($post['like_number']) / count($newtagidlist);

                if (isset($_POST['unlike' . $messageid])) {
                    $post['like_number'] = $post['like_number'] - 1;
                } elseif (isset($_POST['like' . $messageid])) {
                    $post['like_number'] = $post['like_number'] + 1;
                }
                ?>

                <small>♥ <?php echo $post['like_number'] ?></small>
                <?php
                echo "<pre>" . print_r($messageid, 1) . "</pre>";

                if (isset($_POST['like' . $messageid])) {
                    // Ajouter un like
                    $ajoutLikeSql = "INSERT INTO likes (id, user_id, post_id) VALUES (NULL, $sessionId, $messageid)";
                    if (!$mysqli->query($ajoutLikeSql)) {
                        echo "Erreur lors de l'ajout du like: " . $mysqli->error;
                    }
                } elseif (isset($_POST['unlike' . $messageid])) {
                    // Supprimer un like
                    $suppressionLikeSql = "DELETE FROM likes WHERE user_id = $sessionId AND post_id = $messageid";
                    if (!$mysqli->query($suppressionLikeSql)) {
                        echo "Erreur lors de la suppression du like: " . $mysqli->error;
                    }
                }

                // Vérifier si l'utilisateur a liké le post
                $esketulike = "SELECT * FROM likes WHERE post_id='$messageid' AND user_id='$sessionId';";
                $likes = $mysqli->query($esketulike);
                echo "<pre>" . print_r($likes, 1) . "</pre>";

                if ($likes->num_rows == 0) {
                ?>
                    <form method="post" action="">
                        <input type="hidden" name="like<?php echo $messageid ?>" value="true">
                        <button type="submit" id="likeButton" class="like">Like</button>
                    </form>
                <?php
                } else {
                ?>
                    <form method="post" action="">
                        <input type="hidden" name="unlike<?php echo $messageid ?>" value="true">
                        <button type="submit" id="likeButton" class="unlike">Unlike</button>
                    </form>
                    <?php
                }


                if (count($newtagidlist) > 1) {
                    for ($i = 0; $i < count($newtagidlist); $i++) {
                    ?>
                        <a href="./tags.php?tag_id=<?php echo $newtagidlist[$i] ?>">
                            <?php
                            // $hashtag = str_replace(',', ', #', $newtaglist[$i]);
                            echo '#' . $newtaglist[$i]  ?></a>
                    <?php
                    }
                } elseif (strlen($newtagidlist[0]) >= 1) {
                    ?>
                    <a href="./tags.php?tag_id=<?php echo $newtagidlist[0] ?>"><?php echo '#' . $newtaglist[0] ?></a>
                <?php
                }
                ?>
            </footer>
        </article>

<?php }
}
