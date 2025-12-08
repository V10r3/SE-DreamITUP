<?php
if (!isset($_SESSION['user'])) { header("Location:index.php?page=login"); exit; }
require "config.php";
$user = $_SESSION['user'];

$stmtMine = $pdo->prepare("SELECT * FROM projects WHERE developer_id=? ORDER BY id DESC");
$stmtMine->execute([$user['id']]);
$myProjects = $stmtMine->fetchAll();

$stmtOthers = $pdo->query("SELECT p.*, u.name AS dev_name FROM projects p JOIN users u ON p.developer_id=u.id ORDER BY p.id DESC LIMIT 9");
$otherProjects = $stmtOthers->fetchAll();
?>

<div class="grid cols-4">
  <div class="panel"><h3>Explore</h3><p class="muted">Discover games crafted by talented developers.</p></div>
  <div class="panel"><h3>Library</h3><p class="muted">Your purchased or bookmarked items.</p></div>
  <div class="panel"><h3>Collections</h3><p class="muted">Curate and share favorite projects.</p></div>
  <div class="panel"><h3>Messages</h3><p class="muted">Collaborate and connect.</p><a class="btn" href="index.php?page=messages">Open Inbox</a></div>
</div>

<div class="panel" style="margin-top:16px;">
  <h2>Games Created by Other Developers</h2>
  <div class="grid cols-3" style="margin-top:12px;">
    <?php if (!$otherProjects): ?>
      <p class="muted">No games found.</p>
    <?php else: ?>
      <?php foreach ($otherProjects as $p) { $GLOBALS['p'] = $p; include "partials/project_card.php"; } ?>
    <?php endif; ?>
  </div>
</div>

<div class="panel" style="margin-top:16px;">
  <div class="actions">
    <h2 style="flex:1;">Created Projects</h2>
    <a class="btn accent" href="index.php?page=upload">+ Upload new</a>
  </div>
  <div class="grid cols-3" style="margin-top:12px;">
    <?php if (!$myProjects): ?>
      <p class="muted">No projects yet. Upload your first!</p>
    <?php else: ?>
      <?php foreach ($myProjects as $p) { $GLOBALS['p'] = $p; include "partials/project_card.php"; } ?>
    <?php endif; ?>
  </div>
</div>