<?php
// --- Include required class files and ban check ---
include("classes/connect.php");
include("classes/login.php");
include("classes/user.php");
include_once("banCheck.php");

// --- Start session and check if user is banned ---
session_start();
requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

// --- Authenticate user and fetch user data ---
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// --- Fetch followers for the logged-in user ---
$user = new User();
$followers = $user->getFollowers($userData['userid']); // You need to implement this method
?>

<html>
<head>
    <title>Followers | SocialBook</title>
    <style>
        /* --- Page Styles --- */
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
        }
        .followers-section {
            max-width: 500px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .followers-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        .follower-card {
            display: flex;
            align-items: center;
            background: #16161a;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
            box-shadow: 0 1px 6px rgba(20,20,30,0.10);
            border: 1px solid #232946;
        }
        .follower-card img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 14px;
            border: 2px solid #232946;
        }
        .follower-card-name {
            flex: 1;
            color: #eaeaea;
            font-weight: 600;
            font-size: 1.05rem;
        }
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #b8c1ec;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 6px;
            padding: 6px 18px;
            transition: background 0.2s, color 0.2s;
        }
        .back-link:hover {
            background: #eebbc3;
            color: #232946;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="followers-section">
        <div class="followers-title">Your Followers</div>

        <?php
        // --- Display followers if any exist ---
        if ($followers && count($followers) > 0) {
            foreach ($followers as $follower) {
                // --- Determine profile image based on gender or custom image ---
                $profileImg = "images/user_male.jpg";
                if ($follower['gender'] == "Female") $profileImg = "images/user_female.jpg";
                if (isset($follower['profileImage']) && file_exists($follower['profileImage'])) {
                    $profileImg = $follower['profileImage'];
                }

                // --- Prepare follower's full name ---
                $fullName = htmlspecialchars($follower['firstName'] . " " . $follower['lastName']);

                // --- Render follower card ---
                echo '<div class="follower-card">';
                echo '<img src="' . htmlspecialchars($profileImg) . '" alt="Profile">';
                echo '<div class="follower-card-name">' . $fullName . '</div>';
                echo '<a href="profile.php?id=' . htmlspecialchars($follower['userid']) . '" style="color:#eebbc3;">View</a>';
                echo '</div>';
            }
        } else {
            // --- No followers message ---
            echo '<div style="color:#b8c1ec; text-align:center;">No followers yet.</div>';
        }
        ?>

        <!-- --- Back to Profile Link --- -->
        <a href="profile.php" class="back-link">Back to Profile</a>
    </div>
</body>
</html>