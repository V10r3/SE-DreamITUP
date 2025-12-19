<?php
require "config.php";
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

$error = "";
$success = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($name)) {
        $error = "Name is required.";
    } elseif (empty($email)) {
        $error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error = "Email is already taken.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->execute([$name, $email, $user['id']]);
            $_SESSION['user']['username'] = $name;
            $_SESSION['user']['email'] = $email;
            $user = $_SESSION['user'];
            $success = "Profile updated successfully!";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT userpassword FROM users WHERE id=?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();

        if (password_verify($current_password, $userData['userpassword'])) {
            $newHash = password_hash($new_password, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("UPDATE users SET userpassword=? WHERE id=?");
            $stmt->execute([$newHash, $user['id']]);
            $success = "Password changed successfully!";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php if ($success): ?>
<div style="position:fixed; top:20px; right:20px; background:#d4edda; color:#155724; padding:20px 30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:10000; border:1px solid #c3e6cb; animation:slideIn 0.3s ease;">
    <strong><i class="fas fa-check-circle"></i> Success!</strong> <?= htmlspecialchars($success) ?>
</div>
<style>
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
<script>
setTimeout(function() {
    const successMsg = document.querySelector('[style*="fixed"]');
    if (successMsg) successMsg.remove();
}, 3000);
</script>
<?php endif; ?>

<?php if ($error): ?>
<div style="position:fixed; top:20px; right:20px; background:#f8d7da; color:#721c24; padding:20px 30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:10000; border:1px solid #f5c6cb; animation:slideIn 0.3s ease;">
    <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> <?= htmlspecialchars($error) ?>
</div>
<script>
setTimeout(function() {
    const errorMsg = document.querySelectorAll('[style*="fixed"]')[1] || document.querySelector('[style*="fixed"]');
    if (errorMsg) errorMsg.remove();
}, 5000);
</script>
<?php endif; ?>

<!-- Top Navigation -->
<div class="dashboard-topbar">
    <div class="dashboard-brand">Flarify</div>
    <div class="dashboard-nav-links">
        <a href="index.php?page=dashboard">HOME</a>
        <a href="index.php?page=about">ABOUT US</a>
        <a href="index.php?page=dashboard">GAMES</a>
        <a href="index.php?page=logout">LOG OUT</a>
    </div>
    <div class="dashboard-search">
        <input type="text" placeholder="Search...">
        <i class="fas fa-search"></i>
    </div>
    <div class="dashboard-user-area">
        <?php include "partials/notifications.php"; ?>
        <div class="user-profile" style="cursor:pointer;" onclick="window.location.href='index.php?page=profile'">
            <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
            <span><?= htmlspecialchars($user['username']) ?></span>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <a href="index.php?page=dashboard" class="sidebar-item">
            <i class="fas fa-globe"></i>
            <span>Explore</span>
        </a>
        
        <?php if ($user['userrole'] === 'investor'): ?>
        <a href="index.php?page=portfolio" class="sidebar-item">
            <i class="fas fa-briefcase"></i>
            <span>Portfolio</span>
        </a>
        <a href="index.php?page=investments" class="sidebar-item">
            <i class="fas fa-chart-line"></i>
            <span>Investments</span>
        </a>
        <?php else: ?>
        <a href="index.php?page=library" class="sidebar-item">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="index.php?page=collections" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <?php endif; ?>
        
        <?php if ($user['userrole'] === 'developer'): ?>
        <a href="index.php?page=teams" class="sidebar-item">
            <i class="fas fa-users"></i>
            <span>Teams</span>
        </a>
        <?php endif; ?>
        
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        
        <?php if ($user['userrole'] === 'developer'): ?>
        <a href="index.php?page=upload" class="sidebar-item">
            <i class="fas fa-folder-plus"></i>
            <span>Created Projects</span>
        </a>
        <?php elseif ($user['userrole'] === 'investor'): ?>
        <a href="index.php?page=watchlist" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Watchlist</span>
        </a>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <a href="index.php?page=settings" class="sidebar-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div style="max-width:800px; margin:0 auto;">
            <h1 style="margin:0 0 30px 0; color:#333;">
                <i class="fas fa-user-circle"></i> My Profile
            </h1>

            <!-- Profile Information Card -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:30px;">
                <div style="text-align:center; margin-bottom:30px;">
                    <div style="width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg, #9B59FF, #C48FFF); color:white; display:flex; align-items:center; justify-content:center; font-size:3rem; font-weight:bold; margin:0 auto 15px;">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                    <h2 style="margin:0; color:#333;"><?= htmlspecialchars($user['username']) ?></h2>
                    <p style="color:#666; margin:5px 0;">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                    </p>
                    <span style="display:inline-block; margin-top:10px; padding:5px 15px; background:#F0E6FF; color:#9B59FF; border-radius:20px; font-size:0.9rem; font-weight:600;">
                        <i class="fas fa-<?= $user['userrole'] === 'developer' ? 'code' : ($user['userrole'] === 'tester' ? 'flask' : 'money-bill-wave') ?>"></i>
                        <?= ucfirst($user['userrole']) ?>
                    </span>
                </div>

                <h3 style="color:#333; margin-top:30px; margin-bottom:15px; padding-bottom:10px; border-bottom:2px solid #9B59FF;">
                    <i class="fas fa-edit"></i> Edit Profile
                </h3>

                <form method="POST">
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Name
                        </label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['username']) ?>" required 
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Email
                        </label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required 
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <button type="submit" name="update_profile" 
                            style="padding:12px 30px; background:#9B59FF; color:white; border:none; border-radius:8px; font-size:1rem; font-weight:600; cursor:pointer;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password Card -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="color:#333; margin:0 0 20px 0; padding-bottom:10px; border-bottom:2px solid #9B59FF;">
                    <i class="fas fa-lock"></i> Change Password
                </h3>

                <form method="POST">
                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Current Password
                        </label>
                        <input type="password" name="current_password" required 
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            New Password
                        </label>
                        <input type="password" name="new_password" required minlength="8"
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                        <p style="color:#999; font-size:0.85rem; margin-top:5px;">
                            <i class="fas fa-info-circle"></i> Must be at least 8 characters
                        </p>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Confirm New Password
                        </label>
                        <input type="password" name="confirm_password" required minlength="8"
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <button type="submit" name="change_password" 
                            style="padding:12px 30px; background:#E74C3C; color:white; border:none; border-radius:8px; font-size:1rem; font-weight:600; cursor:pointer;">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
