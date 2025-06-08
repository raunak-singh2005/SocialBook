<?php
    // Start the session
    session_start();

    // Include required files for database connection, login logic, and ban checking
    include("classes/connect.php");
    include("classes/login.php");
    include_once("banCheck.php");

    // Check if the user is banned before proceeding
    requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

    // Initialize variables for form fields
    $email = "";
    $password = "";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $login = new Login();
        $result = $login->evaluate($_POST);

        // Get submitted email and password for repopulating the form
        $password = $_POST['password'];
        $email = $_POST['email'];

        if($result == "") {
            // Redirect to mainboard if login is successful
            header("Location: mainboard.php");
            die;
        } else {
            // Display error message if login fails
            echo "<div style='color: #ff6f91; text-align: center; margin-top:16px;'>" . $result . "</div>";
        }
    }
?>

<html>
<head>
    <title>SocialBook | Login</title>
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
        .navbar-buttons {
            margin-top: 10px;
        }
        .navbar-btn {
            display: inline-block;
            background: #eebbc3;
            color: #232946;
            font-weight: 600;
            font-size: 1.05rem;
            border: none;
            border-radius: 8px;
            padding: 8px 26px;
            margin: 0 8px;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(238,187,195,0.08);
        }
        .navbar-btn:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        /* Login section styles */
        .login-section {
            max-width: 350px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        /* Login form input styles */
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 90%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #b8c1ec;
            border-radius: 8px;
            background: #16161a;
            color: #eaeaea;
            font-size: 1rem;
            outline: none;
            transition: border 0.2s;
        }
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border: 1.5px solid #eebbc3;
        }
        .login-form input[type="submit"] {
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
        .login-form input[type="submit"]:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        /* Signup link styles */
        .signup-link {
            margin-top: 18px;
            color: #b8c1ec;
            font-size: 1rem;
        }
        .signup-link a {
            color: #eebbc3;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        /* Responsive styles */
        @media (max-width: 500px) {
            .login-section {
                padding: 18px 4px 16px 4px;
            }
            .navbar-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="navbar-title">SocialBook</div>
        <div class="navbar-buttons">
            <a href="signup.php" class="navbar-btn">Sign Up</a>
        </div>
    </div>

    <!-- Login Section -->
    <div class="login-section">
        <div class="login-title">Login to SocialBook</div>
        <form method="post" action="" class="login-form">
            <input name="email" value="<?php echo htmlspecialchars($email); ?>" type="text" placeholder="Email"><br>
            <input name="password" type="password" placeholder="Password"><br>
            <input type="submit" value="Login">
        </form>
        <div class="signup-link">
            New here? <a href="signup.php">Create an account</a>
        </div>
    </div>
</body>
</html>