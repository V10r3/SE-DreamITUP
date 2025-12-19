<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['userrole'] !== 'investor') {
    header("Location: index.php?page=dashboard");
    exit;
}

$user = $_SESSION['user'];

// Get watchlist items
$stmt = $pdo->prepare("
    SELECT p.*, u.username AS developer_name, 
           (SELECT AVG(rating) FROM project_ratings WHERE project_id = p.id) AS avg_rating,
           (SELECT COUNT(*) FROM project_ratings WHERE project_id = p.id) AS rating_count
    FROM watchlist w
    JOIN projects p ON w.project_id = p.id
    JOIN users u ON p.developer_id = u.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user['id']]);
$watchlist_items = $stmt->fetchAll();
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.watchlist-header {
    margin-bottom: 30px;
}

.watchlist-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
}

.watchlist-header p {
    color: #666;
    font-size: 1.1rem;
}

.watchlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.watchlist-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
    position: relative;
}

.watchlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(155, 89, 255, 0.2);
}

.watchlist-thumbnail {
    width: 100%;
    height: 180px;
    object-fit: cover;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
}

.watchlist-content {
    padding: 20px;
}

.watchlist-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}

.watchlist-developer {
    color: #9B59FF;
    font-size: 0.95rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.watchlist-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
}

.watchlist-stat {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    color: #666;
}

.watchlist-rating {
    color: #FFB800;
}

.watchlist-actions {
    display: flex;
    gap: 10px;
}

.btn-watchlist-action {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.btn-view {
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(155, 89, 255, 0.3);
}

.btn-remove {
    background: #f5f5f5;
    color: #666;
}

.btn-remove:hover {
    background: #fee;
    color: #d32f2f;
}

.empty-watchlist {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.empty-watchlist i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-watchlist h2 {
    color: #666;
    margin-bottom: 10px;
}

.empty-watchlist p {
    color: #999;
    margin-bottom: 25px;
}

.btn-browse {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: transform 0.2s;
}

.btn-browse:hover {
    transform: translateY(-2px);
}

.platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: #f0f0f0;
    border-radius: 15px;
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 10px;
}
</style>

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
        <a href="index.php?page=dashboard" class="sidebar-item">
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
        <a href="index.php?page=watchlist" class="sidebar-item active">
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
        <div class="watchlist-header">
            <h1><i class="fas fa-star" style="color: #FFB800;"></i> My Watchlist</h1>
            <p>Games you're interested in investing or following</p>
        </div>

        <?php if (count($watchlist_items) === 0): ?>
        <div class="empty-watchlist">
            <i class="fas fa-star"></i>
            <h2>Your watchlist is empty</h2>
            <p>Start adding games you're interested in to track them here</p>
            <a href="index.php?page=dashboard" class="btn-browse">Browse Games</a>
        </div>
        <?php else: ?>
        <div class="watchlist-grid">
            <?php foreach ($watchlist_items as $item): ?>
            <div class="watchlist-card">
                <?php 
                $thumbnail = $item['icon_path'] ?: $item['banner_path'];
                if (!$thumbnail) {
                    $screenshots = json_decode($item['screenshots'], true);
                    $thumbnail = !empty($screenshots) ? $screenshots[0] : '';
                }
                ?>
                <?php if ($thumbnail && file_exists($thumbnail)): ?>
                <img src="<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="watchlist-thumbnail">
                <?php else: ?>
                <div class="watchlist-thumbnail" style="background: linear-gradient(135deg, #9B59FF, #C48FFF); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-gamepad" style="font-size: 3rem; color: white;"></i>
                </div>
                <?php endif; ?>
                
                <div class="watchlist-content">
                    <div class="platform-badge">
                        <i class="fas fa-desktop"></i>
                        <span><?= htmlspecialchars($item['platform']) ?></span>
                    </div>
                    
                    <h3 class="watchlist-title"><?= htmlspecialchars($item['title']) ?></h3>
                    
                    <div class="watchlist-developer">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($item['developer_name']) ?></span>
                    </div>
                    
                    <div class="watchlist-stats">
                        <div class="watchlist-stat watchlist-rating">
                            <i class="fas fa-star"></i>
                            <span><?= number_format($item['rating'] ?? 0, 1) ?> (<?= number_format($item['total_ratings'] ?? 0) ?>)</span>
                        </div>
                        <div class="watchlist-stat">
                            <i class="fas fa-download"></i>
                            <span><?= number_format($item['downloads'] ?? 0) ?></span>
                        </div>
                    </div>
                    
                    <div class="watchlist-actions">
                        <a href="index.php?page=game&id=<?= $item['id'] ?>" class="btn-watchlist-action btn-view">
                            <i class="fas fa-eye"></i> View Game
                        </a>
                        <button class="btn-watchlist-action btn-remove" onclick="removeFromWatchlist(<?= $item['id'] ?>)">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function removeFromWatchlist(projectId) {
    if (!confirm('Remove this game from your watchlist?')) {
        return;
    }
    
    fetch('backend/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&project_id=${projectId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove from watchlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>
