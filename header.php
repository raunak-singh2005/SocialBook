<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include user class for fetching user data
include_once("classes/user.php");

// Default profile image
$userPic = "assets/Selfie.png";

// If user is logged in, try to get their profile image
if (isset($_SESSION['SocialBook_userID'])) {
    $user = new User();
    $userData = $user->getUser($_SESSION['SocialBook_userID']);
    if (
        $userData &&
        isset($userData['profileImage']) &&
        file_exists($userData['profileImage'])
    ) {
        $userPic = $userData['profileImage'];
    }
}
?>

<!-- Header Bar -->
<div style="background: #23272f; color: #e4e6eb; box-shadow: 0 4px 24px rgba(0,0,0,0.4); border-radius: 0 0 18px 18px; height: 50px;">
    <div style="width: 800px; margin: auto; font-size: 30px; display: flex; align-items: center; height: 50px;">
        
        <!-- Logo / Home Link -->
        <a href="mainboard.php"
           style="text-decoration: none; color: #8f94fb; font-weight: bold; font-size: 28px; letter-spacing: 1px;">
            SocialBook
        </a>
        
        <!-- Right Side: Profile Picture and Logout -->
        <div style="margin-left: auto; display: flex; align-items: center;">
            
            <!-- Profile Picture Link -->
            <a href="profile.php">
                <img src="<?php echo htmlspecialchars($userPic); ?>"
                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; margin-right: 18px; border: 2px solid #181a20; box-shadow: 0 1px 6px rgba(0,0,0,0.18);">
            </a>
            
            <!-- Logout Link -->
            <a href="logout.php" style="text-decoration: none; color: #8f94fb;">
                <span style="font-size: 13px; margin: 0 0 0 8px; font-weight: 600;">Logout</span>
            </a>
        </div>
    </div>
</div>