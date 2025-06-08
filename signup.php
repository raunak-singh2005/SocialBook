<?php
    // Start the session
    session_start();

    // Include required files for database connection, signup logic, and ban checking
    include("classes/connect.php");
    include("classes/signup.php");
    include_once("banCheck.php");

    // Check if the user is banned before proceeding
    requireNotBanned($_SESSION['SocialBook_userID'] ?? null);

    // Initialize variables for form fields and result message
    $firstName = "";
    $lastName = "";
    $gender = "";
    $email = "";
    $result = "";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $signup = new Signup();
        $result = $signup->evaluate($_POST);

        // Preserve form values in case of error
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $gender = $_POST['gender'];
        $email = $_POST['email'];

        if($result == "") {
            // Redirect to login page if signup is successful
            header("Location: login.php");
            die;
        } else {
            // Display error message if signup fails
            echo "<div style='color: #ff6f91; text-align: center; margin-top:16px;'>" . $result . "</div>";
        }
    }
?>

<html>
<head>
    <title>SocialBook | Signup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* General body styling */
        body {
            background: linear-gradient(135deg, #232946 0%, #16161a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #eaeaea;
            margin: 0;
            padding: 0;
        }
        /* Navbar styling */
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
        /* Signup section styling */
        .signup-section {
            max-width: 370px;
            margin: 48px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 32px 24px 28px 24px;
            text-align: center;
        }
        .signup-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        /* Signup form styling */
        .signup-form input[type="text"],
        .signup-form input[type="password"],
        .signup-form input[type="email"],
        .signup-form select {
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
        .signup-form input[type="text"]:focus,
        .signup-form input[type="password"]:focus,
        .signup-form input[type="email"]:focus,
        .signup-form select:focus {
            border: 1.5px solid #eebbc3;
        }
        .signup-form input[type="submit"] {
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
        .signup-form input[type="submit"]:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }
        /* Login link styling */
        .login-link {
            margin-top: 18px;
            color: #b8c1ec;
            font-size: 1rem;
        }
        .login-link a {
            color: #eebbc3;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        /* Responsive styling */
        @media (max-width: 500px) {
            .signup-section {
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
        <div class="navbar-buttons">
            <a href="login.php" class="navbar-btn">Login</a>
        </div>
    </div>

    <!-- Signup Form Section -->
    <div class="signup-section">
        <div class="signup-title">Create your SocialBook account</div>
        <form method="post" action="" class="signup-form">
            <input value="<?php echo htmlspecialchars($firstName); ?>" name="firstName" type="text" placeholder="First Name"><br>
            <input value="<?php echo htmlspecialchars($lastName); ?>" name="lastName" type="text" placeholder="Last Name"><br>
            <select name="gender">
                <option disabled <?php if($gender == "") echo "selected"; ?> value> -- select a gender -- </option>
                <option <?php if($gender == "Female") echo "selected"; ?>>Female</option>
                <option <?php if($gender == "Male") echo "selected"; ?>>Male</option>
                <option <?php if($gender == "Other") echo "selected"; ?>>Other</option>
            </select><br>
            <input name="password" type="password" placeholder="Password"><br>
            <input name="confirmPassword" type="password" placeholder="Confirm Password"><br>
            <input value="<?php echo htmlspecialchars($email); ?>" name="email" type="email" placeholder="Email"><br>
            <input type="submit" value="Signup">
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>