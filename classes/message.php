<?php
session_start();
include_once("classes/connect.php");

/**
 * Class DirectMessage
 * Handles sending and retrieving direct messages between users.
 */
class DirectMessage {

    /**
     * Sends a direct message from one user to another.
     */
    public function sendMessage($senderID, $receiverID, $message) {
        $DB = new Database();

        // Sanitize input to prevent SQL injection
        $senderID = addslashes($senderID);
        $receiverID = addslashes($receiverID);
        $message = addslashes($message);

        // Insert the message into the database
        $query = "INSERT INTO DirectMessages (senderID, receiverID, message, date) 
                  VALUES ('$senderID', '$receiverID', '$message', NOW())";
        return $DB->write($query);
    }

    /**
     * Retrieves all messages exchanged between two users, ordered by date.
     */
    public function getMessages($user1, $user2) {
        $DB = new Database();

        // Sanitize input to prevent SQL injection
        $user1 = addslashes($user1);
        $user2 = addslashes($user2);

        // Select all messages between the two users
        $query = "SELECT * FROM DirectMessages 
                  WHERE (senderID = '$user1' AND receiverID = '$user2') 
                     OR (senderID = '$user2' AND receiverID = '$user1')
                  ORDER BY date ASC";
        return $DB->read($query);
    }
}
?>