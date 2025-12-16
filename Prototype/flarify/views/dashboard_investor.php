<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'investor') {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

$stmt = $pdo->query("SELECT p.*, u.name AS dev_name FROM projects p JOIN users u ON p.developer_id=u.id ORDER BY p.id DESC");
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
        <a href="#">GAMES</a>
        <a href="index.php?page=logout">LOG OUT</a>
    </div>
    <div class="dashboard-search">
        <input type="text" placeholder="Search projects...">
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
        <a href="index.php?page=dashboard" class="sidebar-item active">
            <i class="fas fa-globe"></i>
            <span>Explore</span>
        </a>
        <a href="index.php?page=portfolio" class="sidebar-item">
            <i class="fas fa-briefcase"></i>
            <span>Portfolio</span>
        </a>
        <a href="index.php?page=investments" class="sidebar-item">
            <i class="fas fa-chart-line"></i>
            <span>Investments</span>
        </a>
        <a href="index.php?page=messages" class="sidebar-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a href="index.php?page=watchlist" class="sidebar-item">
            <i class="fas fa-star"></i>
            <span>Watchlist</span>
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
                        <button class="watchlist-btn" onclick="event.stopPropagation(); toggleWatchlist(<?= $p['id'] ?>, this);" data-project-id="<?= $p['id'] ?>">
                            <i class="fas fa-star"></i>
                        </button>
                        <?php if (!empty($p['banner_path']) && file_exists($p['banner_path'])): ?>
                            <img src="<?= htmlspecialchars($p['banner_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;">
                                <i class="fas fa-gamepad" style="font-size:3rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <h3 class="game-title"><?= htmlspecialchars($p['title']) ?></h3>
                        <div class="game-meta">by <?= htmlspecialchars($p['dev_name']) ?></div>
                        <div class="game-price">$<?= number_format($p['price'], 2) ?></div>
                        <div style="font-size:0.75rem; color:#888; margin-top:5px;">
                            <i class="fas fa-desktop"></i> <?= htmlspecialchars($p['platform'] ?? 'Windows') ?>
                        </div>
                        <div style="font-size:0.85rem; color:#FFD700; margin:8px 0;">
                            <?php
                            $rating = $p['rating'] ?? 0;
                            $fullStars = floor($rating);
                            $halfStar = ($rating - $fullStars) >= 0.5;
                            for ($i = 0; $i < $fullStars; $i++) echo '★';
                            if ($halfStar) echo '★';
                            for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                            ?>
                            <span style="color:#666; font-size:0.8rem;"><?= number_format($p['rating'] ?? 0, 1) ?> (<?= number_format($p['total_ratings'] ?? 0) ?>)</span>
                        </div>
                        <div style="font-size:0.75rem; color:#666;">
                            <i class="fas fa-download"></i> <?= number_format($p['downloads'] ?? 0) ?> downloads
                        </div>
                        <p class="game-description"><?= htmlspecialchars(substr($p['description'], 0, 100)) ?><?= strlen($p['description']) > 100 ? '...' : '' ?></p>
                        <div class="game-tags">
                            <span class="game-tag"><i class="fas fa-envelope"></i> Contact Developer</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <h3>No Projects Available</h3>
            <p>Check back soon for new investment opportunities!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Load watchlist status for all games on page load
document.addEventListener('DOMContentLoaded', function() {
    const watchlistBtns = document.querySelectorAll('.watchlist-btn');
    
    watchlistBtns.forEach(btn => {
        const projectId = btn.dataset.projectId;
        
        fetch('backend/watchlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=check&project_id=${projectId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.in_watchlist) {
                btn.classList.add('in-watchlist');
            }
        });
    });
});

function toggleWatchlist(projectId, button) {
    const isInWatchlist = button.classList.contains('in-watchlist');
    const action = isInWatchlist ? 'remove' : 'add';
    
    fetch('backend/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&project_id=${projectId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('in-watchlist');
        } else {
            alert(data.message || 'Failed to update watchlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>