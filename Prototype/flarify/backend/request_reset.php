<?php
require "../config.php";
session_start();

$message = "";
$messageType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Delete any existing tokens for this user first
        $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")->execute([$user['id']]);
        
        $token = bin2hex(random_bytes(32));
        
        // Use MySQL NOW() + INTERVAL for consistent timezone handling
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id,token,expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $stmt->execute([$user['id'],$token]);

        error_log("Password reset requested for user: $email with token: $token");
        $message = "Reset link: <a href='reset.php?token=$token' style='color: #9B59FF; font-weight: bold;'>Click here to reset password</a>";
    } else {
        error_log("Password reset failed: Email not found - $email");
        $message = "Email not found.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flarify - Forgot Password</title>
  <link rel="stylesheet" href="../assets/login-page-styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .message-box {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .message-box.success {
        background: #e8f5e9;
        border: 1px solid #81c784;
        color: #2e7d32;
    }
    .message-box.error {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
    }
    .message-box p {
        margin: 0;
    }
  </style>
</head>
<body>

<div class="login-page-wrapper">
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="brand-logo">Flarify</div>
        <div class="nav-menu">
            <a href="../index.php?page=login" class="nav-link">HOME</a>
            <a href="../index.php?page=about" class="nav-link">ABOUT US</a>
            <a href="../index.php?page=contact" class="nav-link">CONTACT</a>
            <a href="../index.php?page=login" class="nav-link">LOGIN</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
        <!-- Left Side - Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Reset Your <span class="brand-highlight">Password</span></h1>
            <p class="welcome-subtitle">Enter your email to receive a password reset link. #GameItUp</p>
        </div>

        <!-- Right Side - Reset Form -->
        <div class="login-form-container">
            <h2 class="form-title">Forgot Password</h2>
            <form method="POST" class="login-form">
                <?php if ($message): ?>
                <div class="message-box <?= $messageType ?>">
                    <?php if ($messageType === 'success'): ?>
                        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle" style="font-size: 1.2rem;"></i>
                    <?php endif; ?>
                    <p><?= $message ?></p>
                </div>
                <?php endif; ?>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input name="email" type="email" placeholder="Enter your email" class="form-input" required />
                </div>
                <button type="submit" class="btn-login">Request Reset</button>
                <div class="divider">Remember your password?</div>
                <button type="button" onclick="window.location.href='../index.php?page=login'" class="btn-signup">Back to Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>