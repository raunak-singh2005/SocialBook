<?php
// Include required class files and ban check
include("classes/connect.php");
include("classes/login.php");
include("classes/user.php");
include("classes/image.php");
include_once("banCheck.php");

// Start session and check if user is banned
session_start();
requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

// Authenticate user and get user data
$login = new Login();
$userData = $login->checkLogin($_SESSION['SocialBook_userID']);
$error = "";

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $userid = $userData['userid'];
    $image = new Image();

    // Check if a file is selected
    if (!empty($_FILES['profile_pic']['name'])) {
        $folder = "uploads/" . $userid . "/";
        // Create user folder if it doesn't exist
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        // Get file extension and validate
        $fileInfo = pathinfo($_FILES['profile_pic']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension, $allowed)) {
            // Generate unique filename and move uploaded file
            $filename = $image->generateFilename(15) . "." . $extension;
            $destination = $folder . $filename;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination);

            // Optionally resize/crop the image
            $image->resizeImage($destination, $destination, 600, 600, $extension);

            // Update profile image path in database
            $DB = new Database();
            $query = "UPDATE Users SET profileImage = '$destination' WHERE userid = '$userid' LIMIT 1";
            $DB->write($query);

            // Redirect on success
            header("Location: settings.php?success=1");
            die;
        } else {
            $error = "Only jpg, jpeg, png, and gif files are allowed.";
        }
    } else {
        $error = "Please select an image file.";
    }
}
?>

<html>
<head>
    <title>Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* General body styles */
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
            margin: 0;
            padding: 0;
        }
        /* Navbar styles */
        .navbar {
            background: #232946;
            padding: 24px 0 18px 0;
            text-align: center;
            box-shadow: 0 2px 12px rgba(20,20,30,0.18);
        }
        .navbar-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #eebbc3;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        /* Settings section styles */
        .settings-section {
            max-width: 420px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .settings-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        .settings-success {
            color: #4be18a;
            margin-bottom: 10px;
        }
        .settings-error {
            color: #ff6f91;
            margin-bottom: 10px;
        }
        /* Form styles */
        .settings-form label {
            color: #b8c1ec;
            font-size: 1.05rem;
        }
        .settings-form input[type="file"] {
            margin: 18px 0 10px 0;
            color: #eaeaea;
        }
        .settings-form input[type="submit"] {
            width: 100%;
            background: #eebbc3;
            color: #232946;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(238,187,195,0.08);
        }
        .settings-form input[type="submit"]:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        /* Back link styles */
        .settings-back {
            display: inline-block;
            margin-top: 18px;
            color: #b8c1ec;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 6px;
            padding: 6px 18px;
            transition: background 0.2s, color 0.2s;
        }
        .settings-back:hover {
            background: #eebbc3;
            color: #232946;
            text-decoration: none;
        }
        /* Responsive styles */
        @media (max-width: 600px) {
            .settings-section {
                padding: 18px 4px 16px 4px;
            }
            .navbar-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-title">SocialBook</div>
    </div>
    <!-- Settings Section -->
    <div class="settings-section">
        <div class="settings-title">Settings</div>
        <!-- Success message -->
        <?php if(isset($_GET['success'])): ?>
            <div class="settings-success">Profile picture updated successfully!</div>
        <?php endif; ?>
        <!-- Error message -->
        <?php if($error != ""): ?>
            <div class="settings-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <!-- Profile picture upload form -->
        <form method="POST" enctype="multipart/form-data" class="settings-form">
            <label for="profile_pic"><b>Upload New Profile Picture:</b></label><br>
            <input type="file" name="profile_pic" accept="image/*"><br>
            <input type="submit" value="Upload" id="postButton">
        </form>
        <!-- Back to profile link -->
        <a href="profile.php" class="settings-back">Back to Profile</a>
    </div>
</body>
</html>