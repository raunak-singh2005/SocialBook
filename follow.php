<?php

// Include required class files
include_once("classes/user.php");
include_once("classes/login.php");
include_once("classes/connect.php");

// Start session
session_start();

// Check if user is logged in
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// Determine where to redirect after action
$returnTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mainboard.php";

// Detect if the request is an AJAX request
$isAjax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
);

// Initialize following status
$isFollowing = false;

// Handle follow action if POST data is present and valid
if (isset($_POST['follow_userid']) && is_numeric($_POST['follow_userid'])) {
    $follower_id = $_SESSION['SocialBook_userID'];
    $followed_id = $_POST['follow_userid'];

    // Prevent users from following themselves
    if ($follower_id != $followed_id) {
        $user = new User();
        $user->followUser($followed_id, $follower_id);

        // Check if the user is now following the target user
        $followingArr = $user->getFollowing($follower_id);
        if (is_array($followingArr)) {
            foreach ($followingArr as $f) {
                if ($f['userid'] == $followed_id) {
                    $isFollowing = true;
                    break;
                }
            }
        }
    }
}

// Respond appropriately based on request type
if ($isAjax) {
    // Respond with JSON for AJAX requests
    echo json_encode(['success' => true, 'following' => $isFollowing]);
    exit;
} else {
    // Redirect for normal requests
    header("Location: $returnTo");
    die();
}