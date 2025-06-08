<?php

// Include required class files
include_once("classes/post.php");
include_once("classes/login.php");
include_once("classes/connect.php");

// Check user login and get user data
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// Determine where to redirect after processing
$returnTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mainboard.php";

// Process like action if valid type and id are provided
if (isset($_GET['type']) && isset($_GET['id'])) {
    $allowedTypes = ['post', 'comment', 'user'];
    if (
        is_numeric($_GET['id']) &&
        in_array($_GET['type'], $allowedTypes)
    ) {
        $post = new Post();
        $post->likePost($_GET['id'], $_GET['type'], $_SESSION['SocialBook_userID']);
    }
}

// Redirect back to the referring page or mainboard
header("Location: $returnTo");
die();