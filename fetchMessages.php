<?php
// Start the session to access session variables
session_start();

// Include required class files for database connection and messaging
include("classes/connect.php");
include("classes/message.php");

// Check if the user is logged in
if (!isset($_SESSION['SocialBook_userID'])) {
    http_response_code(403); // Forbidden
    exit;
}

// Get the current user ID from the session
$currentUser = $_SESSION['SocialBook_userID'];

// Get the other user's ID from the GET parameter, default to 0 if not set
$otherUser = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Validate the other user's ID
if ($otherUser === 0) {
    http_response_code(400); // Bad Request
    exit;
}

// Create a DirectMessage instance and fetch messages between users
$dm = new DirectMessage();
$messages = $dm->getMessages($currentUser, $otherUser);

// Set the response header to JSON and output the messages
header('Content-Type: application/json');
echo json_encode($messages);