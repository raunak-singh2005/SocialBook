<?php
    // --- Include required class files and ban check ---
    include("classes/connect.php");
    include("classes/login.php");
    include("classes/user.php");
    include("classes/post.php");
    include("classes/image.php");
    include("classes/profile.php");
    include_once("banCheck.php");

    // --- Start session and check if user is banned ---
    session_start();
    requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

    // --- Authenticate user and get user data ---
    $login = new Login();
    $userData = $login->checkLogin($_SESSION['SocialBook_userID']);

    // --- If viewing another user's profile, get their data ---
    if(isset($_GET['id']) && is_numeric($_GET['id'])){
        $profile = new Profile();
        $profileData = $profile->getProfile($_GET['id']);
        if(is_array($profileData)) {
            $userData = $profileData[0];
        }
    }

    $id = $userData['userid'];
    $postError = "";

    // --- Handle new post submission ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = new Post();
        $result = $post->createPost($id, $_POST, $_FILES);

        if($result != "") {
            header("Location: profile.php?id=" . $id);
            die;
        } else {
            $postError = "<div style='color: #ff7675; text-align: center;'>" . $result . "</div>";
        }
    }

    // --- Fetch posts for the profile user ---
    $post = new Post();
    $posts = $post->getPosts($id);

    // --- Fetch following and followers lists ---
    $user = new User();
    $following = $user->getFollowing($id);
    if (!is_array($following)) $following = [];
    $followers = $user->getFollowers($id);
    if (!is_array($followers)) $followers = [];

    // --- Defensive: filter out non-array elements ---
    $following = array_filter($following, 'is_array');
    $followers = array_filter($followers, 'is_array');

    // --- Find mutual friends (users who are both following and followers) ---
    $followingIDs = array_map('strval', array_map(function($u) { return $u['userid']; }, $following));
    $followersIDs = array_map('strval', array_map(function($u) { return $u['userid']; }, $followers));
    $mutualIDs = array_intersect($followingIDs, $followersIDs);

    // --- Get full user info for mutual friends ---
    $friends = [];
    foreach ($following as $f) {
        if (in_array(strval($f['userid']), $mutualIDs)) {
            $friends[$f['userid']] = $f;
        }
    }
    foreach ($followers as $f) {
        if (in_array(strval($f['userid']), $mutualIDs)) {
            $friends[$f['userid']] = $f;
        }
    }
    $friends = array_values($friends); // reindex
?>

<html>
<head>
    <title>SocialBook | Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- Styles for profile page layout and components --- */
        body { background: #181a20; }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 40px auto;
            gap: 32px;
        }
        .sidebar {
            flex: 0 0 320px;
            background: #23272f;
            border-radius: 24px;
            padding: 32px 24px 24px 24px;
            color: #e4e6eb;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .cover-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 18px;
            margin-bottom: -70px;
            display: block;
        }
        #profilePic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid #181a20;
            object-fit: cover;
            box-shadow: 0 2px 12px rgba(0,0,0,0.4);
            background: #23272f;
            margin-bottom: 12px;
            display: block;
        }
        #profileName {
            font-size: 28px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
            color: #fff;
        }
        .profile-details {
            color: #b0b3b8;
            margin-bottom: 18px;
        }
        #menuButtons {
            margin-top: 18px;
            background: #2c2f38;
            border-radius: 14px;
            padding: 10px 0;
            width: 100%;
            text-align: center;
            font-weight: 600;
            color: #8f94fb;
            box-shadow: 0 2px 8px rgba(78,84,200,0.08);
        }
        #menuButtons a {
            color: #8f94fb;
            text-decoration: none;
        }
        .friends-bar {
            margin-top: 32px;
            width: 100%;
        }
        .friends-bar-title {
            color: #8f94fb;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .friends-list {
            display: flex;
            gap: 18px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .friend-card {
            background: #2c2f38;
            border-radius: 16px;
            padding: 10px 12px;
            min-width: 90px;
            text-align: center;
            color: #e4e6eb;
            box-shadow: 0 1px 6px rgba(0,0,0,0.18);
        }
        .friend-card img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #23272f;
            margin-bottom: 6px;
            display: block;
        }
        .main-content {
            flex: 1 1 0;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .post-form-card {
            background: #23272f;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
            margin-bottom: 0;
        }
        textarea {
            width: 100%;
            border: none;
            font-size: 16px;
            border-radius: 12px;
            background: #181a20;
            color: #e4e6eb;
            padding: 12px;
            margin-top: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            outline: none;
            resize: vertical;
            min-height: 60px;
            transition: background 0.2s;
        }
        textarea:focus { background: #23272f; }
        .themed-btn {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: bold;
            font-size: 16px;
            margin-top: 12px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(78,84,200,0.15);
            transition: background 0.2s;
        }
        .themed-btn:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
        }
        input[type="file"].themed-btn {
            padding: 0;
            height: 44px;
            background: none;
            border: none;
            color: inherit;
            font: inherit;
            box-shadow: none;
            margin: 0;
            display: flex;
            align-items: center;
        }
        input[type="file"].themed-btn::-webkit-file-upload-button,
        input[type="file"].themed-btn::file-selector-button {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(78,84,200,0.15);
            transition: background 0.2s;
            height: 44px;
            display: flex;
            align-items: center;
        }
        input[type="file"].themed-btn::-webkit-file-upload-button:hover,
        input[type="file"].themed-btn::file-selector-button:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
        }
        #postSection {
            width: 100%;
            min-height: 307px;
            background: #23272f;
            padding: 18px 10px 10px 10px;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }
        #post {
            padding: 18px;
            font-size: 15px;
            display: flex;
            background: #181a20;
            border-radius: 18px;
            margin-bottom: 18px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.18);
            align-items: flex-start;
            color: #e4e6eb;
        }
        #post img {
            border-radius: 10px;
            margin-right: 16px;
            width: 75px;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .post-content { flex: 1; }
        a {
            color: #8f94fb;
            text-decoration: none;
            transition: color 0.2s;
        }
        a:hover {
            color: #4e54c8;
            text-decoration: underline;
        }
        .remove-btn {
            background: linear-gradient(90deg, #ff7675 0%, #e17055 100%) !important;
            color: #fff !important;
        }
        .remove-btn:hover {
            background: linear-gradient(90deg, #e17055 0%, #ff7675 100%) !important;
        }
        @media (max-width: 1000px) {
            .container { flex-direction: column; gap: 0; }
            .sidebar { margin-bottom: 32px; }
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <div class="container">
        <!-- === Sidebar/Profile Info === -->
        <div class="sidebar">
            <!-- Cover Image -->
            <img src="assets/mountain.png" class="cover-img">
            <?php
                // --- Determine profile picture ---
                $profilePic = "assets/Selfie.png";
                if (isset($userData['profileImage']) && file_exists($userData['profileImage'])) {
                    $profilePic = $userData['profileImage'];
                }
            ?>
            <!-- Profile Picture -->
            <img id="profilePic" src="<?php echo htmlspecialchars($profilePic); ?>">
            <!-- Profile Name -->
            <div id="profileName"><?php echo htmlspecialchars($userData['firstName'] . " " . $userData['lastName']); ?></div>
            <!-- Email -->
            <div class="profile-details">
                <?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ""; ?>
            </div>
            <?php
                // --- Show Settings/Followers for own profile, Follow/Unfollow for others ---
                if (
                    isset($_SESSION['SocialBook_userID']) &&
                    isset($userData['userid']) &&
                    $_SESSION['SocialBook_userID'] == $userData['userid']
                ) {
                    echo '<div id="menuButtons"><a href="settings.php">Settings</a></div>';
                    echo '<div id="menuButtons" style="margin-top:10px;"><a href="followers.php">Followers</a></div>';
                } else {
                    $currentUserID = $_SESSION['SocialBook_userID'];
                    $profileUserID = $userData['userid'];
                    $userObj = new User();
                    $followingArr = $userObj->getFollowing($currentUserID);
                    $isFollowing = false;
                    if (is_array($followingArr)) {
                        foreach ($followingArr as $f) {
                            if ($f['userid'] == $profileUserID) {
                                $isFollowing = true;
                                break;
                            }
                        }
                    }
                    echo '<form method="post" action="follow.php" style="margin:10px 0;">';
                    echo '<input type="hidden" name="follow_userid" value="' . htmlspecialchars($profileUserID) . '">';
                    if ($isFollowing) {
                        echo '<button type="submit" class="themed-btn remove-btn" style="width:100%;">Unfollow</button>';
                    } else {
                        echo '<button type="submit" class="themed-btn" style="width:100%;">Follow</button>';
                    }
                    echo '</form>';
                }
            ?>
            <!-- Friends Bar -->
            <div class="friends-bar">
                <div class="friends-bar-title">Friends</div>
                <div class="friends-list">
                    <?php
                        if($friends) {
                            foreach($friends as $friendRow) {
                                $friendImg = "images/user_male.jpg";
                                if($friendRow['gender'] == "Female") $friendImg = "images/user_female.jpg";
                                if (isset($friendRow['profileImage']) && file_exists($friendRow['profileImage'])) {
                                    $friendImg = $friendRow['profileImage'];
                                }
                                echo '<div class="friend-card">';
                                echo '<a href="profile.php?id=' . htmlspecialchars($friendRow['userid']) . '">';
                                echo '<img src="' . htmlspecialchars($friendImg) . '"><br>';
                                echo htmlspecialchars($friendRow['firstName'] . " " . $friendRow['lastName']);
                                echo '</a></div>';
                            }
                        } else {
                            echo '<div style="color:#888;">No friends yet.</div>';
                        }
                    ?>
                </div>
            </div>
        </div>

        <!-- === Main Content: Post Form + Posts === -->
        <div class="main-content">
            <!-- Post Form -->
            <div class="post-form-card">
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="post" placeholder="What's on your mind?"></textarea>
                    <div style="display: flex; gap: 10px; align-items: center; margin-top:10px;">
                        <input type="file" name="file" class="themed-btn" style="margin-top:0;" id="fileInput">
                        <button type="button" id="addLocationBtn" class="themed-btn" style="margin-top:0;">Add Location</button>
                        <input type="submit" value="Post" id="postButton" class="themed-btn" style="margin-top:0;">
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <div id="mapPreview" style="width:100%;height:200px;margin-top:10px;display:none;"></div>
                </form>
                <?php echo $postError; ?>
            </div>
            <!-- Posts Section -->
            <div id="postSection">
                <?php
                if($posts) {
                    foreach($posts as $row) {
                        $user = new User();
                        $rowUser = $user->getUser($row['userID']);
                        include("post.php");
                    }
                } else {
                    echo "<div style='color: #888; text-align: center;'>No posts to show.</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- === Scripts === -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAwNYG4p8LD_XQP3LVR8pPTS4yISGYiEt4"></script>
    <script src="js/profile.js"></script>
</body>
</html>
