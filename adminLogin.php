<?php
    // Start the session to manage admin login state
    session_start();

    // Initialize error message variable
    $error = "";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Include database connection and admin login logic
        include("classes/connect.php");
        include("classes/adminLogin.php");

        // Create AdminLogin object and evaluate login credentials
        $adminLogin = new AdminLogin();
        $error = $adminLogin->evaluate($_POST);
    }
?>

<html>
<head>
    <title>Admin Login | SocialBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* General body styles */
        body {
            background: #232946;
            color: #eaeaea;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        /* Login section container */
        .admin-login-section {
            max-width: 350px;
            margin: 60px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        /* Title styling */
        .admin-login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        /* Input fields styling */
        .admin-login-form input[type="email"],
        .admin-login-form input[type="password"] {
            width: 90%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #b8c1ec;
            border-radius: 8px;
            background: #16161a;
            color: #eaeaea;
            font-size: 1rem;
            outline: none;
        }
        /* Submit button styling */
        .admin-login-form input[type="submit"] {
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
        }
        /* Error message styling */
        .error-message {
            color: #ff7675;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="admin-login-section">
        <!-- Login Title -->
        <div class="admin-login-title">Admin Login</div>
        
        <!-- Display error message if any -->
        <?php if($error != "") echo "<div class='error-message'>$error</div>"; ?>
        
        <!-- Admin Login Form -->
        <form method="post" action="" class="admin-login-form">
            <input name="email" type="email" placeholder="Admin Email" required><br>
            <input name="password" type="password" placeholder="Password" required><br>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>