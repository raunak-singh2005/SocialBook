<?php
// Include required class files
include_once("classes/user.php");
include_once("classes/login.php");
include_once("classes/connect.php");

// Start session
session_start();

// Check if user is logged in and get user data
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// Determine where to redirect after blocking
$returnTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "mainboard.php";

// Check if a user ID is provided in the GET request and is valid
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $blockUserId = $_GET['user'];
    $currentUserId = $_SESSION['SocialBook_userID'];

    // Prevent users from blocking themselves
    if ($blockUserId != $currentUserId) {
        $DB = new Database();

        // Fetch the current user's block list
        $query = "SELECT blockList FROM Users WHERE userid = '$currentUserId' LIMIT 1";
        $result = $DB->read($query);

        $blockList = [];
        if ($result && isset($result[0]['blockList']) && $result[0]['blockList']) {
            $blockList = json_decode($result[0]['blockList'], true);
            if (!is_array($blockList)) $blockList = [];
        }

        // Check if the user is already blocked
        $alreadyBlocked = false;
        foreach ($blockList as $entry) {
            if ($entry['userid'] == $blockUserId) {
                $alreadyBlocked = true;
                break;
            }
        }

        // Add the user to the block list if not already blocked
        if (!$alreadyBlocked) {
            $blockList[] = [
                "userid" => $blockUserId,
                "date" => date("Y-m-d H:i:s")
            ];
            $blockListJson = addslashes(json_encode($blockList));
            $updateQuery = "UPDATE Users SET blockList = '$blockListJson' WHERE userid = '$currentUserId' LIMIT 1";
            $DB->write($updateQuery);
        }
    }
}

// Redirect back to the previous page or mainboard
header("Location: $returnTo");
die();