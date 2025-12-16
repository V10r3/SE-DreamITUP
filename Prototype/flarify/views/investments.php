<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'investor') {
    header("Location: index.php?page=dashboard");
    exit;
}

$user = $_SESSION['user'];

// Get all investments with detailed information
$stmt = $pdo->prepare("
    SELECT i.*, p.title, p.platform, p.age_rating, p.rating, p.downloads,
           u.name AS developer_name,
           (SELECT SUM(amount) FROM investments WHERE project_id = p.id AND status = 'active') AS project_total_funding
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN users u ON p.developer_id = u.id
    WHERE i.investor_id = ?
    ORDER BY i.invested_at DESC
");
$stmt->execute([$user['id']]);
$investments = $stmt->fetchAll();

// Calculate analytics
$total_invested = 0;
$by_status = ['active' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
$by_month = [];

foreach ($investments as $inv) {
    $total_invested += $inv['amount'];
    $by_status[$inv['status']] += $inv['amount'];
    
    $month = date('Y-m', strtotime($inv['invested_at']));
    if (!isset($by_month[$month])) {
        $by_month[$month] = 0;
    }
    $by_month[$month] += $inv['amount'];
}

$avg_investment = count($investments) > 0 ? $total_invested / count($investments) : 0;
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.investments-header {
    margin-bottom: 30px;
}

.investments-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.analytics-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-left: 4px solid;
}

.analytics-card.total {
    border-left-color: #9B59FF;
}

.analytics-card.active {
    border-left-color: #4CAF50;
}

.analytics-card.avg {
    border-left-color: #2196F3;
}

.analytics-card.count {
    border-left-color: #FF9800;
}

.analytics-label {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.analytics-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.investments-timeline {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.timeline-header h2 {
    font-size: 1.5rem;
    color: #333;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.filter-btn.active {
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    border-color: #9B59FF;
}

.filter-btn:hover {
    border-color: #9B59FF;
}

.investment-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    border-left: 3px solid #e0e0e0;
    margin-left: 10px;
    margin-bottom: 20px;
    position: relative;
    transition: all 0.3s;
}

.investment-item:hover {
    background: #f9f9f9;
    border-left-color: #9B59FF;
}

.investment-item::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 20px;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: white;
    border: 3px solid #9B59FF;
}

.investment-date {
    min-width: 100px;
    padding-top: 5px;
}

.investment-date-day {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.investment-date-month {
    font-size: 0.85rem;
    color: #666;
    text-transform: uppercase;
}

.investment-content {
    flex: 1;
}

.investment-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.investment-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: #666;
}

.investment-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.investment-notes {
    background: #f5f5f5;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #666;
    margin-top: 10px;
}

.investment-amount {
    text-align: right;
    padding-top: 5px;
}

.amount-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #9B59FF;
}

.amount-label {
    font-size: 0.85rem;
    color: #666;
}

.equity-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #e3f2fd;
    color: #2196F3;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 5px;
}

.empty-investments {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.empty-investments i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.btn-invest {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #9B59FF, #C48FFF);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: transform 0.2s;
}

.btn-invest:hover {
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
        <a href="index.php?page=portfolio" class="sidebar-item">
            <i class="fas fa-briefcase"></i>
            <span>Portfolio</span>
        </a>
        <a href="index.php?page=investments" class="sidebar-item active">
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
        <div class="investments-header">
            <h1><i class="fas fa-chart-line" style="color: #9B59FF;"></i> Investment History</h1>
            <p>Track your investment activity and analytics</p>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card total">
                <div class="analytics-label">Total Invested</div>
                <div class="analytics-value">$<?= number_format($total_invested, 2) ?></div>
            </div>

            <div class="analytics-card active">
                <div class="analytics-label">Active Investments</div>
                <div class="analytics-value">$<?= number_format($by_status['active'], 2) ?></div>
            </div>

            <div class="analytics-card avg">
                <div class="analytics-label">Avg. Investment</div>
                <div class="analytics-value">$<?= number_format($avg_investment, 2) ?></div>
            </div>

            <div class="analytics-card count">
                <div class="analytics-label">Total Deals</div>
                <div class="analytics-value"><?= count($investments) ?></div>
            </div>
        </div>

        <?php if (count($investments) === 0): ?>
        <div class="empty-investments">
            <i class="fas fa-chart-line"></i>
            <h2>No investment history</h2>
            <p>Start investing in game projects to see your activity here</p>
            <a href="index.php?page=dashboard" class="btn-invest">Explore Games</a>
        </div>
        <?php else: ?>
        <div class="investments-timeline">
            <div class="timeline-header">
                <h2>Investment Timeline</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active">All</button>
                    <button class="filter-btn">Active</button>
                    <button class="filter-btn">Completed</button>
                </div>
            </div>

            <?php foreach ($investments as $inv): ?>
            <div class="investment-item">
                <div class="investment-date">
                    <div class="investment-date-day"><?= date('d', strtotime($inv['invested_at'])) ?></div>
                    <div class="investment-date-month"><?= date('M Y', strtotime($inv['invested_at'])) ?></div>
                </div>

                <div class="investment-content">
                    <div class="investment-title"><?= htmlspecialchars($inv['title']) ?></div>
                    <div class="investment-meta">
                        <span><i class="fas fa-user"></i> <?= htmlspecialchars($inv['developer_name']) ?></span>
                        <span><i class="fas fa-desktop"></i> <?= htmlspecialchars($inv['platform']) ?></span>
                        <span class="status-badge <?= $inv['status'] ?>"><?= ucfirst($inv['status']) ?></span>
                    </div>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                        <i class="fas fa-star" style="color: #FFB800;"></i> <?= number_format($inv['rating'] ?? 0, 1) ?>
                        <span style="margin-left: 15px;"><i class="fas fa-download"></i> <?= number_format($inv['downloads'] ?? 0) ?> downloads</span>
                        <?php if ($inv['project_total_funding']): ?>
                        <span style="margin-left: 15px;"><i class="fas fa-chart-pie"></i> Total Funding: $<?= number_format($inv['project_total_funding'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($inv['equity_percentage']): ?>
                    <div class="equity-badge">
                        <i class="fas fa-percentage"></i> <?= number_format($inv['equity_percentage'], 1) ?>% Equity
                    </div>
                    <?php endif; ?>
                    <?php if ($inv['notes']): ?>
                    <div class="investment-notes">
                        <i class="fas fa-sticky-note"></i> <?= htmlspecialchars($inv['notes']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="investment-amount">
                    <div class="amount-value">$<?= number_format($inv['amount'], 2) ?></div>
                    <div class="amount-label">Invested</div>
                    <a href="index.php?page=game&id=<?= $inv['project_id'] ?>" style="display: inline-block; margin-top: 10px; color: #9B59FF; text-decoration: none; font-size: 0.9rem;">
                        <i class="fas fa-eye"></i> View Game
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter functionality
const filterBtns = document.querySelectorAll('.filter-btn');
filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // Filter logic would go here
    });
});
</script>
