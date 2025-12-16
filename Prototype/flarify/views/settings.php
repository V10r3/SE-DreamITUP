<?php
/**
 * User Settings View
 * 
 * Displays user settings interface including:
 * - Account Information (name, email, role)
 * - Preferences (theme, email notifications)
 * - Privacy & Security (profile visibility, 2FA)
 * - Danger Zone (account deletion)
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "config.php";

// Authentication check - redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}

$user = $_SESSION['user'];
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Top Navigation -->
<div class="dashboard-topbar">
    <div class="dashboard-brand">Flarify</div>
    <div class="dashboard-nav-links">
        <a href="index.php?page=dashboard">HOME</a>
        <a href="index.php?page=about">ABOUT US</a>
        <a href="index.php?page=messages">INBOX</a>
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
            <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <span><?= htmlspecialchars($user['name']) ?></span>
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
        <a href="index.php?page=library" class="sidebar-item">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="#" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <?php if ($user['role'] === 'developer'): ?>
        <a href="index.php?page=upload" class="sidebar-item">
            <i class="fas fa-folder-plus"></i>
            <span>Created Projects</span>
        </a>
        <?php endif; ?>
        <div class="sidebar-footer">
            <a href="index.php?page=settings" class="sidebar-item active">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div style="max-width:900px; margin:0 auto;">
            <h1 style="margin:0 0 30px 0; color:#333;">
                <i class="fas fa-cog"></i> Settings
            </h1>

            <!-- Account Settings -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:20px;">
                <h3 style="color:#333; margin:0 0 20px 0; padding-bottom:10px; border-bottom:2px solid #9B59FF;">
                    <i class="fas fa-user"></i> Account Settings
                </h3>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; border-bottom:1px solid #eee;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Profile Information</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Update your name, email, and password</p>
                    </div>
                    <a href="index.php?page=profile" style="padding:10px 20px; background:#9B59FF; color:white; text-decoration:none; border-radius:8px; font-weight:600;">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Account Type</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">
                            You are registered as: <strong><?= ucfirst($user['role']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:20px;">
                <h3 style="color:#333; margin:0 0 20px 0; padding-bottom:10px; border-bottom:2px solid #9B59FF;">
                    <i class="fas fa-sliders-h"></i> Preferences
                </h3>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; border-bottom:1px solid #eee; opacity:0.5;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Email Notifications</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Not Available</p>
                    </div>
                    <label style="position:relative; display:inline-block; width:60px; height:34px;">
                        <input type="checkbox" disabled style="opacity:0; width:0; height:0;">
                        <span style="position:absolute; cursor:not-allowed; top:0; left:0; right:0; bottom:0; background:#ccc; transition:0.4s; border-radius:34px;"></span>
                    </label>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; opacity:0.5;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Display Mode</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Not Available</p>
                    </div>
                    <select disabled style="padding:10px 20px; border:1px solid #ddd; border-radius:8px; font-size:1rem; background:#f0f0f0; cursor:not-allowed;">
                        <option>Light Mode</option>
                    </select>
                </div>
            </div>

            <!-- Privacy & Security -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:20px;">
                <h3 style="color:#333; margin:0 0 20px 0; padding-bottom:10px; border-bottom:2px solid #9B59FF;">
                    <i class="fas fa-shield-alt"></i> Privacy & Security
                </h3>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; border-bottom:1px solid #eee; opacity:0.5;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Profile Visibility</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Not Available</p>
                    </div>
                    <select disabled style="padding:10px 20px; border:1px solid #ddd; border-radius:8px; font-size:1rem; background:#f0f0f0; cursor:not-allowed;">
                        <option>Public</option>
                    </select>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; opacity:0.5;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Two-Factor Authentication</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Coming soon - This feature is not yet available</p>
                    </div>
                    <button disabled style="padding:10px 20px; background:#E0E0E0; color:#999; border:none; border-radius:8px; font-weight:600; cursor:not-allowed;">
                        <i class="fas fa-lock"></i> Not Available
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border:2px solid #E74C3C;">
                <h3 style="color:#E74C3C; margin:0 0 20px 0; padding-bottom:10px; border-bottom:2px solid #E74C3C;">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </h3>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0;">
                    <div>
                        <h4 style="margin:0 0 5px 0; color:#333;">Delete Account</h4>
                        <p style="margin:0; color:#666; font-size:0.9rem;">Permanently delete your account and all associated data</p>
                    </div>
                    <button onclick="if(confirm('Are you sure you want to delete your account? This action cannot be undone!')) alert('Account deletion is not yet implemented.')" 
                            style="padding:10px 20px; background:#E74C3C; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Toast notification styles */
@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOut {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(400px);
    opacity: 0;
  }
}
</style>

<script>
// Theme selector functionality
document.getElementById('themeSelector').addEventListener('change', function() {
    const theme = this.value;
    const formData = new FormData();
    formData.append('action', 'update_theme');
    formData.append('theme', theme);
    
    fetch('backend/settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Theme updated successfully!', 'success');
            // Apply theme immediately
            applyTheme(theme);
        } else {
            showToast(data.message || 'Failed to update theme', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Server error: Could not update theme', 'error');
    });
});

function applyTheme(theme) {
    // Get system preference for auto mode
    if (theme === 'auto') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        theme = isDark ? 'dark' : 'light';
    }
    
    if (theme === 'dark') {
        document.body.style.backgroundColor = '#1a1a1a';
        document.body.style.color = '#ffffff';
    } else {
        document.body.style.backgroundColor = '#f5f5f5';
        document.body.style.color = '#333333';
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' 
        ? 'linear-gradient(135deg, #4CAF50, #81C784)' 
        : 'linear-gradient(135deg, #f44336, #ef5350)';
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `
        <i class="fas ${icon}" style="margin-right: 10px; font-size: 1.2rem;"></i>
        <span>${message}</span>
    `;
    
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: bgColor,
        color: 'white',
        padding: '16px 24px',
        borderRadius: '10px',
        boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
        zIndex: '10000',
        display: 'flex',
        alignItems: 'center',
        fontWeight: '500',
        animation: 'slideIn 0.3s ease-out'
    });
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Apply saved theme on page load
window.addEventListener('DOMContentLoaded', function() {
    const savedTheme = document.getElementById('themeSelector').value;
    applyTheme(savedTheme);
});
</script>
