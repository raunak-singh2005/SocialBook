<?php
    // --- PHP SECTION: Initialization and Data Fetching ---

    // Include required class files and ban check
    include("classes/connect.php");
    include("classes/login.php");
    include("classes/user.php");
    include("classes/post.php");
    include("classes/image.php");
    include_once("banCheck.php");
    session_start();

    // Ban check for current user
    requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

    // User authentication and data
    $login = new Login();
    $userData = $login->checkLogin($_SESSION['SocialBook_userID']);

    // Fetch latest 50 posts
    $post = new Post();
    $DB = new Database();
    $query = "SELECT * FROM Posts ORDER BY id DESC LIMIT 50";
    $allPosts = $DB->read($query);

    // Fetch all users (friends) except current
    $user = new User();
    $allUsers = $user->getFriends($userData['userid']);

    // Get current user's following list as array of userids
    $currentUserFollowingArr = (new User())->getFollowing($userData['userid']);
    $currentUserFollowing = [];
    if (is_array($currentUserFollowingArr)) {
        foreach ($currentUserFollowingArr as $f) {
            if (isset($f['userid'])) {
                $currentUserFollowing[] = $f['userid'];
            }
        }
    }

    // Get block list as array of userids
    $blockList = [];
    if (isset($userData['blockList']) && $userData['blockList']) {
        $blockListArr = json_decode($userData['blockList'], true);
        if (is_array($blockListArr)) {
            foreach ($blockListArr as $entry) {
                if (isset($entry['userid'])) {
                    $blockList[] = $entry['userid'];
                }
            }
        }
    }
?>

<html>
<head>
    <title>SocialBook | Main Board</title>
    <style>
        /* --- CSS SECTION: Layout and Styling --- */
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
        }
        .mainboard-layout {
            display: flex;
            gap: 32px;
            max-width: 1200px;
            margin: 40px auto 0 auto;
            padding: 0 16px;
            align-items: flex-start;
        }
        .sidebar-users {
            flex: 0 0 390px;
            max-width: 420px;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 24px 16px;
            height: fit-content;
            max-height: 80vh;
            overflow-y: auto;
        }
        .sidebar-users-title {
            color: #eebbc3;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 18px;
            text-align: center;
        }
        .user-card {
            position: relative;
            display: flex;
            align-items: center;
            background: #16161a;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
            box-shadow: 0 1px 6px rgba(20,20,30,0.10);
            border: 1px solid #232946;
            min-width: 340px;
            max-width: 100%;
        }
        .user-card img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 14px;
            border: 2px solid #232946;
        }
        .user-card-name {
            flex: 1;
            color: #eaeaea;
            font-weight: 600;
            font-size: 1.05rem;
            margin-right: 18px;
        }
        .follow-btn {
            background: #eebbc3;
            color: #232946;
            border: none;
            border-radius: 8px;
            padding: 6px 18px;
            font-weight: 600;
            font-size: 0.98rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .follow-btn:hover {
            background: #f6c7d1;
            color: #16161a;
        }
        .mainboard-container {
            width: 100%;
            padding: 0;
        }
        .mainboard-content {
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.25);
            padding: 32px 24px;
        }
        .mainboard-title {
            color: #eebbc3;
            font-size: 2rem;
            margin-bottom: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .post-section {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
        }
        .post-card {
            background: #16161a;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(20,20,30,0.18);
            padding: 14px 12px;
            transition: box-shadow 0.2s, transform 0.2s;
            border: 1px solid #232946;
            width: 340px;
            max-width: 100%;
            margin: 0;
        }
        .post-card:hover {
            box-shadow: 0 6px 24px rgba(238,187,195,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .no-posts {
            color: #b8c1ec;
            text-align: center;
            font-size: 1.1rem;
            padding: 40px 0;
        }
        .post-card img {
            width: 140px;
            height: auto;
            display: block;
            margin: 12px auto 0 auto;
            border-radius: 10px;
            object-fit: contain;
            box-shadow: 0 2px 8px rgba(20,20,30,0.10);
        }
        ::-webkit-scrollbar {
            width: 10px;
            background: #232946;
        }
        ::-webkit-scrollbar-thumb {
            background: #16161a;
            border-radius: 8px;
        }
        .user-card-actions {
            display: flex;
            gap: 8px;
            position: absolute;
            right: 10px;
            top: 60%;
            transform: translateY(-50%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 2;
        }
        .user-card:hover .user-card-actions {
            opacity: 1;
            pointer-events: auto;
        }
        .user-card-actions-dropdown {
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: absolute;
            left: 16px;
            top: 100%;
            min-width: 180px;
            background: #232946;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(20,20,30,0.18);
            padding: 12px 16px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s, transform 0.22s;
            transform: translateY(8px);
            z-index: 10;
        }
        .user-card-container:hover .user-card-actions-dropdown {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        .block-btn, .report-btn {
            background: #ff7675;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .report-btn {
            background: #eebbc3;
            color: #232946;
        }
        .block-btn:hover {
            background: #d63031;
        }
        .report-btn:hover {
            background: #f6c7d1;
            color: #16161a;
        }
        @media (max-width: 1000px) {
            .mainboard-layout { flex-direction: column; gap: 0; }
            .sidebar-users { margin-bottom: 32px; }
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="mainboard-layout">
        <!-- --- SIDEBAR: User List --- -->
        <div class="sidebar-users">
            <div class="sidebar-users-title">All Users</div>
            <input type="text" id="userSearch" placeholder="Search users..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid #b8c1ec; margin-bottom: 16px; background: #16161a; color: #eaeaea; font-size: 1rem;">
            <div id="userList">
            <?php
                // Render user cards
                if ($allUsers) {
                    foreach ($allUsers as $userRow) {
                        // Determine profile image
                        $profileImg = "images/user_male.jpg";
                        if ($userRow['gender'] == "Female") $profileImg = "images/user_female.jpg";
                        if (isset($userRow['profileImage']) && file_exists($userRow['profileImage'])) {
                            $profileImg = $userRow['profileImage'];
                        }
                        $fullName = htmlspecialchars($userRow['firstName'] . " " . $userRow['lastName']);
                        echo '<div class="user-card-container" data-name="' . strtolower($fullName) . '">';
                        echo '<div class="user-card">';
                        echo '<img src="' . htmlspecialchars($profileImg) . '" alt="Profile">';
                        echo '<div class="user-card-name">' . $fullName . '</div>';
                        // Follow/Unfollow button
                        echo '<form method="post" action="follow.php" style="margin:0;display:inline;">';
                        echo '<input type="hidden" name="follow_userid" value="' . htmlspecialchars($userRow['userid']) . '">';
                        if (in_array($userRow['userid'], $currentUserFollowing)) {
                            echo '<button type="submit" class="follow-btn">Unfollow</button>';
                        } else {
                            echo '<button type="submit" class="follow-btn">Follow</button>';
                        }
                        echo '</form>';
                        // Message button
                        echo '<a href="message.php?user=' . htmlspecialchars($userRow['userid']) . '" class="follow-btn" style="margin-left:8px;text-decoration:none;font-size:1.2em;padding:6px 14px;">💬</a>';
                        // More dropdown button
                        echo '<button class="more-btn" type="button" onclick="toggleDropdown(this)" style="margin-left:8px;background:#b8c1ec;color:#232946;border:none;border-radius:8px;padding:6px 14px;cursor:pointer;">More</button>';
                        echo '</div>'; // close user-card

                        // Dropdown for Block/Report
                        $isBlocked = in_array($userRow['userid'], $blockList);
                        echo '<div class="user-card-actions-dropdown" style="display:none;flex-direction:column;gap:8px;position:relative;background:#232946;border-radius:10px;box-shadow:0 4px 16px rgba(20,20,30,0.18);padding:12px 16px;margin-top:8px;">';
                        if ($isBlocked) {
                            echo '<a href="unblock.php?user=' . htmlspecialchars($userRow['userid']) . '" class="block-btn" style="background:#b8c1ec;color:#232946;border:none;border-radius:8px;padding:6px 14px;font-weight:600;font-size:0.95rem;cursor:pointer;min-width:90px;text-align:center;display:inline-block;text-decoration:none;">Unblock</a>';
                        } else {
                            echo '<a href="block.php?user=' . htmlspecialchars($userRow['userid']) . '" class="block-btn" style="background:#ff7675;color:#fff;border:none;border-radius:8px;padding:6px 14px;font-weight:600;font-size:0.95rem;cursor:pointer;min-width:90px;text-align:center;display:inline-block;text-decoration:none;">Block</a>';
                        }
                        echo '<a href="report.php?user=' . htmlspecialchars($userRow['userid']) . '" class="report-btn" style="background:#eebbc3;color:#232946;border:none;border-radius:8px;padding:6px 14px;font-weight:600;font-size:0.95rem;cursor:pointer;min-width:90px;text-align:center;display:inline-block;text-decoration:none;">Report</a>';
                        echo '</div>';
                        echo '</div>'; // close user-card-container
                    }
                } else {
                    echo '<div style="color:#b8c1ec; text-align:center;">No users found.</div>';
                }
            ?>
            </div>
        </div>

        <!-- --- MAINBOARD: Posts Section --- -->
        <div class="mainboard-container" style="flex:1;">
            <div class="mainboard-content">
                <h2 class="mainboard-title">Main Board - All Posts</h2>
                <div id="postSection" class="post-section">
                    <?php
                        // Render posts, skipping those from blocked users
                        if($allPosts) {
                            foreach($allPosts as $row) {
                                if (in_array($row['userID'], $blockList)) continue;
                                $user = new User();
                                $rowUser = $user->getUser($row['userID']);
                                echo '<div class="post-card">';
                                include("post.php");
                                echo '</div>';
                            }
                        } else {
                            echo "<div class='no-posts'>No posts to show.</div>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/mainboard.js"></script>
</body>

</html>