<?php
include_once("classes/connect.php");
include_once("classes/login.php");
include_once("classes/user.php");

session_start();

$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

if(isset($_SERVER['HTTP_REFERER'])) {
    $returnTo = $_SERVER['HTTP_REFERER'];
} else {
    $returnTo = "mainboard.php";
}

if(isset($_POST['postID']) && isset($_POST['comment_text'])) {
    $DB = new Database();
    $commentText = trim($_POST['comment_text']);
    $commentPostID = $_POST['postID'];
    $commentUserID = $_SESSION['SocialBook_userID'];

    // Get post owner ID
    $postRow = $DB->read("SELECT userID FROM Posts WHERE postID = '$commentPostID' LIMIT 1");
    $postOwnerID = is_array($postRow) ? $postRow[0]['userID'] : 0;

    if ($commentText !== "" && $postOwnerID) {
        $DB->write("INSERT INTO Comments (userID, postOwnerID, postID, comment, date) VALUES ('$commentUserID', '$postOwnerID', '$commentPostID', '" . addslashes($commentText) . "', NOW())");
    }
}

header("Location: $returnTo");
die();