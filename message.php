<?php
// Start session and include dependencies
session_start();
include("classes/connect.php");
include("classes/message.php");
include_once("banCheck.php");

// Ensure user is not banned
requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

// Check if user is logged in
if (!isset($_SESSION['SocialBook_userID'])) {
    die("You must be logged in to view messages.");
}

$currentUser = $_SESSION['SocialBook_userID'];
$otherUser = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Ensure another user is selected
if ($otherUser === 0) {
    die("No user selected.");
}

// Initialize database and fetch the other user's name
$DB = new Database();
$userRow = false;
$userQuery = "SELECT firstName, lastName FROM Users WHERE userid = '$otherUser' LIMIT 1";
$userResult = $DB->read($userQuery);

if ($userResult && isset($userResult[0]['firstName'])) {
    $otherUsername = htmlspecialchars($userResult[0]['firstName'] . ' ' . $userResult[0]['lastName']);
} else {
    $otherUsername = "Unknown User";
}

$dm = new DirectMessage();

// --- Block List Checks ---

// Get current user's block list
$blockList = [];
$blockListArr = $DB->read("SELECT blockList FROM Users WHERE userid = '$currentUser' LIMIT 1");
if ($blockListArr && isset($blockListArr[0]['blockList']) && $blockListArr[0]['blockList']) {
    $blockListDecoded = json_decode($blockListArr[0]['blockList'], true);
    if (is_array($blockListDecoded)) {
        foreach ($blockListDecoded as $entry) {
            $blockList[] = $entry['userid'];
        }
    }
}

// Get other user's block list
$otherBlockList = [];
$otherBlockListArr = $DB->read("SELECT blockList FROM Users WHERE userid = '$otherUser' LIMIT 1");
if ($otherBlockListArr && isset($otherBlockListArr[0]['blockList']) && $otherBlockListArr[0]['blockList']) {
    $otherBlockListDecoded = json_decode($otherBlockListArr[0]['blockList'], true);
    if (is_array($otherBlockListDecoded)) {
        foreach ($otherBlockListDecoded as $entry) {
            $otherBlockList[] = $entry['userid'];
        }
    }
}

// If either user has blocked the other, prevent messaging
if (in_array($otherUser, $blockList) || in_array($currentUser, $otherBlockList)) {
    die("You cannot message this user.");
}

// --- Message Sending ---

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== "") {
        $dm->sendMessage($currentUser, $otherUser, $msg);
        header("Location: message.php?user=" . $otherUser);
        exit;
    }
}

// --- Fetch Messages ---

$messages = $dm->getMessages($currentUser, $otherUser);
?>

<html>
<head>
    <title>Direct Messages with <?php echo $otherUsername; ?> | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* --- Styles for DM Page --- */
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
            margin: 0;
            padding: 0;
        }
        .dm-container {
            max-width: 540px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
        }
        .dm-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
            text-align: center;
        }
        .messages-list {
            max-height: 340px;
            overflow-y: auto;
            margin-bottom: 24px;
            padding-right: 6px;
        }
        .message-bubble {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 12px;
            max-width: 75%;
            word-break: break-word;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(20,20,30,0.10);
            position: relative;
        }
        .message-bubble.you {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            margin-left: auto;
            text-align: right;
        }
        .message-bubble.other {
            background: #16161a;
            color: #eebbc3;
            margin-right: auto;
            text-align: left;
        }
        .message-meta {
            font-size: 0.85em;
            color: #b8c1ec;
            margin-top: 4px;
            display: block;
        }
        .dm-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .dm-form textarea {
            width: 100%;
            border: none;
            font-size: 1rem;
            border-radius: 10px;
            background: #16161a;
            color: #eaeaea;
            padding: 10px;
            min-height: 60px;
            resize: vertical;
            outline: none;
            box-shadow: 0 1px 4px rgba(0,0,0,0.10);
        }
        .dm-form button {
            background: #eebbc3;
            color: #232946;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(238,187,195,0.08);
        }
        .dm-form button:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        @media (max-width: 600px) {
            .dm-container {
                padding: 16px 4px 12px 4px;
            }
            .dm-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="dm-container">
        <!-- DM Title -->
        <div class="dm-title">Direct Messages with <?php echo $otherUsername; ?></div>

        <!-- Messages List -->
        <div class="messages-list">
            <?php
            // Display messages or a placeholder if none exist
            if ($messages) {
                foreach ($messages as $row) {
                    $isYou = $row['senderID'] == $currentUser;
                    $bubbleClass = $isYou ? "you" : "other";
                    $sender = $isYou ? "You" : $otherUsername;
                    echo '<div class="message-bubble ' . $bubbleClass . '">';
                    echo htmlspecialchars($row['message']);
                    echo '<span class="message-meta">' . $sender . ' &middot; ' . htmlspecialchars($row['date']) . '</span>';
                    echo '</div>';
                }
            } else {
                echo "<div style='color:#b8c1ec; text-align:center;'>No messages yet.</div>";
            }
            ?>
        </div>

        <!-- Message Form -->
        <form method="POST" class="dm-form">
            <textarea name="message" required placeholder="Type your message..."></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
    window.currentUserID = <?php echo json_encode($currentUser); ?>;
    window.otherUserID = <?php echo json_encode($otherUser); ?>;
    window.otherUsername = <?php echo json_encode($otherUsername); ?>;
    </script>
    <script src="js/message.js"></script>
</body>
</html>