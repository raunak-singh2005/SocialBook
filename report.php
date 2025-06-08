<?php
// Include required files for database connection, login, and ban check
include_once("classes/connect.php");
include_once("classes/login.php");
include_once("banCheck.php");

// Start session and check if user is banned
session_start();
requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

// Check user login and get user data
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);

// Initialize variables
$reportedUserID = isset($_GET['user']) ? intval($_GET['user']) : 0;
$reportingUserID = $_SESSION['SocialBook_userID'];
$error = "";
$success = false;

// Handle form submission for reporting a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reportText'])) {
    $reportText = trim($_POST['reportText']);
    $reportedUserID = intval($_POST['reportedUserID']);
    if ($reportText !== "" && $reportedUserID && $reportingUserID) {
        $DB = new Database();
        // Escape report text to prevent SQL injection
        $reportTextEscaped = addslashes($reportText);
        $query = "INSERT INTO Reports (reportedUserID, reportingUserID, reportText) VALUES ('$reportedUserID', '$reportingUserID', '$reportTextEscaped')";
        $DB->write($query);
        $success = true;
    } else {
        $error = "Please enter your report details.";
    }
}
?>

<head>
    <title>Report User | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
            margin: 0;
            padding: 0;
        }
        .report-section {
            max-width: 420px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .report-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        .report-form textarea {
            width: 95%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #b8c1ec;
            border-radius: 8px;
            background: #16161a;
            color: #eaeaea;
            font-size: 1rem;
            outline: none;
            min-height: 100px;
            resize: vertical;
        }
        .report-form input[type="submit"] {
            width: 100%;
            background: #eebbc3;
            color: #232946;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            margin-top: 18px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(238,187,195,0.08);
        }
        .report-form input[type="submit"]:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        .report-success {
            color: #4be18a;
            margin-bottom: 10px;
        }
        .report-error {
            color: #ff6f91;
            margin-bottom: 10px;
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

    <div class="report-section">
        <div class="report-title">Report User</div>

        <!-- Success message -->
        <?php if ($success): ?>
            <div class="report-success">Thank you for your report. Our team will review it soon.</div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="report-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Report form (only show if not successful) -->
        <?php if (!$success): ?>
        <form method="POST" class="report-form">
            <textarea name="reportText" placeholder="Describe the issue..." required></textarea>
            <input type="hidden" name="reportedUserID" value="<?php echo htmlspecialchars($reportedUserID); ?>">
            <input type="submit" value="Save Report">
        </form>
        <?php endif; ?>

        <!-- Back link -->
        <a href="mainboard.php" class="back-link">Back to Mainboard</a>
    </div>
</body>