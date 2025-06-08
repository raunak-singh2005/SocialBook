<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['SocialBook_adminID'])) {
    header("Location: adminLogin.php");
    die;
}

// Include database connection
include_once("classes/connect.php");

// Initialize variables
$userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;
$success = "";
$error = "";

// Handle form submission for banning a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userid'], $_POST['reason'])) {
    $userid = intval($_POST['userid']);
    $reason = trim($_POST['reason']);

    if ($userid && $reason !== "") {
        $DB = new Database();
        $dateBanned = date("Y-m-d H:i:s");
        // Escape reason to prevent SQL injection
        $reasonEscaped = addslashes($reason);
        $DB->write("INSERT INTO TempBans (bannedUserID, dateBanned, reason) VALUES ('$userid', '$dateBanned', '$reasonEscaped')");
        $success = "User has been temporarily banned for 30 days.";
    } else {
        $error = "Please provide a reason for the ban.";
    }
}
?>

<html>
<head>
    <title>Start Temporary Ban | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #232946; color: #eaeaea; font-family: 'Segoe UI', Arial, sans-serif; }
        .ban-section {
            max-width: 400px; margin: 60px auto 0 auto; background: #232946;
            border-radius: 18px; box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px; text-align: center;
        }
        .ban-title { font-size: 1.5rem; font-weight: 700; color: #eebbc3; margin-bottom: 18px; }
        .ban-form textarea {
            width: 90%; padding: 10px 12px; margin: 10px 0; border: 1px solid #b8c1ec;
            border-radius: 8px; background: #16161a; color: #eaeaea; font-size: 1rem; outline: none; min-height: 80px;
        }
        .ban-form input[type="submit"] {
            width: 100%; background: #eebbc3; color: #232946; font-weight: 700; font-size: 1.1rem;
            border: none; border-radius: 8px; padding: 10px 0; margin-top: 18px; cursor: pointer;
        }
        .success-message { color: #4be18a; margin-bottom: 12px; }
        .error-message { color: #ff7675; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="ban-section">
        <div class="ban-title">Temporary Ban User</div>
        <?php
        // Show success message if ban was successful
        if ($success) {
            echo "<div class='success-message'>$success</div>";
            echo "<a href='adminPage.php' style='color:#eebbc3;'>Back to Admin Page</a>";
        } else {
            // Show error message if any
            if ($error) echo "<div class='error-message'>$error</div>";
        ?>
        <!-- Ban form -->
        <form method="post" class="ban-form">
            <input type="hidden" name="userid" value="<?php echo htmlspecialchars($userid); ?>">
            <textarea name="reason" placeholder="Reason for ban..." required></textarea><br>
            <input type="submit" value="Ban for 30 Days">
        </form>
        <a href="adminPage.php" style="color:#eebbc3;">Cancel</a>
        <?php } ?>
    </div>
</body>
</html>