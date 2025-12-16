<?php
require "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'developer') {
    header("Location: index.php?page=login");
    exit;
}
$user = $_SESSION['user'];

$error = "";
$success = "";

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=? AND developer_id=?");
    $stmt->execute([$delete_id, $user['id']]);
    $project = $stmt->fetch();
    
    if ($project) {
        // Delete file from server
        if (file_exists($project['file_path'])) {
            unlink($project['file_path']);
        }
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id=?");
        $stmt->execute([$delete_id]);
        $success = "Game deleted successfully!";
    }
}

// Get existing uploads for this user
$stmt = $pdo->prepare("SELECT * FROM projects WHERE developer_id=? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$uploads = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if POST data was received (can be empty if file size exceeds post_max_size)
    if (empty($_POST) && empty($_FILES)) {
        $error = "File size exceeds server limit. Maximum allowed size is " . ini_get('post_max_size') . ". Please upload a smaller file or contact support to increase limits.";
        error_log("Upload failed: POST data empty, likely exceeded post_max_size for user " . $user['id']);
    } 
    // Check if required fields exist
    elseif (!isset($_POST['title']) || !isset($_POST['description']) || !isset($_FILES['gamefile'])) {
        $error = "Missing required fields. Please fill in all information.";
        error_log("Upload failed: Missing fields for user " . $user['id']);
    }
    // Check if file was uploaded without errors
    elseif (!isset($_FILES['gamefile']['error']) || $_FILES['gamefile']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['gamefile']['error'] ?? 'unknown';
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $error = $errorMessages[$errorCode] ?? "Unknown upload error (code: $errorCode)";
        error_log("File upload error for user " . $user['id'] . ": " . $error);
    }
    else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $demo_flag = isset($_POST['demo_flag_submit']) ? 1 : 0;
        $preorder_flag = isset($_POST['preorder_flag']) ? 1 : 0;
        $platform = trim($_POST['platform'] ?? 'Windows');
        $age_rating = trim($_POST['age_rating'] ?? 'Everyone');

        // Validate fields
        if (empty($title)) {
            $error = "Game title is required.";
        } elseif (empty($description)) {
            $error = "Game description is required.";
        } else {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $filename = basename($_FILES["gamefile"]["name"]);
            $targetFile = $targetDir . time() . "_" . $filename;
            
            // Check file size (100MB limit as safety)
            $maxSize = 10000 * 1024 * 1024; // 10000MB in bytes
            if ($_FILES['gamefile']['size'] > $maxSize) {
                $error = "File size exceeds 10GB limit. Please compress your game or contact support.";
            } else {
                if (move_uploaded_file($_FILES["gamefile"]["tmp_name"], $targetFile)) {
                    $stmt = $pdo->prepare("INSERT INTO projects (developer_id,title,description,price,demo_flag,file_path,platform,age_rating) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->execute([$user['id'],$title,$description,$price,$demo_flag,$targetFile,$platform,$age_rating]);
                    $success = "Game uploaded successfully!";
                    // Refresh uploads list
                    $stmt = $pdo->prepare("SELECT * FROM projects WHERE developer_id=? ORDER BY id DESC");
                    $stmt->execute([$user['id']]);
                    $uploads = $stmt->fetchAll();
                } else {
                    error_log("File move failed for user " . $user['id']);
                    $error = "Failed to save uploaded file. Please try again.";
                }
            }
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
    if (successMsg) {
        successMsg.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => successMsg.remove(), 300);
    }
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
    if (errorMsg) {
        errorMsg.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => errorMsg.remove(), 300);
    }
}, 5000);
</script>
<?php endif; ?>

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
        <a href="index.php?page=upload" class="sidebar-item active">
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
        <div class="upload-layout">
            <!-- Left Section - Upload Form -->
            <div class="upload-section">
                <h2 class="upload-title">Uploads</h2>
                
                <?php if (count($uploads) > 0): ?>
                <div class="uploaded-files">
                    <?php foreach ($uploads as $upload): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <h4 class="file-name"><?= htmlspecialchars($upload['title']) ?></h4>
                            <p class="file-size">
                                <?php if (file_exists($upload['file_path'])): ?>
                                    <?= round(filesize($upload['file_path']) / 1024 / 1024, 2) ?>mb
                                <?php else: ?>
                                    File not found
                                <?php endif; ?>
                                • <a href="#">Change display name</a>
                            </p>
                            <p class="file-meta">0 Downloads, <?= date('F j \a\t g:i A', strtotime($upload['created_at'] ?? 'now')) ?></p>
                            <select class="file-type-select">
                                <option>Graphical assets</option>
                                <option>Game executable</option>
                                <option>Source code</option>
                            </select>
                        </div>
                        <div class="file-actions">
                            <a href="index.php?page=edit&id=<?= $upload['id'] ?>" class="link-action">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?page=upload&delete=<?= $upload['id'] ?>" class="link-action danger" onclick="return confirm('Are you sure you want to delete this game?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div id="initial-view">
                        <div class="upload-options" style="display:none;" id="uploadOptions">
                            <label class="checkbox-label">
                                <input type="checkbox" name="different_price">
                                <span>Set a different price for this file</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="demo_flag" id="demo_flag">
                                <span>This file is a demo and can be downloaded for free</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="preorder_flag">
                                <span>This file is a pre-order placeholder</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="hide_file">
                                <span>Hide this file and prevent it from being downloaded</span>
                            </label>
                        </div>
                        
                        <div class="upload-actions">
                            <input type="file" name="gamefile" id="gamefile" accept=".zip,.rar,.7z,.exe,.apk" style="display:none;" required>
                            
                            <button type="button" class="btn-upload" onclick="document.getElementById('gamefile').click();">
                                <i class="fas fa-upload"></i> Upload files
                            </button>
                            <span class="or-text">or</span>
                            <a href="#" class="add-external">Add External File</a>
                        </div>
                        
                        <p class="file-limit">
                            File size limit: 100 MB (current server limit: <?= ini_get('upload_max_filesize') ?>). 
                            <a href="index.php?page=contact">Contact us</a> if you need to upload larger files.
                        </p>
                        <p class="file-limit" style="font-size:0.8rem; color:#999; margin-top:5px;">
                            <i class="fas fa-info-circle"></i> Supported formats: .zip, .rar, .7z, .exe, .apk
                        </p>
                    </div>
                    
                    <div id="extra-fields" style="display:none; margin-top:20px; padding:20px; background:#f5f5f5; border-radius:10px;">
                        <p style="margin-bottom:15px; color:#333;"><strong>Selected file:</strong> <span id="fileName"></span></p>
                        
                        <label style="display:block; margin-bottom:15px;">
                            <strong style="color:#333;">Game Title:</strong>
                            <input type="text" name="title" id="title" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-top:5px;">
                        </label>
                        
                        <label style="display:block; margin-bottom:15px;">
                            <strong style="color:#333;">Description:</strong>
                            <textarea name="description" id="description" rows="4" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-top:5px;"></textarea>
                        </label>
                        
                        <label style="display:block; margin-bottom:15px;">
                            <strong style="color:#333;">Price ($):</strong>
                            <input type="number" name="price" id="price" step="0.01" value="0" min="0" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-top:5px;">
                        </label>
                        
                        <label style="display:block; margin-bottom:15px;">
                            <strong style="color:#333;">Platform:</strong>
                            <select name="platform" id="platform" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-top:5px; cursor:pointer;">
                                <option value="Windows">Windows</option>
                                <option value="macOS">macOS</option>
                                <option value="Linux">Linux</option>
                                <option value="Android">Android</option>
                                <option value="iOS">iOS</option>
                                <option value="Web">Web Browser</option>
                                <option value="Cross-platform">Cross-platform</option>
                            </select>
                        </label>
                        
                        <label style="display:block; margin-bottom:15px;">
                            <strong style="color:#333;">Age Rating:</strong>
                            <select name="age_rating" id="age_rating" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-top:5px; cursor:pointer;">
                                <option value="Everyone">Everyone</option>
                                <option value="Everyone 10+">Everyone 10+</option>
                                <option value="Teen">Teen (13+)</option>
                                <option value="Mature 17+">Mature 17+</option>
                                <option value="Adults Only 18+">Adults Only 18+</option>
                            </select>
                        </label>
                        
                        <div style="margin-bottom:15px;">
                            <label class="checkbox-label">
                                <input type="checkbox" name="demo_flag_submit">
                                <span>This is a free demo</span>
                            </label>
                        </div>
                        
                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn-primary" style="padding:12px 30px; background:#9B59FF; color:white; border:none; border-radius:8px; cursor:pointer; font-size:1rem;">
                                <i class="fas fa-check"></i> Complete Upload
                            </button>
                            <button type="button" class="btn-secondary" onclick="cancelUpload()" style="padding:12px 30px; background:#ccc; color:#333; border:none; border-radius:8px; cursor:pointer; font-size:1rem;">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Right Section - Screenshot Preview (only shows when uploading) -->
            <div class="screenshot-section" id="screenshotSection" style="display:none;">
                <div class="screenshot-placeholder" id="screenshotPreview">
                    <img id="previewImage" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23999' font-size='14' dy='.3em'%3ENo Preview%3C/text%3E%3C/svg%3E" alt="Preview">
                </div>
                <input type="file" id="screenshotInput" accept="image/*" style="display:none;" multiple>
                <button type="button" class="btn-add-screenshot" onclick="document.getElementById('screenshotInput').click();">Add screenshots</button>
                <p style="font-size:0.85rem; color:#999; margin-top:10px; text-align:center;">
                    <i class="fas fa-info-circle"></i> Supports JPG, PNG, GIF (Max 5MB per image)
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.upload-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 30px;
}

.upload-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.upload-title {
    font-size: 1.8rem;
    margin: 0 0 25px 0;
    color: #333;
}

.uploaded-files {
    margin-bottom: 30px;
}

.file-item {
    display: flex;
    justify-content: space-between;
    padding: 20px;
    background: #F9F9F9;
    border-radius: 10px;
    margin-bottom: 15px;
}

.file-info {
    flex: 1;
}

.file-name {
    font-size: 1.1rem;
    margin: 0 0 5px 0;
    color: #333;
}

.file-size {
    font-size: 0.9rem;
    color: #666;
    margin: 5px 0;
}

.file-size a {
    color: #9B59FF;
    text-decoration: none;
}

.file-meta {
    font-size: 0.85rem;
    color: #999;
    margin: 5px 0;
}

.file-type-select {
    padding: 8px 12px;
    border: 1px solid #DDD;
    border-radius: 5px;
    margin-top: 10px;
    font-size: 0.9rem;
}

.file-actions {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.link-action {
    color: #9B59FF;
    text-decoration: none;
    font-size: 0.9rem;
}

.link-action.danger {
    color: #E74C3C;
}

.upload-options {
    margin: 20px 0;
}

.checkbox-label {
    display: block;
    margin: 12px 0;
    font-size: 0.95rem;
    color: #333;
    cursor: pointer;
}

.checkbox-label input {
    margin-right: 10px;
}

.upload-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 25px 0;
}

.btn-upload {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    background: #E74C3C;
    color: white;
}

.btn-upload:hover {
    background: #C0392B;
}

.or-text {
    color: #999;
    font-style: italic;
}

.add-external {
    color: #9B59FF;
    text-decoration: none;
    font-size: 0.9rem;
}

.file-limit {
    font-size: 0.85rem;
    color: #999;
    margin-top: 15px;
}

.file-limit a {
    color: #9B59FF;
    text-decoration: none;
}

.screenshot-section {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    text-align: center;
}

.screenshot-placeholder {
    width: 100%;
    height: 200px;
    background: #F5F5F5;
    border-radius: 10px;
    margin-bottom: 15px;
    overflow: hidden;
}

.screenshot-placeholder img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-add-screenshot {
    background: #E74C3C;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-add-screenshot:hover {
    background: #C0392B;
}

@media (max-width: 1024px) {
    .upload-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Handle game file selection
document.getElementById('gamefile').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('fileName').textContent = this.files[0].name;
        document.getElementById('extra-fields').style.display = 'block';
        document.getElementById('uploadOptions').style.display = 'block';
        document.getElementById('screenshotSection').style.display = 'block';
    }
});

// Handle screenshot selection and preview
document.getElementById('screenshotInput').addEventListener('change', function() {
    if (this.files.length > 0) {
        const file = this.files[0];
        
        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert('Screenshot file size must be less than 5MB');
            this.value = '';
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file (JPG, PNG, GIF)');
            this.value = '';
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('previewImage');
            previewImg.src = e.target.result;
            previewImg.style.objectFit = 'cover';
        };
        reader.readAsDataURL(file);
        
        console.log('Screenshot selected:', file.name);
    }
});

function cancelUpload() {
    document.getElementById('uploadForm').reset();
    document.getElementById('extra-fields').style.display = 'none';
    document.getElementById('uploadOptions').style.display = 'none';
    document.getElementById('fileName').textContent = '';
    
    // Reset screenshot preview
    const previewImg = document.getElementById('previewImage');
    previewImg.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23999' font-size='14' dy='.3em'%3ENo Preview%3C/text%3E%3C/svg%3E";
    document.getElementById('screenshotInput').value = '';
}
</script>