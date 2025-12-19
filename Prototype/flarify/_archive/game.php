<?php
// Redirect to new route
if (isset($_GET['id'])) {
    header("Location: index.php?page=game&id=" . $_GET['id']);
} else {
    header("Location: index.php");
}
exit;

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT p.*, u.username AS dev_name, u.id AS dev_id
    FROM projects p
    JOIN users u ON p.developer_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    echo "Game not found.";
    exit;
}

$user = $_SESSION['user'] ?? null;

// Get user's rating if they've rated this game
$user_rating = null;
if ($user) {
    $stmt = $pdo->prepare("SELECT rating FROM project_ratings WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$id, $user['id']]);
    $user_rating_row = $stmt->fetch();
    $user_rating = $user_rating_row ? $user_rating_row['rating'] : null;
}

// Check if user can rate (not the developer of this game)
$can_rate = $user && (!($user['userrole'] === 'developer' && $game['developer_id'] == $user['id']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($game['title']) ?> - Flarify</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="assets/dashboard-styles.css">
  <style>
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
    
    .dashboard-content {
      background: #1a1a1a !important;
    }
    
    .game-page-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .game-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
    }
    
    .game-title-section h1 {
      font-size: 48px;
      font-weight: 700;
      color: #fff;
      margin: 0 0 10px 0;
    }
    
    .game-meta {
      color: #aaa;
      font-size: 14px;
      margin-bottom: 5px;
    }
    
    .game-meta a {
      color: #9B59FF;
      text-decoration: none;
    }
    
    .game-meta a:hover {
      text-decoration: underline;
    }
    
    .game-platforms {
      color: #888;
      font-size: 13px;
    }
    
    .game-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: flex-end;
    }
    
    .btn-play-demo {
      background: linear-gradient(135deg, #9B59FF, #C48FFF);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 25px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: transform 0.2s;
    }
    
    .btn-play-demo:hover {
      transform: translateY(-2px);
    }
    
    .action-buttons {
      display: flex;
      gap: 15px;
    }
    
    .action-btn {
      background: transparent;
      border: none;
      color: #aaa;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
      transition: color 0.2s;
    }
    
    .action-btn:hover {
      color: #9B59FF;
    }
    
    .game-banners {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 15px;
      margin-bottom: 30px;
    }
    
    .game-banner {
      border-radius: 10px;
      overflow: hidden;
      background: #2a2a2a;
      aspect-ratio: 16/9;
    }
    
    .game-banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .game-stats {
      display: flex;
      gap: 40px;
      margin-bottom: 30px;
      padding: 20px 0;
      border-bottom: 1px solid #333;
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-value {
      font-size: 18px;
      font-weight: 600;
      color: #fff;
      display: block;
    }
    
    .stat-label {
      font-size: 12px;
      color: #888;
      margin-top: 5px;
      display: block;
    }
    
    .game-description {
      color: #ccc;
      line-height: 1.8;
      margin-bottom: 30px;
    }
    
    .game-description p {
      margin-bottom: 15px;
    }
    
    .game-section {
      margin-bottom: 30px;
    }
    
    .section-title {
      font-size: 20px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #9B59FF;
    }
    
    .controls-list {
      background: #222;
      padding: 20px;
      border-radius: 10px;
      color: #ccc;
      line-height: 1.8;
    }
    
    .controls-list p {
      margin: 5px 0;
      text-align: center;
    }
    
    .feedback-section {
      background: #222;
      padding: 20px;
      border-radius: 10px;
      margin-top: 30px;
    }
    
    .feedback-section textarea {
      width: 100%;
      min-height: 120px;
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 8px;
      color: #fff;
      padding: 15px;
      font-size: 14px;
      resize: vertical;
      margin-bottom: 15px;
    }
    
    .feedback-section textarea:focus {
      outline: none;
      border-color: #9B59FF;
    }
    
    .btn-send-feedback {
      background: linear-gradient(135deg, #9B59FF, #C48FFF);
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s;
    }
    
    .btn-send-feedback:hover {
      transform: translateY(-2px);
    }
    
    .contact-developer {
      text-align: center;
      padding: 30px;
      background: #222;
      border-radius: 10px;
    }
    
    .contact-developer p {
      color: #ccc;
      margin-bottom: 15px;
    }
  </style>
</head>
<body class="dashboard-body">
  <!-- Top Navigation Bar -->
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
      <input type="text" placeholder="Search games...">
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

  <div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
      <a href="index.php?page=dashboard" class="sidebar-item">
        <i class="fas fa-globe"></i>
        <span>Explore</span>
      </a>
      
      <?php if ($user['role'] === 'investor'): ?>
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
      <?php elseif ($user['role'] === 'investor'): ?>
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
      <div class="game-page-content">
        <!-- Game Header -->
        <div class="game-header">
          <div class="game-title-section">
            <h1><?= htmlspecialchars($game['title']) ?></h1>
            <div class="game-meta">
              by <a href="#"><?= htmlspecialchars($game['dev_name']) ?></a>
            </div>
            <div class="game-platforms">
              <?php 
              $platform = $game['platform'] ?? 'Windows';
              if ($platform === 'Cross-platform') {
                echo 'A downloadable game for Windows, macOS, Linux, and Android';
              } else {
                echo 'A downloadable game for ' . htmlspecialchars($platform);
              }
              ?>
            </div>
          </div>
          <div class="game-actions">
            <?php if ($game['demo_flag']): ?>
              <a href="backend/download_game.php?id=<?= $game['id'] ?>" class="btn-play-demo">
                <i class="fas fa-play"></i> Play Demo
              </a>
            <?php else: ?>
              <a href="backend/download_game.php?id=<?= $game['id'] ?>" class="btn-play-demo">
                <i class="fas fa-download"></i> Download - $<?= number_format($game['price'],2) ?>
              </a>
            <?php endif; ?>
            <div class="action-buttons">
              <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'investor'): ?>
              <button class="action-btn" style="background: linear-gradient(135deg, #4CAF50, #81C784); color: white; font-weight: 600;" onclick="openInvestModal()">
                <i class="fas fa-hand-holding-usd"></i> Invest in this Game
              </button>
              <button class="action-btn" id="watchlistBtn" onclick="toggleWatchlist(<?= $game['id'] ?>)">
                <i class="fas fa-star"></i> <span id="watchlistText">Add to Watchlist</span>
              </button>
              <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'tester'): ?>
              <button class="action-btn" id="testQueueBtn" style="background: linear-gradient(135deg, #FF9800, #FB8C00); color: white; font-weight: 600;" onclick="toggleTestingQueue(<?= $game['id'] ?>)">
                <i class="fas fa-flask"></i> <span id="testQueueText">Add to Testing Queue</span>
              </button>
              <button class="action-btn">
                <i class="fas fa-share-alt"></i> Share
              </button>
              <?php else: ?>
              <button class="action-btn">
                <i class="fas fa-share-alt"></i> Share
              </button>
              <button class="action-btn">
                <i class="fas fa-bookmark"></i> Add to wishlist
              </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Game Banners -->
        <?php 
        $screenshots = json_decode($game['screenshots'] ?? '[]', true) ?: [];
        $hasBanner = !empty($game['banner_path']);
        $hasScreenshots = count($screenshots) > 0;
        ?>
        <?php if ($hasBanner || $hasScreenshots): ?>
        <div class="game-banners">
          <div class="game-banner">
            <?php if ($hasBanner): ?>
              <img src="<?= htmlspecialchars($game['banner_path']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
            <?php else: ?>
              <img src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22800%22 height=%22450%22%3E%3Crect fill=%22%232a2a2a%22 width=%22800%22 height=%22450%22/%3E%3Ctext fill=%22%23666%22 font-family=%22Arial%22 font-size=%2224%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EGame Banner%3C/text%3E%3C/svg%3E" alt="No banner">
            <?php endif; ?>
          </div>
          <div class="game-banner">
            <?php if ($hasScreenshots): ?>
              <img src="<?= htmlspecialchars($screenshots[0]) ?>" alt="Screenshot">
            <?php else: ?>
              <img src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22225%22%3E%3Crect fill=%22%232a2a2a%22 width=%22400%22 height=%22225%22/%3E%3Ctext fill=%22%23666%22 font-family=%22Arial%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EScreenshot%3C/text%3E%3C/svg%3E" alt="No screenshot">
            <?php endif; ?>
          </div>
        </div>
        
        <?php if (count($screenshots) > 1): ?>
        <div style="margin-bottom:30px;">
          <h3 style="font-size:18px; color:#fff; margin-bottom:15px;">More Screenshots</h3>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:15px;">
            <?php for ($i = 1; $i < count($screenshots); $i++): ?>
              <img src="<?= htmlspecialchars($screenshots[$i]) ?>" alt="Screenshot <?= $i + 1 ?>" 
                   style="width:100%; height:150px; object-fit:cover; border-radius:8px; cursor:pointer;"
                   onclick="this.requestFullscreen();">
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Game Stats -->
        <div class="game-stats">
          <div class="stat-item">
            <span class="stat-value" id="displayRating">
              <i class="fas fa-star" style="color: #FFD700;"></i> 
              <?= number_format($game['rating'] ?? 0, 1) ?>
            </span>
            <span class="stat-label">Rating (<span id="totalRatings"><?= number_format($game['total_ratings'] ?? 0) ?></span> reviews)</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">
              <?php 
              $downloads = $game['downloads'] ?? 0;
              if ($downloads >= 1000) {
                echo number_format($downloads / 1000, 1) . 'K+';
              } else {
                echo $downloads;
              }
              ?>
            </span>
            <span class="stat-label">Downloads</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?= htmlspecialchars($game['age_rating'] ?? 'Everyone') ?></span>
            <span class="stat-label">Age Rating</span>
          </div>
          <?php if ($game['demo_flag']): ?>
          <div class="stat-item">
            <span class="stat-value"><i class="fas fa-gamepad" style="color: #9B59FF;"></i> DEMO</span>
            <span class="stat-label">Version</span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Rating Section -->
        <?php if ($user && $can_rate): ?>
        <div class="game-section" style="background: #222; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
          <h3 style="margin: 0 0 15px 0; color: #fff;">Rate This Game</h3>
          <div style="display: flex; align-items: center; gap: 15px;">
            <div id="ratingStars" class="rating-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="rating-star <?= $user_rating >= $i ? 'selected' : '' ?>" data-rating="<?= $i ?>">★</span>
              <?php endfor; ?>
            </div>
            <button id="submitRating" class="btn-send-feedback" style="padding: 8px 20px;">
              <?= $user_rating ? 'Update Rating' : 'Submit Rating' ?>
            </button>
            <span id="ratingMessage" style="color: #fff;"></span>
          </div>
        </div>
        <?php elseif ($user && $user['role'] === 'developer' && $game['developer_id'] == $user['id']): ?>
        <div class="game-section" style="background: #222; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
          <p style="color: #999; margin: 0;"><i class="fas fa-info-circle"></i> You cannot rate your own game.</p>
        </div>
        <?php endif; ?>

        <!-- Game Description -->
        <div class="game-section">
          <h2 class="section-title">About This Game</h2>
          <div class="game-description">
            <?= nl2br(htmlspecialchars($game['description'])) ?>
          </div>
        </div>

        <!-- Role-specific Actions -->
        <?php if ($user['role'] === 'tester'): ?>
        <div class="feedback-section">
          <h2 class="section-title">Leave Feedback for Developer</h2>
          <?php if (isset($_GET['success']) && $_GET['success'] === 'sent'): ?>
          <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #c3e6cb;">
            <strong>Success!</strong> Your feedback has been sent to the developer.
          </div>
          <?php endif; ?>
          <form method="POST" action="backend/send_message.php">
            <input type="hidden" name="receiver_id" value="<?= $game['dev_id'] ?>">
            <input type="hidden" name="redirect" value="game&id=<?= $game['id'] ?>">
            <textarea name="content" placeholder="Share your thoughts about this game with the developer..." required></textarea>
            <button type="submit" class="btn-send-feedback">
              <i class="fas fa-paper-plane"></i> Send Feedback
            </button>
          </form>
        </div>
        <?php elseif ($user['role'] === 'investor'): ?>
        <div class="contact-developer">
          <h2 class="section-title">Interested in Funding This Project?</h2>
          <p>Connect with the developer to discuss investment opportunities.</p>
          <a href="index.php?page=messages&to=<?= $game['dev_id'] ?>" class="btn-play-demo">
            <i class="fas fa-envelope"></i> Contact Developer
          </a>
        </div>
        <?php elseif ($user['role'] === 'developer' && $user['id'] === $game['dev_id']): ?>
        <div class="contact-developer">
          <h2 class="section-title">Manage Your Project</h2>
          <p>Update game files, screenshots, and description.</p>
          <a href="index.php?page=edit&id=<?= $game['id'] ?>" class="btn-play-demo">
            <i class="fas fa-edit"></i> Edit Project
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <style>
    .rating-stars {
      font-size: 2rem;
      color: #555;
      cursor: pointer;
    }
    
    .rating-star {
      transition: color 0.2s;
      color: #555;
    }
    
    .rating-star:hover,
    .rating-star.hover {
      color: #FFD700;
    }
    
    .rating-star.selected {
      color: #FFD700;
    }
  </style>

  <script>
    // Rating functionality
    <?php if ($user && $can_rate): ?>
    const ratingStars = document.querySelectorAll('.rating-star');
    let selectedRating = <?= $user_rating ?? 0 ?>;

    ratingStars.forEach(star => {
      star.addEventListener('mouseover', function() {
        const rating = parseInt(this.dataset.rating);
        highlightStars(rating);
      });

      star.addEventListener('mouseout', function() {
        highlightStars(selectedRating);
      });

      star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rating);
        highlightStars(selectedRating);
      });
    });

    function highlightStars(rating) {
      ratingStars.forEach(star => {
        const starRating = parseInt(star.dataset.rating);
        if (starRating <= rating) {
          star.classList.add('selected');
        } else {
          star.classList.remove('selected');
        }
      });
    }

    document.getElementById('submitRating').addEventListener('click', function() {
      if (selectedRating === 0) {
        document.getElementById('ratingMessage').textContent = 'Please select a rating';
        document.getElementById('ratingMessage').style.color = '#ff6b6b';
        return;
      }

      const formData = new FormData();
      formData.append('project_id', <?= $game['id'] ?>);
      formData.append('rating', selectedRating);

      fetch('backend/rate_game.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        const messageEl = document.getElementById('ratingMessage');
        if (data.success) {
          messageEl.textContent = data.message;
          messageEl.style.color = '#51cf66';
          document.getElementById('displayRating').innerHTML = '<i class="fas fa-star" style="color: #FFD700;"></i> ' + data.new_rating;
          document.getElementById('totalRatings').textContent = data.total_ratings;
          document.getElementById('submitRating').textContent = 'Update Rating';
        } else {
          messageEl.textContent = data.message;
          messageEl.style.color = '#ff6b6b';
        }
        setTimeout(() => {
          messageEl.textContent = '';
        }, 3000);
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('ratingMessage').textContent = 'Failed to submit rating';
        document.getElementById('ratingMessage').style.color = '#ff6b6b';
      });
    });
    <?php endif; ?>

    // Screenshot modal functionality
    const screenshotImages = document.querySelectorAll('.screenshot-image');
    screenshotImages.forEach(img => {
      img.addEventListener('click', function() {
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);display:flex;align-items:center;justify-content:center;z-index:10000;cursor:pointer;';
        const fullImg = document.createElement('img');
        fullImg.src = this.src;
        fullImg.style.cssText = 'max-width:90%;max-height:90%;';
        modal.appendChild(fullImg);
        document.body.appendChild(modal);
        modal.addEventListener('click', () => modal.remove());
      });
    });

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'investor'): ?>
    // Watchlist functionality
    let inWatchlist = false;

    // Check if game is in watchlist on page load
    fetch('backend/watchlist.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'action=check&project_id=<?= $game['id'] ?>'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.in_watchlist) {
        inWatchlist = true;
        updateWatchlistButton();
      }
    });

    function toggleWatchlist(projectId) {
      const action = inWatchlist ? 'remove' : 'add';
      
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
          inWatchlist = !inWatchlist;
          updateWatchlistButton();
        } else {
          alert(data.message || 'Failed to update watchlist');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
    }

    function updateWatchlistButton() {
      const btn = document.getElementById('watchlistBtn');
      const text = document.getElementById('watchlistText');
      if (inWatchlist) {
        btn.style.background = 'linear-gradient(135deg, #FFB800, #FFC107)';
        btn.style.color = 'white';
        text.textContent = 'In Watchlist';
        btn.querySelector('i').className = 'fas fa-star';
      } else {
        btn.style.background = '';
        btn.style.color = '';
        text.textContent = 'Add to Watchlist';
        btn.querySelector('i').className = 'fas fa-star';
      }
    }
    <?php endif; ?>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'tester'): ?>
    // Testing queue functionality
    let inTestQueue = false;

    // Check if game is in testing queue on load
    fetch(`backend/testing_queue.php?action=check&project_id=<?= $game['id'] ?>`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.in_queue) {
        inTestQueue = true;
        updateTestQueueButton();
      }
    });

    function toggleTestingQueue(projectId) {
      const action = inTestQueue ? 'remove' : 'add';
      
      if (action === 'add') {
        // Show a simple prompt for notes
        const notes = prompt('Add any notes about this testing (optional):');
        if (notes === null) return; // User cancelled
        
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('project_id', projectId);
        formData.append('notes', notes || '');
        
        fetch('backend/testing_queue.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            inTestQueue = true;
            updateTestQueueButton();
            showToast('Added to testing queue!', 'success');
          } else {
            showToast(data.message || 'Failed to add to queue', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      } else {
        // Find the queue ID first
        fetch(`backend/testing_queue.php?action=list&status=all`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const item = data.queue.find(q => q.project_id == projectId);
            if (item) {
              const formData = new FormData();
              formData.append('action', 'remove');
              formData.append('queue_id', item.id);
              
              fetch('backend/testing_queue.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  inTestQueue = false;
                  updateTestQueueButton();
                  showToast('Removed from testing queue', 'success');
                } else {
                  showToast(data.message || 'Failed to remove', 'error');
                }
              });
            }
          }
        });
      }
    }

    function updateTestQueueButton() {
      const btn = document.getElementById('testQueueBtn');
      const text = document.getElementById('testQueueText');
      if (inTestQueue) {
        btn.style.background = 'linear-gradient(135deg, #4CAF50, #81C784)';
        text.textContent = 'In Testing Queue';
        btn.querySelector('i').className = 'fas fa-check-circle';
      } else {
        btn.style.background = 'linear-gradient(135deg, #FF9800, #FB8C00)';
        text.textContent = 'Add to Testing Queue';
        btn.querySelector('i').className = 'fas fa-flask';
      }
    }
    <?php endif; ?>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'investor'): ?>
    // Investment modal functionality
    function openInvestModal() {
      const modal = document.createElement('div');
      modal.id = 'investModal';
      modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:10000;';
      
      modal.innerHTML = `
        <div style="background:white;border-radius:15px;padding:30px;max-width:500px;width:90%;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="margin:0;color:#333;"><i class="fas fa-hand-holding-usd" style="color:#4CAF50;"></i> Invest in <?= htmlspecialchars($game['title']) ?></h2>
            <button onclick="closeInvestModal()" style="border:none;background:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
          </div>
          
          <form id="investForm" style="display:flex;flex-direction:column;gap:15px;">
            <div>
              <label style="display:block;margin-bottom:5px;font-weight:600;color:#666;">Investment Amount ($)</label>
              <input type="number" id="investAmount" name="amount" min="1" step="0.01" required 
                     style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
            </div>
            
            <div>
              <label style="display:block;margin-bottom:5px;font-weight:600;color:#666;">Equity Percentage (optional)</label>
              <input type="number" id="investEquity" name="equity" min="0" max="100" step="0.1" 
                     style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;"
                     placeholder="e.g., 5.0">
            </div>
            
            <div>
              <label style="display:block;margin-bottom:5px;font-weight:600;color:#666;">Notes (optional)</label>
              <textarea id="investNotes" name="notes" rows="3" 
                        style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"
                        placeholder="Any additional notes about this investment..."></textarea>
            </div>
            
            <div style="background:#f0f7ff;padding:15px;border-radius:8px;font-size:0.9rem;color:#666;">
              <i class="fas fa-info-circle" style="color:#2196F3;"></i> 
              This investment will be tracked in your Portfolio. You'll receive notifications about the game's progress.
            </div>
            
            <button type="submit" style="padding:12px;background:linear-gradient(135deg, #4CAF50, #81C784);color:white;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;">
              <i class="fas fa-check"></i> Confirm Investment
            </button>
          </form>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Handle form submission
      document.getElementById('investForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = document.getElementById('investAmount').value;
        const equity = document.getElementById('investEquity').value;
        const notes = document.getElementById('investNotes').value;
        
        fetch('backend/investments.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=invest&project_id=<?= $game['id'] ?>&amount=${amount}&equity=${equity}&notes=${encodeURIComponent(notes)}`
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(text => {
          try {
            const data = JSON.parse(text);
            if (data.success) {
              closeInvestModal();
              showToast('Investment recorded successfully! View your portfolio to track this investment.', 'success');
              setTimeout(() => location.reload(), 2000);
            } else {
              showToast(data.message || 'Failed to record investment', 'error');
            }
          } catch (e) {
            console.error('Response text:', text);
            showToast('Server error: ' + text.substring(0, 100), 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred: ' + error.message, 'error');
        });
      });
    }

    function closeInvestModal() {
      const modal = document.getElementById('investModal');
      if (modal) {
        modal.remove();
      }
    }

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      const bgColor = type === 'success' ? 'linear-gradient(135deg, #4CAF50, #81C784)' : 'linear-gradient(135deg, #f44336, #ef5350)';
      const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
      
      toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        z-index: 10001;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1rem;
        font-weight: 500;
        max-width: 400px;
        animation: slideIn 0.3s ease;
      `;
      
      toast.innerHTML = `
        <i class="fas ${icon}" style="font-size: 1.5rem;"></i>
        <span>${message}</span>
      `;
      
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 5000);
    }
    <?php endif; ?>
  </script>
</body>
</html>