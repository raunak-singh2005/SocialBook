<?php
// Start the session to access session variables
session_start();

// Include the database connection class
include_once("classes/connect.php");

// Get the current user's ID from the session, if available
$userID = isset($_SESSION['SocialBook_userID']) ? $_SESSION['SocialBook_userID'] : null;

$banInfo = null;

if ($userID) {
    // Initialize the database connection
    $DB = new Database();

    // Fetch the most recent ban record for the user
    $ban = $DB->read(
        "SELECT * FROM TempBans WHERE bannedUserID = '$userID' ORDER BY id DESC LIMIT 1"
    );

    if ($ban && isset($ban[0]['dateBanned'])) {
        // Calculate how many days have passed since the ban started
        $dateBanned = new DateTime($ban[0]['dateBanned']);
        $now = new DateTime();
        $interval = $now->diff($dateBanned);
        $daysPassed = $interval->days;

        // Calculate days left for the ban (assuming 30-day ban)
        $daysLeft = 30 - $daysPassed;

        // If the ban date is in the future, set days left to 30
        if ($now < $dateBanned) {
            $daysLeft = 30;
        }

        // Ensure days left is not negative
        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        // Prepare ban information for display
        $banInfo = [
            'reason'     => $ban[0]['reason'],
            'dateBanned' => $ban[0]['dateBanned'],
            'daysLeft'   => $daysLeft
        ];
    }
}
?>

<html>
<head>
    <title>Banned | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Page background and font styling */
        body {
            max-width: 400px;
            margin: 80px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .ban-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ff7675;
            margin-bottom: 18px;
        }
        .ban-reason {
            margin: 18px 0;
            color: #eebbc3;
        }
        .ban-days {
            color: #b8c1ec;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="ban-section">
        <div class="ban-title">You have been temporarily banned</div>
        <?php if ($banInfo): ?>
            <!-- Display ban reason -->
            <div class="ban-reason">
                <b>Reason:</b> <?php echo htmlspecialchars($banInfo['reason']); ?>
            </div>
            <!-- Display days left for the ban -->
            <div class="ban-days">
                Days left: <?php echo $banInfo['daysLeft']; ?>
            </div>
            <!-- Display ban start date -->
            <div style="color:#eaeaea;">
                Ban started: <?php echo htmlspecialchars($banInfo['dateBanned']); ?>
            </div>
        <?php else: ?>
            <!-- Display generic ban message if no ban info is available -->
            <div class="ban-reason">
                You are currently banned from accessing SocialBook.
            </div>
        <?php endif; ?>
        <!-- Logout link -->
        <a href="logout.php" style="color:#ff7675;display:block;margin-top:24px;">Logout</a>
    </div>
</body>
</html>