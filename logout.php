<?php

// Start the session to access session variables
session_start();

// If the user is logged in, remove their session data
if (isset($_SESSION['SocialBook_userID'])) {
    unset($_SESSION['SocialBook_userID']);
}

// Redirect the user to the login page
header("Location: login.php");
exit;