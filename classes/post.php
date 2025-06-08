<?php

// The Post class handles creating, liking, and retrieving posts.
class post {

    // Stores error messages
    private $error = "";

    /**
     * Like or unlike a post.
     * If the user hasn't liked the post, it adds a like.
     * If the user already liked the post, it removes the like.
     */
    public function likePost($id, $type, $userid) {
        if ($type == "post") {
            $DB = new Database();

            // Check if user already liked this post
            $sql = "SELECT likes FROM Likes WHERE Type = 'post' AND contentID = '$id' LIMIT 1";
            $result = $DB->read($sql);

            $alreadyLiked = false;
            $likes = [];

            if (is_array($result) && isset($result[0]['likes'])) {
                $likes = json_decode($result[0]['likes'], true);
                if (!is_array($likes)) {
                    $likes = [];
                }
                $userIDS = array_column($likes, 'userid');
                if (in_array($userid, $userIDS)) {
                    $alreadyLiked = true;
                }
            }

            if (!$alreadyLiked) {
                // Like: Increment likes in Posts table
                $sql = "UPDATE Posts SET likes = likes + 1 WHERE postID = '$id'";
                $DB->write($sql);

                // Add this user to the likes array
                $arr = [
                    'userid' => $userid,
                    'date' => date("Y-m-d H:i:s")
                ];
                $likes[] = $arr;
                $likesSTR = json_encode($likes);

                if (is_array($result) && isset($result[0]['likes'])) {
                    // Update existing Likes row
                    $sql = "UPDATE Likes SET likes = '$likesSTR' WHERE Type = 'post' AND contentID = '$id'";
                    $DB->write($sql);
                } else {
                    // Insert new Likes row
                    $sql = "INSERT INTO Likes (type, contentID, likes) VALUES ('$type', '$id', '$likesSTR')";
                    $DB->write($sql);
                }
            } else {
                // Unlike: Remove user from likes array and decrement post likes
                $likes = array_filter($likes, function($like) use ($userid) {
                    return $like['userid'] != $userid;
                });
                $likesSTR = json_encode(array_values($likes)); // reindex array

                // Decrement likes in Posts table, but not below zero
                $sql = "UPDATE Posts SET likes = GREATEST(likes - 1, 0) WHERE postID = '$id'";
                $DB->write($sql);

                // Update Likes row or delete if empty
                if (count($likes) > 0) {
                    $sql = "UPDATE Likes SET likes = '$likesSTR' WHERE Type = 'post' AND contentID = '$id'";
                    $DB->write($sql);
                } else {
                    $sql = "DELETE FROM Likes WHERE Type = 'post' AND contentID = '$id'";
                    $DB->write($sql);
                }
            }
        }
    }

    /**
     * Create a unique post ID.
     */
    private function createPostID() {
        $length = rand(4, 19);
        $number = "";
        for ($i = 0; $i < $length; $i++) {
            $number .= rand(0, 9);
        }
        return $number;
    }

    /**
     * Retrieve the latest 10 posts for a user.
     */
    public function getPosts($id) {
        $query = "SELECT * FROM Posts WHERE userID = '$id' ORDER BY ID DESC limit 10";
        $DB = new Database();
        $result = $DB->read($query);

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Create a new post with optional image and location data.
     */
    public function createPost($userid, $postData, $fileData) {

        // Only proceed if post text or image is provided
        if (!empty($postData['post']) || !empty($fileData['file']['name'])) {

            $myImage = "";
            $hasImage = 0;
            $destination = "";

            // Handle image upload if present
            if (!empty($fileData['file']['name'])) {
                $folder = "uploads/" . $userid . "/";

                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $image = new Image();

                // Get file extension and validate
                $fileInfo = pathinfo($fileData['file']['name']);
                $extension = strtolower($fileInfo['extension']);
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $allowed)) {
                    $myImageName = $image->generateFilename(15) . "." . $extension;
                    $myImage = $folder . $myImageName;
                    $destination = $myImage;
                    move_uploaded_file($fileData['file']['tmp_name'], $destination);

                    // Resize image
                    $image->resizeImage($destination, $destination, 1500, 1500, $extension);

                    $hasImage = 1;
                }
            }

            // Prepare post data
            $post = addslashes($postData['post']);
            $postID = $this->createPostID();

            $latitude = isset($postData['latitude']) ? floatval($postData['latitude']) : null;
            $longitude = isset($postData['longitude']) ? floatval($postData['longitude']) : null;

            // Insert post into database
            $query = "INSERT INTO Posts (userID, postID, post, image, latitude, longitude, date) VALUES ('$userid', '$postID', '$post', '$myImage', " .
                ($latitude !== null ? "'$latitude'" : "NULL") . ", " .
                ($longitude !== null ? "'$longitude'" : "NULL") . ", now())";

            $DB = new Database();
            $DB->write($query);
        } else {
            $this->error = "Please enter a post to post something<br>";
        }

        return $this->error;
    }

}