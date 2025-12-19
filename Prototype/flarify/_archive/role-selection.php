<?php include 'includes/config.php'; if (!isset($_SESSION['user_id'])) header("Location: index.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Role Selection</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="role-container">
    <header>
        <div class="logo">Flarify</div>
        <nav>Home | About Us | Contact | Sign Up</nav>
    </header>
    <h2>A Developers Journey</h2>
    <p>Choose how you'll participate</p>
    <div class="options">
        <div class="option"><img src="developer-icon.png" alt="Developer"> <h3>Developer</h3> <p>Build the game and improve its features.</p></div>
        <div class="option"><img src="investor-icon.png" alt="Investor"> <h3>Investor</h3> <p>Provide funding and support to grow the platform.</p></div>
        <div class="option"><img src="tester-icon.png" alt="Tester"> <h3>Tester</h3> <p>Test games and features to make sure everything works properly.</p></div>
    </div>
    <div class="right-side">
        <h3>How would you like to use your account?</h3>
        <form action="role-process.php" method="POST">
            <select name="role" required>
                <option value="">Select...</option>
                <option value="Developer">Developer</option>
                <option value="Investor">Investor</option>
                <option value="Tester">Tester</option>
            </select>
            <button type="submit">Continue</button>
        </form>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>