<?php
/**
 * Library View
 * 
 * Displays user's game library.
 * Content varies by user role:
 * - Developers: See their uploaded games with edit functionality
 * - Testers/Investors: See all available games
 * 
 * Features:
 * - Game cards with ratings, downloads, descriptions
 * - Edit button for developers
 * - Responsive grid layout
 * 
 * @package Flarify
 * @author Flarify Team
 */

require "config.php";

// Authentication check
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit;
}

$user = $_SESSION['user'];

// Fetch games based on user role
if ($user['role'] === 'developer') {
    // Developers see only their own games
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE developer_id=? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
} else {
    // Other users see all available games
    $stmt = $pdo->prepare("SELECT p.*, u.name AS dev_name FROM projects p JOIN users u ON p.developer_id = u.id ORDER BY p.created_at DESC");
    $stmt->execute();
}
$games = $stmt->fetchAll();
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
        <a href="index.php?page=profile" style="text-decoration: none; color: inherit;">
            <div class="user-profile" style="cursor:pointer;">
                <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <span><?= htmlspecialchars($user['name']) ?></span>
            </div>
        </a>
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
        <a href="index.php?page=library" class="sidebar-item active">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="index.php?page=collections" class="sidebar-item">
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
        <?php elseif ($user['role'] === 'tester'): ?>
        <a href="index.php?page=testing_queue" class="sidebar-item">
            <i class="fas fa-flask"></i>
            <span>Testing Queue</span>
        </a>
        <?php endif; ?>
        <div class="sidebar-footer">
            <a href="#" class="sidebar-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div class="welcome-header">
            <h1><i class="fas fa-book"></i> My Library</h1>
            <p><?php if ($user['role'] === 'developer'): ?>Your uploaded games<?php else: ?>Your game collection<?php endif; ?></p>
        </div>

        <?php if (count($games) === 0): ?>
        <div style="text-align:center; padding:60px 20px; color:#999;">
            <i class="fas fa-folder-open" style="font-size:4rem; margin-bottom:20px; opacity:0.3;"></i>
            <h3>No games in your library yet</h3>
            <p><?php if ($user['role'] === 'developer'): ?>Start by uploading your first game!<?php else: ?>Explore and download some games to get started.<?php endif; ?></p>
            <?php if ($user['role'] === 'developer'): ?>
            <a href="index.php?page=upload" style="display:inline-block; margin-top:20px; padding:12px 30px; background:#9B59FF; color:white; text-decoration:none; border-radius:25px; font-weight:600;">
                <i class="fas fa-upload"></i> Upload Game
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="games-section">
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                <div class="game-card" onclick="window.location.href='index.php?page=game&id=<?= $game['id'] ?>'">
                    <div class="game-thumbnail">
                        <?php if (!empty($game['banner_path']) && file_exists($game['banner_path'])): ?>
                            <img src="<?= htmlspecialchars($game['banner_path']) ?>" alt="<?= htmlspecialchars($game['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;">
                                <i class="fas fa-gamepad" style="font-size:3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($game['demo_flag']): ?>
                        <div class="discount-badge">DEMO<span>Available</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($game['title']) ?></h3>
                        <div class="game-price">$<?= number_format($game['price'], 2) ?></div>
                        <div style="font-size:0.75rem; color:#888; margin-bottom:5px;">
                            <i class="fas fa-desktop"></i> <?= htmlspecialchars($game['platform'] ?? 'Windows') ?>
                        </div>
                        <div class="game-rating">
                            <span class="stars" style="color:#FFD700;">
                                <?php
                                $rating = $game['rating'] ?? 0;
                                $fullStars = floor($rating);
                                $halfStar = ($rating - $fullStars) >= 0.5;
                                for ($i = 0; $i < $fullStars; $i++) echo '★';
                                if ($halfStar) echo '★';
                                for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                                ?>
                            </span>
                            <span class="review-count"><?= number_format($game['rating'] ?? 0, 1) ?> (<?= number_format($game['total_ratings'] ?? 0) ?>)</span>
                        </div>
                        <div style="font-size:0.75rem; color:#666; margin-top:5px;">
                            <i class="fas fa-download"></i> <?= number_format($game['downloads'] ?? 0) ?> downloads
                        </div>
                        <p class="game-description"><?= htmlspecialchars(substr($game['description'], 0, 100)) ?><?= strlen($game['description']) > 100 ? '...' : '' ?></p>
                        <?php if ($user['role'] === 'developer'): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                            <a href="index.php?page=edit&id=<?= $game['id'] ?>" onclick="event.stopPropagation();" style="color:#9B59FF; text-decoration:none; font-size:0.9rem; font-weight: 600;">
                                <i class="fas fa-edit"></i> Edit Game
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
