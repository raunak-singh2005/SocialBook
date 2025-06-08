<?php
// -------------------- SESSION & AUTH --------------------
// Start session and check admin authentication
session_start();
if (!isset($_SESSION['SocialBook_adminID'])) {
    header("Location: adminLogin.php");
    die;
}

// -------------------- INCLUDES --------------------
// Include required classes
include_once("classes/connect.php");
include_once("classes/user.php");

// -------------------- DATABASE FETCHES --------------------
$DB = new Database();

// Fetch all users for Users tab
$users = $DB->read("SELECT * FROM Users ORDER BY date DESC");

// Fetch all reports for Reports tab (with reporter and reported user names)
$reports = $DB->read(
    "SELECT r.*, 
            u1.firstName AS reportedFirst, u1.lastName AS reportedLast, 
            u2.firstName AS reporterFirst, u2.lastName AS reporterLast 
     FROM Reports r 
     LEFT JOIN Users u1 ON r.reportedUserID = u1.userid 
     LEFT JOIN Users u2 ON r.reportingUserID = u2.userid 
     ORDER BY r.id DESC"
);

// Fetch all bans for Ban Log tab (with banned user names)
$bans = $DB->read(
    "SELECT b.*, u.firstName, u.lastName 
     FROM TempBans b 
     LEFT JOIN Users u ON b.bannedUserID = u.userid 
     ORDER BY b.id DESC"
);

// -------------------- POST LOG FILTERING --------------------
// Get filter parameters from GET
$filter = isset($_GET['postlog_filter']) ? $_GET['postlog_filter'] : 'all';
$userFilter = isset($_GET['postlog_userid']) ? trim($_GET['postlog_userid']) : '';
$whereClauses = [];

// Build WHERE clauses for post log filtering
if ($filter === 'hour') {
    $whereClauses[] = "p.date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
} elseif ($filter === 'week') {
    $whereClauses[] = "p.date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter === 'month') {
    $whereClauses[] = "p.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($filter === 'year') {
    $whereClauses[] = "p.date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}
if ($userFilter !== '') {
    $whereClauses[] = "p.userID = '" . addslashes($userFilter) . "'";
}
$dateFilterSQL = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Fetch posts for Post Log tab (with user info)
$postsQuery = "SELECT p.*, u.firstName, u.lastName, u.email 
               FROM Posts p 
               LEFT JOIN Users u ON p.userID = u.userid 
               $dateFilterSQL 
               ORDER BY p.date DESC";
$postLogs = $DB->read($postsQuery);
?>

<html>
<head>
    <title>Admin Panel | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* ----------- ADMIN PANEL STYLES ----------- */
        body {
            background: #121629;
            color: #eebbc3;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background: #232946;
            width: 220px;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        .sidebar-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #eebbc3;
            padding: 24px 0 18px 0;
            text-align: center;
            border-bottom: 1px solid #b8c1ec;
            margin-bottom: 12px;
        }
        .sidebar-tab {
            background: none;
            border: none;
            color: #b8c1ec;
            padding: 18px 0;
            font-size: 1.1rem;
            cursor: pointer;
            text-align: left;
            padding-left: 32px;
            transition: background 0.18s, color 0.18s;
        }
        .sidebar-tab.active, .sidebar-tab:hover {
            background: #eebbc3;
            color: #232946;
        }
        .main-content {
            flex: 1;
            padding: 32px 36px;
            background: #16182b;
            min-height: 100vh;
        }
        .tab-section {
            display: none;
        }
        .tab-section.active {
            display: block;
        }
        .user-table, .report-table, .ban-table, .post-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .user-table th, .user-table td,
        .report-table th, .report-table td,
        .ban-table th, .ban-table td,
        .post-table th, .post-table td {
            border: 1px solid #393e5c;
            padding: 10px 8px;
            text-align: left;
        }
        .user-table th, .report-table th, .ban-table th, .post-table th {
            background: #232946;
            color: #eebbc3;
        }
        .user-table tr:nth-child(even),
        .report-table tr:nth-child(even),
        .ban-table tr:nth-child(even),
        .post-table tr:nth-child(even) { background: #232946; }
        .user-table tr:nth-child(odd),
        .report-table tr:nth-child(odd),
        .ban-table tr:nth-child(odd),
        .post-table tr:nth-child(odd) { background: #181a20; }
        .filter-btn-group {
            margin-bottom: 18px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: #232946;
            color: #eebbc3;
            border: 1px solid #b8c1ec;
            border-radius: 6px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.18s, color 0.18s;
        }
        .filter-btn.active, .filter-btn:hover {
            background: #eebbc3;
            color: #232946;
        }
        .admin-form input[type="text"],
        .admin-form input[type="email"],
        .admin-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: 1px solid #b8c1ec;
            background: #232946;
            color: #eebbc3;
        }
        .admin-form input[type="submit"] {
            background: #eebbc3;
            color: #232946;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
        }
        .admin-form input[type="submit"]:hover {
            background: #232946;
            color: #eebbc3;
        }
        .success-message {
            background: #2ecc71;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            margin-bottom: 18px;
        }
        .error-message {
            background: #ff7675;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <!-- ========== SIDEBAR NAVIGATION ========== -->
    <div class="sidebar">
        <div class="sidebar-title">Admin Panel</div>
        <button class="sidebar-tab active" onclick="showTab('newAdmin')">New Admin</button>
        <button class="sidebar-tab" onclick="showTab('users')">Users</button>
        <button class="sidebar-tab" onclick="showTab('reports')">User Reports</button>
        <button class="sidebar-tab" onclick="showTab('banlog')">Ban Log</button>
        <button class="sidebar-tab" onclick="showTab('postlog')">Post Log</button>
        <a href="logout.php" class="sidebar-tab" style="color:#ff7675;">Logout</a>
    </div>

    <div class="main-content">
        <!-- ========== NEW ADMIN TAB ========== -->
        <div id="tab-newAdmin" class="tab-section active">
            <h2>Create New Admin</h2>
            <?php
            // Handle new admin creation
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
                include_once("classes/adminLogin.php");
                $adminLogin = new AdminLogin();
                $err = $adminLogin->createAdmin($_POST);
                if ($err) {
                    echo "<div class='error-message'>$err</div>";
                } else {
                    echo "<div class='success-message'>New admin created successfully.</div>";
                }
            }
            ?>
            <form method="post" class="admin-form" autocomplete="off">
                <input name="firstName" type="text" placeholder="First Name" required>
                <input name="lastName" type="text" placeholder="Last Name" required>
                <input name="email" type="email" placeholder="Email" required>
                <input name="password" type="password" placeholder="Password" required>
                <input type="submit" value="Create Admin">
            </form>
        </div>

        <!-- ========== USERS TAB ========== -->
        <div id="tab-users" class="tab-section">
            <h2>All Users</h2>
            <?php
            // Handle user deletion and related data cleanup
            if (isset($_POST['delete_userid']) && is_numeric($_POST['delete_userid'])) {
                $deleteUserId = intval($_POST['delete_userid']);
                $DB = new Database();

                // Delete user's posts and images
                $posts = $DB->read("SELECT image FROM Posts WHERE userID = '$deleteUserId'");
                if ($posts) {
                    foreach ($posts as $post) {
                        if (!empty($post['image']) && file_exists($post['image'])) {
                            @unlink($post['image']);
                        }
                    }
                }
                $DB->write("DELETE FROM Posts WHERE userID = '$deleteUserId'");

                // Delete user's comments
                $DB->write("DELETE FROM Comments WHERE userID = '$deleteUserId' OR postOwnerID = '$deleteUserId'");

                // Delete user's messages
                $DB->write("DELETE FROM DirectMessages WHERE senderID = '$deleteUserId' OR receiverID = '$deleteUserId'");

                // Delete user's uploads directory
                $userUploads = __DIR__ . "/uploads/$deleteUserId";
                if (is_dir($userUploads)) {
                    $files = glob("$userUploads/*");
                    foreach ($files as $file) {
                        if (is_file($file)) @unlink($file);
                    }
                    @rmdir($userUploads);
                }

                // Delete user from Users table
                $DB->write("DELETE FROM Users WHERE userid = '$deleteUserId'");

                // Remove from Follows, Likes, Reports, etc.
                $DB->write("DELETE FROM Follows WHERE userID = '$deleteUserId'");
                $DB->write("DELETE FROM Likes WHERE JSON_CONTAINS(likes, '\"$deleteUserId\"', '$')");
                $DB->write("DELETE FROM Reports WHERE reportedUserID = '$deleteUserId' OR reportingUserID = '$deleteUserId'");

                // Refresh users list
                $users = $DB->read("SELECT * FROM Users ORDER BY date DESC");

                echo "<div class='success-message'>User and all related data deleted.</div>";
            }
            ?>
            <table class="user-table">
                <tr>
                    <th>UserID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Date Joined</th>
                    <th>Action</th>
                </tr>
                <?php
                // Display all users
                if ($users) {
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['userid']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['firstName'] . " " . $user['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['gender']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['date']) . "</td>";
                        echo "<td>
                            <form method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this user and all their data?');\">
                                <input type='hidden' name='delete_userid' value='" . htmlspecialchars($user['userid']) . "'>
                                <input type='submit' value='Delete' style='background:#ff7675;color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;'>
                            </form>
                            <form method='get' action='startBan.php' style='display:inline;margin-left:6px;'>
                                <input type='hidden' name='userid' value='" . htmlspecialchars($user['userid']) . "'>
                                <input type='submit' value='Temp Ban' style='background:#eebbc3;color:#232946;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;'>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No users found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- ========== REPORTS TAB ========== -->
        <div id="tab-reports" class="tab-section">
            <h2>User Reports</h2>
            <table class="report-table">
                <tr>
                    <th>Report ID</th>
                    <th>Reported User</th>
                    <th>Reporter</th>
                    <th>Report Text</th>
                    <th>Date</th>
                </tr>
                <?php
                // Display all reports
                if ($reports) {
                    foreach ($reports as $report) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($report['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['reportedFirst'] . " " . $report['reportedLast']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['reporterFirst'] . " " . $report['reporterLast']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['reportText']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No reports found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- ========== BAN LOG TAB ========== -->
        <div id="tab-banlog" class="tab-section">
            <h2>Ban Log</h2>
            <table class="ban-table">
                <tr>
                    <th>Ban ID</th>
                    <th>User</th>
                    <th>UserID</th>
                    <th>Date Banned</th>
                    <th>Reason</th>
                    <th>Days Left</th>
                </tr>
                <?php
                // Display all bans and calculate days left
                if ($bans) {
                    foreach ($bans as $ban) {
                        $dateBanned = new DateTime($ban['dateBanned']);
                        $now = new DateTime();
                        $interval = $now->diff($dateBanned);
                        $daysPassed = $interval->days;
                        $daysLeft = 30 - $daysPassed;
                        if ($now < $dateBanned) $daysLeft = 30; // Defensive: if future date
                        if ($daysLeft < 0) $daysLeft = 0;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($ban['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($ban['firstName'] . " " . $ban['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($ban['bannedUserID']) . "</td>";
                        echo "<td>" . htmlspecialchars($ban['dateBanned']) . "</td>";
                        echo "<td>" . htmlspecialchars($ban['reason']) . "</td>";
                        echo "<td>" . $daysLeft . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No bans found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- ========== POST LOG TAB ========== -->
        <div id="tab-postlog" class="tab-section">
            <h2>Post Log</h2>
            <!-- Filter Form -->
            <form method="get" class="filter-btn-group" style="margin-bottom:0;flex-wrap:wrap;gap:12px;">
                <input type="hidden" name="tab" value="postlog">
                <button type="submit" name="postlog_filter" value="hour" class="filter-btn<?php if($filter==='hour') echo ' active'; ?>">Last Hour</button>
                <button type="submit" name="postlog_filter" value="week" class="filter-btn<?php if($filter==='week') echo ' active'; ?>">Last Week</button>
                <button type="submit" name="postlog_filter" value="month" class="filter-btn<?php if($filter==='month') echo ' active'; ?>">Last Month</button>
                <button type="submit" name="postlog_filter" value="year" class="filter-btn<?php if($filter==='year') echo ' active'; ?>">Last Year</button>
                <button type="submit" name="postlog_filter" value="all" class="filter-btn<?php if($filter==='all') echo ' active'; ?>">All Time</button>
                <input type="text" name="postlog_userid" placeholder="Filter by UserID" value="<?php echo htmlspecialchars($userFilter); ?>" style="padding:6px 10px;border-radius:6px;border:1px solid #b8c1ec;background:#232946;color:#eebbc3;min-width:120px;">
                <button type="submit" class="filter-btn" style="padding:6px 14px;">Apply</button>
            </form>
            <table class="post-table">
                <tr>
                    <th>Post ID</th>
                    <th>User</th>
                    <th>UserID</th>
                    <th>Email</th>
                    <th>Content</th>
                    <th>Image</th>
                    <th>Date</th>
                </tr>
                <?php
                // Display all posts with filters
                if ($postLogs) {
                    foreach ($postLogs as $post) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($post['postID']) . "</td>";
                        echo "<td>" . htmlspecialchars($post['firstName'] . " " . $post['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($post['userID']) . "</td>";
                        echo "<td>" . htmlspecialchars($post['email']) . "</td>";
                        echo "<td style='max-width:300px;overflow:auto;'>" . nl2br(htmlspecialchars($post['post'])) . "</td>";
                        if (!empty($post['image']) && file_exists($post['image'])) {
                            echo "<td><img src='" . htmlspecialchars($post['image']) . "' alt='Post Image' style='max-width:80px;max-height:80px;object-fit:cover;'></td>";
                        } else {
                            echo "<td>-</td>";
                        }
                        echo "<td>" . htmlspecialchars($post['date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No posts found for this period.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</div>
<script src="js/adminPage.js"></script>
</body>
</html>
