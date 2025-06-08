<?php
// User class handles user data and social features (friends, followers, following)
class User {

    // Get user data by ID (returns associative array or false)
    public function getData($id){
        $query = "SELECT * FROM Users WHERE userid = '$id' limit 1";
        $DB = new Database();
        $result = $DB->read($query);

        if($result){
            return $result[0];
        }else{
            return false;
        }
    }

    // Alias for getData (can be merged)
    public function getUser($id){
        return $this->getData($id);
    }

    // Get all users except the given user (for friend suggestions, etc.)
    public function getFriends($id){
        $query = "SELECT * FROM Users WHERE userid != '$id'";
        $DB = new Database();
        $result = $DB->read($query);

        return $result ? $result : false;
    }

    // Get followers of a user (returns array of user info)
    public function getFollowers($userid) {
        $DB = new Database();
        $followers = [];

        // Get the follows JSON for this user
        $query = "SELECT follows FROM Follows WHERE userID = '$userid' LIMIT 1";
        $result = $DB->read($query);

        if ($result && isset($result[0]['follows'])) {
            $followsArr = json_decode($result[0]['follows'], true);
            if (is_array($followsArr) && count($followsArr) > 0) {
                $followerIDs = array_column($followsArr, 'follower_id');
                $ids = implode("','", array_map('addslashes', $followerIDs));
                $userQuery = "SELECT * FROM Users WHERE userid IN ('$ids')";
                $followers = $DB->read($userQuery);
            }
        }
        return $followers ? $followers : [];
    }

    // Get users that the given user is following (returns array of user info)
    public function getFollowing($userid) {
        $DB = new Database();
        $followingIDs = [];

        // Find all users where the follows JSON contains this user as a follower
        $query = "SELECT userID, follows FROM Follows";
        $result = $DB->read($query);

        if ($result) {
            foreach ($result as $row) {
                $followsArr = json_decode($row['follows'], true);
                if (is_array($followsArr)) {
                    foreach ($followsArr as $follow) {
                        if ($follow['follower_id'] == $userid) {
                            $followingIDs[] = $row['userID'];
                            break;
                        }
                    }
                }
            }
        }

        // Fetch user info for all followingIDs
        $following = [];
        if (count($followingIDs) > 0) {
            $ids = implode("','", array_map('addslashes', $followingIDs));
            $userQuery = "SELECT * FROM Users WHERE userid IN ('$ids')";
            $following = $DB->read($userQuery);
        }
        return $following ? $following : [];
    }

    /**
     * Follow or unfollow a user.
     * If not already following, follow and increment follower count.
     * If already following, unfollow and decrement follower count.
     */
    public function followUser($followed_id, $follower_id) {
        $DB = new Database();

        // Check for existing Follows row
        $sql = "SELECT follows FROM Follows WHERE userID = '$followed_id' LIMIT 1";
        $result = $DB->read($sql);

        $alreadyFollowing = false;
        $follows = [];

        if (is_array($result) && count($result) > 0) {
            $follows = json_decode($result[0]['follows'], true);
            if (!is_array($follows)) {
                $follows = [];
            }
            $followerIDs = array_column($follows, 'follower_id');
            if (in_array($follower_id, $followerIDs)) {
                $alreadyFollowing = true;
            }
        }

        if (!$alreadyFollowing) {
            // Follow: increment followers in Users table
            $sql = "UPDATE Users SET followers = followers + 1 WHERE userid = '$followed_id'";
            $DB->write($sql);

            // Add this follower to the follows array
            $arr = [
                'follower_id' => $follower_id,
                'followed_id' => $followed_id,
                'date' => date("Y-m-d H:i:s")
            ];
            $follows[] = $arr;
            $followsSTR = json_encode($follows);

            // Update or insert Follows row
            $sql_check = "SELECT 1 FROM Follows WHERE userID = '$followed_id' LIMIT 1";
            $check = $DB->read($sql_check);
            if (is_array($check) && count($check) > 0) {
                $sql = "UPDATE Follows SET follows = '$followsSTR' WHERE userID = '$followed_id'";
                $DB->write($sql);
            } else {
                $sql = "INSERT INTO Follows (userID, follows) VALUES ('$followed_id', '$followsSTR')";
                $DB->write($sql);
            }
        } else {
            // Unfollow: decrement followers in Users table, not below zero
            $sql = "UPDATE Users SET followers = GREATEST(followers - 1, 0) WHERE userid = '$followed_id'";
            $DB->write($sql);

            // Remove follower from follows array
            $follows = array_filter($follows, function($follow) use ($follower_id) {
                return $follow['follower_id'] != $follower_id;
            });
            $followsSTR = json_encode(array_values($follows)); // reindex

            // Update or delete Follows row
            if (count($follows) > 0) {
                $sql = "UPDATE Follows SET follows = '$followsSTR' WHERE userID = '$followed_id'";
                $DB->write($sql);
            } else {
                $sql = "DELETE FROM Follows WHERE userID = '$followed_id'";
                $DB->write($sql);
            }
        }
    }

}
