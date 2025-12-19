<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['userrole'] !== 'developer') {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

// Get all projects (except the developer's own if needed for explore)
$stmt = $pdo->prepare("
    SELECT p.*, u.username AS dev_name, t.team_name 
    FROM projects p 
    LEFT JOIN users u ON p.developer_id = u.id
    LEFT JOIN teams t ON p.team_id = t.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$projects = $stmt->fetchAll();
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
        <input type="text" placeholder="Search games...">
        <i class="fas fa-search"></i>
    </div>
    <div class="dashboard-user-area">
        <?php include "partials/notifications.php"; ?>
        <a href="index.php?page=profile" style="text-decoration: none; color: inherit;">
            <div class="user-profile" style="cursor:pointer;">
                <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <span><?= htmlspecialchars($user['username']) ?></span>
            </div>
        </a>
    </div>
</div>

<!-- Main Layout -->
<div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <a href="index.php?page=dashboard" class="sidebar-item active">
            <i class="fas fa-globe"></i>
            <span>Explore</span>
        </a>
        <a href="index.php?page=library" class="sidebar-item">
            <i class="fas fa-book"></i>
            <span>Library</span>
        </a>
        <a href="index.php?page=collections" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <a href="index.php?page=teams" class="sidebar-item">
            <i class="fas fa-users"></i>
            <span>Teams</span>
        </a>
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a href="index.php?page=upload" class="sidebar-item">
            <i class="fas fa-folder-plus"></i>
            <span>Created Projects</span>
        </a>
        <div class="sidebar-footer">
            <a href="index.php?page=settings" class="sidebar-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div class="welcome-header">
            <h1>Explore More Games</h1>
            <p>This your gateway to new worlds and fresh challenges. Discover games crafted by talented developers, test your skills, and find your next favorite adventure. Start exploring and play today!</p>
        </div>

        <?php if (count($projects) > 0): ?>
        <div class="games-section">
            <div class="section-header">Games Created by Other Developers</div>
            <div class="games-grid">
                <?php foreach ($projects as $p): ?>
                <div class="game-card" onclick="window.location.href='index.php?page=game&id=<?= $p['id'] ?>'">
                    <div class="game-thumbnail">
                        <?php if (!empty($p['banner_path']) && file_exists($p['banner_path'])): ?>
                            <img src="<?= htmlspecialchars($p['banner_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;">
                                <i class="fas fa-gamepad" style="font-size:3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($p['demo_flag']): ?>
                        <div class="discount-badge">DEMO<span>Available</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($p['title']) ?></h3>
                        <?php if (!empty($p['team_name']) && $p['credit_type'] !== 'developer'): ?>
                        <div style="font-size:0.8rem; color:#9B59FF; margin-bottom:5px;">
                            <i class="fas fa-users"></i> <?= htmlspecialchars($p['team_name']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="game-price">$<?= number_format($p['price'], 2) ?></div>
                        <div style="font-size:0.75rem; color:#888; margin-bottom:5px;">
                            <i class="fas fa-desktop"></i> <?= htmlspecialchars($p['platform'] ?? 'Windows') ?>
                        </div>
                        <div class="game-rating">
                            <span class="stars" style="color:#FFD700;">
                                <?php
                                $rating = $p['rating'] ?? 0;
                                $fullStars = floor($rating);
                                $halfStar = ($rating - $fullStars) >= 0.5;
                                for ($i = 0; $i < $fullStars; $i++) echo '★';
                                if ($halfStar) echo '★';
                                for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                                ?>
                            </span>
                            <span class="review-count"><?= number_format($p['rating'] ?? 0, 1) ?> (<?= number_format($p['total_ratings'] ?? 0) ?>)</span>
                        </div>
                        <div style="font-size:0.75rem; color:#666; margin-top:5px;">
                            <i class="fas fa-download"></i> <?= number_format($p['downloads'] ?? 0) ?> downloads
                        </div>
                        <p class="game-description"><?= htmlspecialchars(substr($p['projectdescription'], 0, 100)) ?><?= strlen($p['projectdescription']) > 100 ? '...' : '' ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-gamepad"></i>
            <h3>No Games Yet</h3>
            <p>Start your journey by uploading your first game!</p>
            <button class="btn-primary" onclick="window.location.href='index.php?page=upload'">
                <i class="fas fa-plus"></i> Upload Your First Game
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>