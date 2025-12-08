<?php $user = $_SESSION['user'] ?? null; ?>
<nav class="topbar">
  <div class="brand">Flarify <span>#GameItUp</span></div>
  <div class="navlinks">
    <a href="index.php?page=dashboard">HOME</a>
    <a href="#">ABOUT US</a>
    <a href="index.php?page=messages">INBOX</a>
    <a href="index.php?page=upload">GAMES</a>
    <?php if ($user): ?>
      <a href="backend/auth.php?action=logout">LOG OUT</a>
      <span class="user-pill"><?= htmlspecialchars($user['name']) ?></span>
    <?php else: ?>
      <a href="index.php?page=login">LOG IN</a>
      <a href="index.php?page=signup">SIGN UP</a>
    <?php endif; ?>
  </div>
</nav>
<div class="container">