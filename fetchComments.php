<?php
session_start();

// Include required classes
include("classes/connect.php");
include("classes/user.php");

// Check if user is logged in
if (!isset($_SESSION['SocialBook_userID'])) {
    http_response_code(403);
    exit;
}

// Validate and get postID from GET parameters
$postID = isset($_GET['postID']) ? intval($_GET['postID']) : 0;
if ($postID === 0) {
    http_response_code(400);
    exit;
}

// Initialize database connection
$DB = new Database();

// Fetch comments for the given postID, ordered by date
$comments = $DB->read("SELECT * FROM Comments WHERE postID = '$postID' ORDER BY date ASC");

$commentData = [];

if ($comments) {
    foreach ($comments as $comment) {
        // Fetch user details for each comment
        $user = new User();
        $commentUser = $user->getUser($comment['userID']);

        // Prepare comment data with sanitized output
        $commentData[] = [
            'name' => htmlspecialchars($commentUser['firstName'] . " " . $commentUser['lastName']),
            'comment' => htmlspecialchars($comment['comment']),
            'date' => htmlspecialchars($comment['date'])
        ];
    }
}

// Set response header and output JSON
header('Content-Type: application/json');
echo json_encode($commentData);