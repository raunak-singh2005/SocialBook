<?php
session_start();

/**
 * Class Login
 * Handles user authentication, ban checks, and session management.
 */
class Login {
    // Stores error messages
    private $error = "";

    /**
     * Checks if a user is currently banned.
     */
    public function checkBan($userID) {
        $DB = new Database();
        // Get the most recent ban record for the user
        $ban = $DB->read("SELECT * FROM TempBans WHERE bannedUserID = '$userID' ORDER BY id DESC LIMIT 1");
        if ($ban && isset($ban[0]['dateBanned'])) {
            $dateBanned = new \DateTime($ban[0]['dateBanned']);
            $now = new \DateTime();
            $interval = $now->diff($dateBanned);
            $daysPassed = $interval->days;
            $daysLeft = 30 - $daysPassed;
            // If ban date is in the future, reset days left to 30
            if ($now < $dateBanned) $daysLeft = 30;
            if ($daysLeft > 0) {
                return true; // Still banned
            }
        }
        return false;
    }

    /**
     * Evaluates login credentials and handles login logic.
     */
    public function evaluate($data) {
        // Sanitize input
        $email = addslashes($data['email']);
        $password = addslashes($data['password']);

        // Hash the password
        $hashedPassword = $this->hashPassword($password);

        // Query for user with matching email and password
        $query = "SELECT * FROM Users WHERE email = '$email' AND password = '$hashedPassword' limit 1";
        $DB = new Database();
        $result = $DB->read($query);

        if ($result) {
            $row = $result[0];

            // Check if user is banned before logging in
            if ($this->checkBan($row['userid'])) {
                $_SESSION['SocialBook_userID'] = $row['userid'];
                header("Location: ban.php");
                die;
            }

            // Successful login
            $_SESSION['SocialBook_userID'] = $row['userid'];
            header("Location: mainboard.php");
            die;
        } else {
            $this->error = "Invalid email or password";
        }

        return $this->error;
    }

    /**
     * Hashes the password using SHA-256.
     */
    private function hashPassword($password) {
        return hash('sha256', $password);
    }

    /**
     * Checks if a user is logged in and returns user data.
     * Redirects to login page if not logged in.
     */
    public function checkLogin($id) {
        if (is_numeric($id)) {
            $DB = new Database();
            $query = "SELECT * FROM Users WHERE userid = '$id' limit 1";
            $userData = $DB->read($query);

            if ($userData) {
                return $userData[0];
            } else {
                header("Location: login.php");
                die;
            }
        } else {
            header("Location: login.php");
            die;
        }
    }
}