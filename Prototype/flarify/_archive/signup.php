<?php include 'includes/config.php'; if (isset($_SESSION['user_id'])) header("Location: dashboard.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Signup</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="signup-container">
    <div class="left-side">
        <h1 class="logo">Flarify</h1>
        <nav>Home | About Us | Contact | Sign Up</nav>
        <h2>Welcome to Flarify</h2>
        <p>Build, innovate, and manage your games with ease. #GameItUp</p>
    </div>
    <div class="right-side">
        <h3>Sign Up</h3>
        <form action="signup-process.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Create Account</button>
        </form>
        <p>Or</p>
        <a href="index.php">Log In</a>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>