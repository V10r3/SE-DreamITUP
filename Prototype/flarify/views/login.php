<?php
require "config.php";

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username)) {
        $error = "Please enter your username or email.";
    } elseif (empty($password)) {
        $error = "Please enter your password.";
    } else {
        // Check user by name or email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name=? OR email=?");
        $stmt->execute([$username,$username]);
        $user = $stmt->fetch();

        if (!$user) {
            error_log("Login failed: user not found - $username");
            $error = "No account found with that username or email.";
        } elseif (!password_verify($password, $user['password'])) {
            error_log("Login failed: wrong password for user - $username");
            $error = "Incorrect password. Please try again.";
        } else {
            $_SESSION['user'] = $user;
            header("Location: index.php?page=dashboard");
            exit;
        }
    }
}
?>
<link rel="stylesheet" href="assets/login-page-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.error-message {
    background: #fee;
    border: 1px solid #fcc;
    color: #c33;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: shakeError 0.3s ease;
}

@keyframes shakeError {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.error-message i {
    font-size: 1.1rem;
}
</style>

<div class="login-page-wrapper">
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="brand-logo">Flarify</div>
        <div class="nav-menu">
            <a href="index.php?page=login" class="nav-link active">HOME</a>
            <a href="index.php?page=about" class="nav-link">ABOUT US</a>
            <a href="index.php?page=contact" class="nav-link">CONTACT</a>
            <a href="index.php?page=login" class="nav-link">LOGIN</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
        <!-- Left Side - Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome to <span class="brand-highlight">Flarify</span></h1>
            <p class="welcome-subtitle">Build, Innovate, and manage your games with ease. #GameItUp</p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form-container">
            <h2 class="form-title">Log in</h2>
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input name="username" type="text" placeholder="Username" class="form-input" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input name="password" type="password" placeholder="Password" class="form-input" required />
                    <i class="fas fa-eye password-toggle"></i>
                </div>
                <div class="form-options">
                    <label class="remember-label">
                        <input type="checkbox" class="remember-checkbox" />
                        <span>Remember Me</span>
                    </label>
                    <a href="backend/request_reset.php" class="forgot-link">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-login">Log in</button>
                <div class="divider">Or</div>
                <button type="button" onclick="window.location.href='index.php?page=signup'" class="btn-signup">Sign up</button>
            </form>
        </div>
    </div>
</div>