<?php
require "../config.php";
session_start();

$error = "";
$success = "";
$tokenProvided = false;
$reset = null;

try {
    if (isset($_GET['token'])) {
        $tokenProvided = true;
        $token = $_GET['token'];
        
        // First check if token exists at all
        $stmt = $pdo->prepare("SELECT *, expires_at, NOW() as server_time FROM password_resets WHERE token=?");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            error_log("Password reset failed: Token not found in database - " . $token);
            $error = "Invalid reset link. The token was not found.";
        } elseif (strtotime($reset['expires_at']) < strtotime($reset['server_time'])) {
            error_log("Password reset failed: Token expired - " . $token . " (expired at " . $reset['expires_at'] . ", server time: " . $reset['server_time'] . ")");
            // Delete expired token
            $pdo->prepare("DELETE FROM password_resets WHERE token=?")->execute([$token]);
            $error = "This reset link has expired. Please request a new password reset.";
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Token is valid and form submitted
            $newHash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
            $pdo->prepare("UPDATE users SET userpassword=? WHERE id=?")->execute([$newHash, $reset['user_id']]);
            
            // Delete used token
            $pdo->prepare("DELETE FROM password_resets WHERE token=?")->execute([$token]);
            
            $success = "Password updated successfully! You can now <a href='../index.php?page=login'>log in</a>.";
            error_log("Password reset successful for user_id: " . $reset['user_id']);
        }
    } else {
        $error = "No reset token provided.";
    }
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    // Show actual error for debugging
    $error = "Database error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flarify - Reset Password</title>
  <link rel="stylesheet" href="../assets/login-page-styles.css">
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
    
    .success-message {
        background: #e8f5e9;
        border: 1px solid #81c784;
        color: #2e7d32;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes shakeError {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .success-message i, .error-message i {
        font-size: 1.1rem;
    }
    
    .success-message a {
        color: #1b5e20;
        font-weight: 600;
        text-decoration: underline;
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
            <a href="../index.php?page=login" class="nav-link active">LOGIN</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
        <!-- Left Side - Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Reset Your <span class="brand-highlight">Password</span></h1>
            <p class="welcome-subtitle">Enter your new password to regain access to your account.</p>
        </div>

        <!-- Right Side - Reset Form -->
        <div class="login-form-container">
            <h2 class="form-title">Reset Password</h2>
            
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?= $success ?></span>
            </div>
            <?php endif; ?>

            <?php if (!$success && $tokenProvided && isset($reset) && $reset && empty($error)): ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input name="password" type="password" placeholder="New Password" class="form-input" required minlength="8" />
                    <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                </div>
                <p style="font-size: 0.85rem; color: #666; margin: 10px 0;">Password must be at least 8 characters long.</p>
                <button type="submit" class="btn-login">Reset Password</button>
                <div class="form-options" style="justify-content: center; margin-top: 15px;">
                    <a href="../index.php?page=login" class="forgot-link">Back to Login</a>
                </div>
            </form>
            <?php elseif (!$tokenProvided || (!isset($reset) && $tokenProvided)): ?>
            <div class="form-options" style="justify-content: center; margin-top: 15px;">
                <a href="request_reset.php" class="btn-login" style="display: inline-block; text-decoration: none; text-align: center;">Request New Reset Link</a>
            </div>
            <div class="form-options" style="justify-content: center; margin-top: 15px;">
                <a href="../index.php?page=login" class="forgot-link">Back to Login</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword(icon) {
    const input = icon.previousElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>