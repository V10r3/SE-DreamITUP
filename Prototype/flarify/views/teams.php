<?php
/**
 * Teams Management View
 * Allows developers to create and manage teams
 */

require "config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['userrole'] !== 'developer') {
    header("Location: index.php?page=login");
    exit;
}

$user = $_SESSION['user'];
$success = "";
$error = "";

// Handle team creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $team_name = trim($_POST['team_name']);
    $description = trim($_POST['description'] ?? '');
    
    if (empty($team_name)) {
        $error = "Team name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO teams (team_name, teamdescription, owner_id) VALUES (?, ?, ?)");
            $stmt->execute([$team_name, $description, $user['id']]);
            $team_id = $pdo->lastInsertId();
            
            // Add creator as owner member
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, memberrole) VALUES (?, ?, 'owner')");
            $stmt->execute([$team_id, $user['id']]);
            
            $success = "Team created successfully!";
        } catch (PDOException $e) {
            error_log("Team creation failed: " . $e->getMessage());
            $error = "Failed to create team. Please try again.";
        }
    }
}

// Handle adding member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $team_id = (int)$_POST['team_id'];
    $member_email = trim($_POST['member_email']);
    
    // Check if user is team owner/admin
    $stmt = $pdo->prepare("SELECT memberrole FROM team_members WHERE team_id = ? AND user_id = ?");
    $stmt->execute([$team_id, $user['id']]);
    $membership = $stmt->fetch();
    
    if ($membership && in_array($membership['memberrole'], ['owner', 'admin'])) {
        // Find user by email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND userrole = 'developer'");
        $stmt->execute([$member_email]);
        $new_member = $stmt->fetch();
        
        if ($new_member) {
            try {
                $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, memberrole) VALUES (?, ?, 'member')");
                $stmt->execute([$team_id, $new_member['id']]);
                $success = "Member added successfully!";
            } catch (PDOException $e) {
                $error = "Member already in team or error occurred.";
            }
        } else {
            $error = "Developer not found with that email.";
        }
    } else {
        $error = "You don't have permission to add members.";
    }
}

// Get user's teams
$stmt = $pdo->prepare("
    SELECT t.*, tm.memberrole,
           (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) as member_count,
           (SELECT COUNT(*) FROM projects WHERE team_id = t.id) as project_count
    FROM teams t
    JOIN team_members tm ON t.id = tm.team_id
    WHERE tm.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user['id']]);
$teams = $stmt->fetchAll();
?>
<link rel="stylesheet" href="assets/dashboard-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.team-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.team-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.team-stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}
.team-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}
.create-team-form {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
.role-owner { background: #FFD700; color: #333; }
.role-admin { background: #9B59FF; color: white; }
.role-member { background: #E0E0E0; color: #666; }
</style>

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
        <a href="index.php?page=collections" class="sidebar-item">
            <i class="fas fa-play-circle"></i>
            <span>Collections</span>
        </a>
        <a href="index.php?page=teams" class="sidebar-item active">
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
            <h1><i class="fas fa-users"></i> My Teams</h1>
            <p>Create and manage developer teams for collaborative projects</p>
        </div>

        <?php if ($success): ?>
        <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:8px;margin-bottom:20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;border-radius:8px;margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Create Team Form -->
        <div class="create-team-form">
            <h2 style="margin:0 0 20px 0;"><i class="fas fa-plus-circle"></i> Create New Team</h2>
            <form method="POST">
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">
                        <i class="fas fa-tag"></i> Team Name *
                    </label>
                    <input type="text" name="team_name" required 
                           style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;"
                           placeholder="Enter team name">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">
                        <i class="fas fa-align-left"></i> Description (Optional)
                    </label>
                    <textarea name="description" rows="3"
                              style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"
                              placeholder="Describe your team..."></textarea>
                </div>
                <button type="submit" name="create_team" 
                        style="background:#9B59FF;color:white;border:none;padding:12px 30px;border-radius:25px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-plus"></i> Create Team
                </button>
            </form>
        </div>

        <!-- Teams List -->
        <h2 style="margin:30px 0 20px 0;"><i class="fas fa-list"></i> Your Teams</h2>
        
        <?php if (count($teams) === 0): ?>
        <div style="text-align:center;padding:60px 20px;color:#999;">
            <i class="fas fa-users" style="font-size:4rem;margin-bottom:20px;opacity:0.3;"></i>
            <h3>No teams yet</h3>
            <p>Create a team above to start collaborating with other developers!</p>
        </div>
        <?php else: ?>
            <?php foreach ($teams as $team): ?>
            <div class="team-card">
                <div class="team-header">
                    <div>
                        <h3 style="margin:0;color:#333;display:inline-block;margin-right:10px;">
                            <i class="fas fa-users"></i> <?= htmlspecialchars($team['team_name']) ?>
                        </h3>
                        <span class="role-badge role-<?= $team['memberrole'] ?>">
                            <?= strtoupper($team['memberrole']) ?>
                        </span>
                    </div>
                    <?php if (in_array($team['memberrole'], ['owner', 'admin'])): ?>
                    <button onclick="document.getElementById('add-member-<?= $team['id'] ?>').style.display='block'"
                            style="background:#9B59FF;color:white;border:none;padding:8px 20px;border-radius:20px;cursor:pointer;font-size:0.9rem;">
                        <i class="fas fa-user-plus"></i> Add Member
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if ($team['teamdescription']): ?>
                <p style="color:#666;margin:10px 0;"><?= htmlspecialchars($team['teamdescription']) ?></p>
                <?php endif; ?>
                
                <div class="team-stats">
                    <div class="team-stat">
                        <i class="fas fa-users"></i>
                        <span><?= $team['member_count'] ?> Member<?= $team['member_count'] != 1 ? 's' : '' ?></span>
                    </div>
                    <div class="team-stat">
                        <i class="fas fa-gamepad"></i>
                        <span><?= $team['project_count'] ?> Project<?= $team['project_count'] != 1 ? 's' : '' ?></span>
                    </div>
                    <div class="team-stat">
                        <i class="fas fa-calendar"></i>
                        <span>Created <?= date('M d, Y', strtotime($team['created_at'])) ?></span>
                    </div>
                </div>

                <!-- Add Member Form (Hidden) -->
                <div id="add-member-<?= $team['id'] ?>" style="display:none;margin-top:20px;padding-top:20px;border-top:1px solid #eee;">
                    <form method="POST" style="display:flex;gap:10px;align-items:flex-end;">
                        <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                        <div style="flex:1;">
                            <label style="display:block;margin-bottom:5px;font-size:0.9rem;color:#666;">Developer Email</label>
                            <input type="email" name="member_email" required
                                   style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        </div>
                        <button type="submit" name="add_member"
                                style="background:#28a745;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;">
                            <i class="fas fa-plus"></i> Add
                        </button>
                        <button type="button" onclick="document.getElementById('add-member-<?= $team['id'] ?>').style.display='none'"
                                style="background:#6c757d;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
