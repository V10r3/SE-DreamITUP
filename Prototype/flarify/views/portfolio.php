<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'investor') {
    header("Location: index.php?page=dashboard");
    exit;
}

$user = $_SESSION['user'];

// Get portfolio (invested projects with stats)
$stmt = $pdo->prepare("
    SELECT p.*, u.name AS developer_name, 
           i.amount AS invested_amount,
           i.equity_percentage,
           i.invested_at,
           i.status AS investment_status,
           (SELECT SUM(amount) FROM investments WHERE project_id = p.id AND status = 'active') AS total_funding
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN users u ON p.developer_id = u.id
    WHERE i.investor_id = ?
    ORDER BY i.invested_at DESC
");
$stmt->execute([$user['id']]);
$portfolio = $stmt->fetchAll();

// Calculate totals
$total_invested = 0;
$active_investments = 0;
foreach ($portfolio as $item) {
    if ($item['investment_status'] === 'active') {
        $total_invested += $item['invested_amount'];
        $active_investments++;
    }
}
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.portfolio-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.stat-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.purple {
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
}

.stat-icon.green {
    background: linear-gradient(135deg, #4CAF50, #81C784);
}

.stat-icon.blue {
    background: linear-gradient(135deg, #2196F3, #64B5F6);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 0.95rem;
}

.portfolio-header {
    margin-bottom: 30px;
}

.portfolio-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
}

.portfolio-table {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow-x: auto;
}

.portfolio-table table {
    width: 100%;
    border-collapse: collapse;
}

.portfolio-table th {
    text-align: left;
    padding: 15px;
    border-bottom: 2px solid #f0f0f0;
    color: #666;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.portfolio-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #f5f5f5;
}

.game-cell {
    display: flex;
    align-items: center;
    gap: 15px;
}

.game-thumb {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
}

.game-details h3 {
    font-size: 1rem;
    color: #333;
    margin-bottom: 3px;
}

.game-details p {
    font-size: 0.85rem;
    color: #666;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.active {
    background: #e8f5e9;
    color: #4CAF50;
}

.status-badge.pending {
    background: #fff3e0;
    color: #FF9800;
}

.status-badge.completed {
    background: #e3f2fd;
    color: #2196F3;
}

.status-badge.cancelled {
    background: #ffebee;
    color: #f44336;
}

.amount-cell {
    font-weight: 600;
    color: #333;
    font-size: 1.1rem;
}

.equity-cell {
    color: #9B59FF;
    font-weight: 600;
}

.btn-view-game {
    padding: 8px 20px;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: transform 0.2s;
}

.btn-view-game:hover {
    transform: translateY(-2px);
}

.empty-portfolio {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.empty-portfolio i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-portfolio h2 {
    color: #666;
    margin-bottom: 10px;
}

.empty-portfolio p {
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
        <a href="index.php?page=portfolio" class="sidebar-item active">
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
        <div class="portfolio-header">
            <h1><i class="fas fa-briefcase" style="color: #9B59FF;"></i> My Portfolio</h1>
            <p>Games and projects you've invested in</p>
        </div>

        <div class="portfolio-stats">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="stat-value">$<?= number_format($total_invested, 2) ?></div>
                        <div class="stat-label">Total Invested</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $active_investments ?></div>
                        <div class="stat-label">Active Investments</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon blue">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= count($portfolio) ?></div>
                        <div class="stat-label">Total Projects</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($portfolio) === 0): ?>
        <div class="empty-portfolio">
            <i class="fas fa-briefcase"></i>
            <h2>No investments yet</h2>
            <p>Start investing in promising game projects to build your portfolio</p>
            <a href="index.php?page=dashboard" class="btn-browse">Explore Games</a>
        </div>
        <?php else: ?>
        <div class="portfolio-table">
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Developer</th>
                        <th>Invested</th>
                        <th>Equity</th>
                        <th>Status</th>
                        <th>Performance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portfolio as $item): ?>
                    <tr>
                        <td>
                            <div class="game-cell">
                                <?php 
                                $thumb = $item['icon_path'] ?: $item['banner_path'];
                                if (!$thumb) {
                                    $screenshots = json_decode($item['screenshots'], true);
                                    $thumb = !empty($screenshots) ? $screenshots[0] : '';
                                }
                                ?>
                                <?php if ($thumb && file_exists($thumb)): ?>
                                <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="game-thumb">
                                <?php else: ?>
                                <div class="game-thumb" style="background: linear-gradient(135deg, #9B59FF, #C48FFF); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-gamepad" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <?php endif; ?>
                                <div class="game-details">
                                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                                    <p><?= htmlspecialchars($item['platform']) ?> • <?= htmlspecialchars($item['age_rating']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($item['developer_name']) ?></td>
                        <td class="amount-cell">$<?= number_format($item['invested_amount'], 2) ?></td>
                        <td class="equity-cell"><?= $item['equity_percentage'] ? number_format($item['equity_percentage'], 1) . '%' : 'N/A' ?></td>
                        <td>
                            <span class="status-badge <?= $item['investment_status'] ?>">
                                <?= ucfirst($item['investment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.9rem;">
                                <div style="color: #FFB800;">
                                    <i class="fas fa-star"></i> <?= number_format($item['rating'] ?? 0, 1) ?>
                                </div>
                                <div style="color: #666;">
                                    <i class="fas fa-download"></i> <?= number_format($item['downloads'] ?? 0) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="index.php?page=game&id=<?= $item['id'] ?>" class="btn-view-game">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
