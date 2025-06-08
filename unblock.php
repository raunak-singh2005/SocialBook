<?php
// Include necessary class files
include_once("classes/user.php");
include_once("classes/login.php");
include_once("classes/connect.php");

// Start the session
session_start();

// Check if user is logged in
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// Determine where to redirect after unblocking
$returnTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mainboard.php";

// Check if a valid user ID is provided in the GET request
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $unblockUserId = $_GET['user'];
    $currentUserId = $_SESSION['SocialBook_userID'];

    // Prevent users from unblocking themselves
    if ($unblockUserId != $currentUserId) {
        $DB = new Database();

        // Fetch the current user's block list
        $query = "SELECT blockList FROM Users WHERE userid = '$currentUserId' LIMIT 1";
        $result = $DB->read($query);

        $blockList = [];
        if ($result && isset($result[0]['blockList']) && $result[0]['blockList']) {
            $blockList = json_decode($result[0]['blockList'], true);
            if (!is_array($blockList)) {
                $blockList = [];
            }
        }

        // Remove the specified user from the block list
        $newBlockList = [];
        foreach ($blockList as $entry) {
            if ($entry['userid'] != $unblockUserId) {
                $newBlockList[] = $entry;
            }
        }

        // Update the block list in the database
        $blockListJson = addslashes(json_encode($newBlockList));
        $updateQuery = "UPDATE Users SET blockList = '$blockListJson' WHERE userid = '$currentUserId' LIMIT 1";
        $DB->write($updateQuery);
    }
}

// Redirect back to the previous page or mainboard
header("Location: $returnTo");
die();