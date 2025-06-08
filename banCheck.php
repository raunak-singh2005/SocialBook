<?php

// Checks if the user is not banned; redirects if banned
function requireNotBanned($userID) {
    // Return early if no user ID is provided
    if (!$userID) return;

    // Include database connection
    include_once("classes/connect.php");
    $DB = new Database();

    // Fetch the most recent ban record for the user
    $ban = $DB->read(
        "SELECT * FROM TempBans WHERE bannedUserID = '$userID' ORDER BY id DESC LIMIT 1"
    );

    // If a ban record exists and has a date
    if ($ban && isset($ban[0]['dateBanned'])) {
        $dateBanned = new DateTime($ban[0]['dateBanned']);
        $now = new DateTime();

        // Calculate days since the ban
        $interval = $now->diff($dateBanned);
        $daysPassed = $interval->days;
        $daysLeft = 30 - $daysPassed;

        // If the ban date is in the future, reset days left to 30
        if ($now < $dateBanned) {
            $daysLeft = 30;
        }

        // If the ban is still active, redirect to ban page
        if ($daysLeft > 0) {
            header("Location: ban.php");
            die;
        }
    }
}