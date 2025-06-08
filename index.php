<html>
<head>
    <title>SocialBook | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Base body styles */
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
            font-size: 2.5rem;
            font-weight: 800;
            color: #eebbc3;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .navbar-buttons {
            margin-top: 10px;
        }
        .navbar-btn {
            display: inline-flex;           /* Center content vertically and horizontally */
            align-items: center;
            justify-content: center;
            background: #eebbc3;
            color: #232946;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 10px 32px;
            margin: 0 10px;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(238,187,195,0.08);
            text-align: center;
        }
        .navbar-btn:hover {
            background: #f6c7d1;
            color: #16161a;
            box-shadow: 0 4px 16px rgba(238,187,195,0.18);
        }

        /* Hero section styles */
        .hero-section {
            max-width: 420px;
            margin: 60px auto 0 auto;
            background: #232946;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(20,20,30,0.22);
            padding: 38px 32px 32px 32px;
            text-align: center;
        }
        .hero-title {
            font-size: 2rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 18px;
        }
        .hero-desc {
            font-size: 1.1rem;
            color: #b8c1ec;
            margin-bottom: 30px;
        }
        .hero-cta {
            display: flex;
            justify-content: center;
            gap: 18px;
        }

        /* Responsive styles for mobile */
        @media (max-width: 600px) {
            .hero-section {
                padding: 22px 8px 20px 8px;
            }
            .navbar-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="navbar-title">SocialBook</div>
        <div class="navbar-buttons">
            <a href="login.php" class="navbar-btn">Login</a>
            <a href="signup.php" class="navbar-btn">Sign Up</a>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-title">Welcome to SocialBook</div>
        <div class="hero-desc">
            Discover a modern, friendly space to share your thoughts, connect with friends, and be part of a vibrant community.<br><br>
            Join now and start your journey!
        </div>
        <div class="hero-cta">
            <!-- Call-to-action buttons -->
            <a href="signup.php" class="navbar-btn">Get Started</a>
            <a href="login.php" class="navbar-btn" style="background:#232946; color:#eebbc3; border:1px solid #eebbc3;">Login</a>
            <a href="adminLogin.php" class="navbar-btn" style="background:#ff7675; color:#fff; border:1px solid #ff7675;">Admin Login</a>
        </div>
    </div>
</body>
</html>