<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['userrole'] !== 'developer') {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

$error = "";
$success = "";

// Get project ID
if (!isset($_GET['id'])) {
    header("Location: index.php?page=library");
    exit;
}

$project_id = (int)$_GET['id'];

// Get project details
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id=? AND developer_id=?");
$stmt->execute([$project_id, $user['id']]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: index.php?page=library");
    exit;
}

// Fetch user's teams
$stmt = $pdo->prepare("
    SELECT t.id, t.team_name 
    FROM teams t
    INNER JOIN team_members tm ON t.id = tm.team_id
    WHERE tm.user_id = ?
    ORDER BY t.team_name ASC
");
$stmt->execute([$user['id']]);
$user_teams = $stmt->fetchAll();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $demo_flag = isset($_POST['demo_flag']) ? 1 : 0;
    $platform = trim($_POST['platform'] ?? 'Windows');
    $age_rating = trim($_POST['age_rating'] ?? 'Everyone');
    
    // Get team and credit type
    $team_id = !empty($_POST['team_id']) ? (int)$_POST['team_id'] : null;
    $credit_type = isset($_POST['credit_type']) ? $_POST['credit_type'] : 'developer';
    
    // If team selected, validate user is member
    if ($team_id) {
        $stmt = $pdo->prepare("SELECT 1 FROM team_members WHERE team_id=? AND user_id=?");
        $stmt->execute([$team_id, $user['id']]);
        if (!$stmt->fetch()) {
            $team_id = null;
            $credit_type = 'developer';
        }
    }

    if (empty($title)) {
        $error = "Game title is required.";
    } elseif (empty($description)) {
        $error = "Game description is required.";
    } else {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $newFilePath = $project['file_path'];
        $newBannerPath = $project['banner_path'] ?? null;
        $screenshotPaths = json_decode($project['screenshots'] ?? '[]', true) ?: [];
        
        // Handle game file upload
        if (isset($_FILES['gamefile']) && $_FILES['gamefile']['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES["gamefile"]["name"]);
            $targetFile = $targetDir . time() . "_" . $filename;
            
            if ($_FILES['gamefile']['size'] > 100 * 1024 * 1024) {
                $error = "Game file exceeds 100MB limit.";
            } elseif (move_uploaded_file($_FILES["gamefile"]["tmp_name"], $targetFile)) {
                if (file_exists($project['file_path'])) unlink($project['file_path']);
                $newFilePath = $targetFile;
            } else {
                $error = "Failed to upload game file.";
            }
        }
        
        // Handle banner upload
        if (!$error && isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $bannerName = basename($_FILES["banner"]["name"]);
            $bannerFile = $targetDir . "banner_" . time() . "_" . $bannerName;
            
            if ($_FILES['banner']['size'] > 5 * 1024 * 1024) {
                $error = "Banner image exceeds 5MB limit.";
            } elseif (!getimagesize($_FILES["banner"]["tmp_name"])) {
                $error = "Banner must be a valid image.";
            } elseif (move_uploaded_file($_FILES["banner"]["tmp_name"], $bannerFile)) {
                if ($project['banner_path'] && file_exists($project['banner_path'])) {
                    unlink($project['banner_path']);
                }
                $newBannerPath = $bannerFile;
            }
        }
        
        // Handle screenshots upload
        if (!$error && isset($_FILES['screenshots']) && is_array($_FILES['screenshots']['name'])) {
            foreach ($_FILES['screenshots']['name'] as $key => $name) {
                if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
                    $screenshotName = basename($name);
                    $screenshotFile = $targetDir . "screenshot_" . time() . "_" . $key . "_" . $screenshotName;
                    
                    if ($_FILES['screenshots']['size'][$key] > 5 * 1024 * 1024) {
                        continue; // Skip files over 5MB
                    }
                    if (move_uploaded_file($_FILES['screenshots']['tmp_name'][$key], $screenshotFile)) {
                        $screenshotPaths[] = $screenshotFile;
                    }
                }
            }
        }
        
        // Update database if no errors
        if (!$error) {
            $screenshotsJson = json_encode($screenshotPaths);
            $stmt = $pdo->prepare("UPDATE projects SET title=?, projectdescription=?, price=?, demo_flag=?, file_path=?, banner_path=?, screenshots=?, platform=?, age_rating=?, team_id=?, credit_type=? WHERE id=?");
            $stmt->execute([$title, $description, $price, $demo_flag, $newFilePath, $newBannerPath, $screenshotsJson, $platform, $age_rating, $team_id, $credit_type, $project_id]);
            $success = "Game updated successfully!";
            
            // Refresh project data
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch();
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
        <div style="max-width:800px; margin:0 auto;">
            <div style="margin-bottom:20px;">
                <a href="index.php?page=library" style="color:#9B59FF; text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Back to Library
                </a>
            </div>
            
            <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                <h1 style="margin:0 0 30px 0; color:#333;">
                    <i class="fas fa-edit"></i> Edit Game
                </h1>

                <form method="POST" enctype="multipart/form-data">
                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Game Title *
                        </label>
                        <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" required 
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Description *
                        </label>
                        <textarea name="description" rows="6" required 
                                  style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem; resize:vertical;"><?= htmlspecialchars($project['projectdescription']) ?></textarea>
                    </div>

                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Price ($)
                        </label>
                        <input type="number" name="price" step="0.01" value="<?= $project['price'] ?>" min="0" 
                               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
                    </div>

                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Platform
                        </label>
                        <select name="platform" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem; cursor:pointer;">
                            <option value="Windows" <?= ($project['platform'] ?? 'Windows') === 'Windows' ? 'selected' : '' ?>>Windows</option>
                            <option value="macOS" <?= ($project['platform'] ?? '') === 'macOS' ? 'selected' : '' ?>>macOS</option>
                            <option value="Linux" <?= ($project['platform'] ?? '') === 'Linux' ? 'selected' : '' ?>>Linux</option>
                            <option value="Android" <?= ($project['platform'] ?? '') === 'Android' ? 'selected' : '' ?>>Android</option>
                            <option value="iOS" <?= ($project['platform'] ?? '') === 'iOS' ? 'selected' : '' ?>>iOS</option>
                            <option value="Web" <?= ($project['platform'] ?? '') === 'Web' ? 'selected' : '' ?>>Web Browser</option>
                            <option value="Cross-platform" <?= ($project['platform'] ?? '') === 'Cross-platform' ? 'selected' : '' ?>>Cross-platform</option>
                        </select>
                    </div>

                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            Age Rating
                        </label>
                        <select name="age_rating" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem; cursor:pointer;">
                            <option value="Everyone" <?= ($project['age_rating'] ?? 'Everyone') === 'Everyone' ? 'selected' : '' ?>>Everyone</option>
                            <option value="Everyone 10+" <?= ($project['age_rating'] ?? '') === 'Everyone 10+' ? 'selected' : '' ?>>Everyone 10+</option>
                            <option value="Teen" <?= ($project['age_rating'] ?? '') === 'Teen' ? 'selected' : '' ?>>Teen (13+)</option>
                            <option value="Mature 17+" <?= ($project['age_rating'] ?? '') === 'Mature 17+' ? 'selected' : '' ?>>Mature 17+</option>
                            <option value="Adults Only 18+" <?= ($project['age_rating'] ?? '') === 'Adults Only 18+' ? 'selected' : '' ?>>Adults Only 18+</option>
                        </select>
                    </div>

                    <?php if (count($user_teams) > 0): ?>
                    <div style="margin-bottom:25px; padding:20px; background:#f9f9f9; border-radius:10px; border:1px solid #e0e0e0;">
                        <label style="display:block; margin-bottom:12px; color:#333; font-weight:600;">
                            <i class="fas fa-users"></i> Team Credit Options
                        </label>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:8px; color:#555;">Select Team (Optional):</label>
                            <select name="team_id" id="team_id" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; cursor:pointer;" onchange="toggleCreditOptions()">
                                <option value="">No Team (Personal Project)</option>
                                <?php foreach ($user_teams as $team): ?>
                                    <option value="<?= $team['id'] ?>" <?= ($project['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($team['team_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="creditTypeOptions" style="<?= !empty($project['team_id']) ? 'display:block;' : 'display:none;' ?> margin-top:15px;">
                            <label style="display:block; margin-bottom:8px; color:#555; font-weight:600;">Credit:</label>
                            <label style="display:block; margin-bottom:10px; cursor:pointer;">
                                <input type="radio" name="credit_type" value="developer" <?= ($project['credit_type'] ?? 'developer') === 'developer' ? 'checked' : '' ?> style="margin-right:8px;">
                                <span style="color:#333;">Just Me</span>
                            </label>
                            <label style="display:block; margin-bottom:10px; cursor:pointer;">
                                <input type="radio" name="credit_type" value="team" <?= ($project['credit_type'] ?? '') === 'team' ? 'checked' : '' ?> style="margin-right:8px;">
                                <span style="color:#333;">Just the Team</span>
                            </label>
                            <label style="display:block; margin-bottom:10px; cursor:pointer;">
                                <input type="radio" name="credit_type" value="both" <?= ($project['credit_type'] ?? '') === 'both' ? 'checked' : '' ?> style="margin-right:8px;">
                                <span style="color:#333;">Both Me & Team</span>
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div style="margin-bottom:25px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="demo_flag" <?= $project['demo_flag'] ? 'checked' : '' ?> 
                                   style="width:20px; height:20px;">
                            <span style="color:#333;">This is a free demo</span>
                        </label>
                    </div>

                    <div style="margin-bottom:25px; padding:20px; background:#f9f9f9; border-radius:10px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            <i class="fas fa-file-upload"></i> Replace Game File (Optional)
                        </label>
                        <p style="color:#666; font-size:0.9rem; margin-bottom:10px;">
                            Current file: <?= htmlspecialchars(basename($project['file_path'])) ?>
                        </p>
                        <input type="file" name="gamefile" accept=".zip,.rar,.7z,.exe,.apk"
                               style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                        <p style="color:#999; font-size:0.85rem; margin-top:8px;">
                            <i class="fas fa-info-circle"></i> Leave empty to keep current file. Max 100MB.
                        </p>
                    </div>

                    <div style="margin-bottom:25px; padding:20px; background:#f9f9f9; border-radius:10px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            <i class="fas fa-image"></i> Game Banner (Optional)
                        </label>
                        <?php if ($project['banner_path'] ?? null): ?>
                        <div style="margin-bottom:10px;">
                            <img src="<?= htmlspecialchars($project['banner_path']) ?>" alt="Current banner" 
                                 style="max-width:100%; height:auto; border-radius:8px; border:2px solid #ddd;">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="banner" accept="image/*" id="bannerInput"
                               style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                        <p style="color:#999; font-size:0.85rem; margin-top:8px;">
                            <i class="fas fa-info-circle"></i> Recommended size: 800x450px. Max 5MB.
                        </p>
                        <div id="bannerPreview" style="margin-top:15px;"></div>
                    </div>

                    <div style="margin-bottom:25px; padding:20px; background:#f9f9f9; border-radius:10px;">
                        <label style="display:block; margin-bottom:8px; color:#333; font-weight:600;">
                            <i class="fas fa-images"></i> Game Screenshots (Optional)
                        </label>
                        <?php 
                        $existingScreenshots = json_decode($project['screenshots'] ?? '[]', true) ?: [];
                        if (count($existingScreenshots) > 0): 
                        ?>
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:10px; margin-bottom:15px;">
                            <?php foreach ($existingScreenshots as $screenshot): ?>
                            <img src="<?= htmlspecialchars($screenshot) ?>" alt="Screenshot" 
                                 style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:2px solid #ddd;">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="screenshots[]" accept="image/*" multiple id="screenshotInput"
                               style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                        <p style="color:#999; font-size:0.85rem; margin-top:8px;">
                            <i class="fas fa-info-circle"></i> You can upload multiple images. Max 5MB per image. New screenshots will be added to existing ones.
                        </p>
                        <div id="screenshotPreview" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:10px; margin-top:15px;"></div>
                    </div>

                    <div style="display:flex; gap:15px; margin-top:30px;">
                        <button type="submit" 
                                style="flex:1; padding:15px; background:#9B59FF; color:white; border:none; border-radius:8px; font-size:1.1rem; font-weight:600; cursor:pointer;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="index.php?page=library" 
                           style="flex:1; padding:15px; background:#e0e0e0; color:#333; border:none; border-radius:8px; font-size:1.1rem; font-weight:600; text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview banner
document.getElementById('bannerInput').addEventListener('change', function() {
    const previewContainer = document.getElementById('bannerPreview');
    previewContainer.innerHTML = '';
    
    if (this.files.length > 0) {
        const file = this.files[0];
        
        if (file.size > 5 * 1024 * 1024) {
            alert('Banner exceeds 5MB limit');
            this.value = '';
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'max-width:100%; height:auto; border-radius:8px; border:2px solid #9B59FF;';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

// Preview screenshots
document.getElementById('screenshotInput').addEventListener('change', function() {
    const previewContainer = document.getElementById('screenshotPreview');
    previewContainer.innerHTML = '';
    
    if (this.files.length > 0) {
        Array.from(this.files).forEach((file, index) => {
            // Check file size
            if (file.size > 5 * 1024 * 1024) {
                alert('Screenshot ' + (index + 1) + ' exceeds 5MB limit');
                return;
            }
            
            // Check file type
            if (!file.type.startsWith('image/')) {
                alert('File ' + (index + 1) + ' is not an image');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'position:relative; border-radius:8px; overflow:hidden; border:2px solid #ddd;';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width:100%; height:120px; object-fit:cover;';
                
                div.appendChild(img);
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
});

function toggleCreditOptions() {
    const teamSelect = document.getElementById('team_id');
    const creditOptions = document.getElementById('creditTypeOptions');
    
    if (teamSelect && creditOptions) {
        if (teamSelect.value) {
            creditOptions.style.display = 'block';
        } else {
            creditOptions.style.display = 'none';
        }
    }
}
</script>
