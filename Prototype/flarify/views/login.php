<?php if (!empty($_SESSION['flash'])): ?>
  <div class="notice"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="grid cols-2">
  <div class="panel">
    <h2>Log in</h2>
    <p class="muted">Welcome to Flarify</p>
    <p>Build, Innovate, and manage your games with ease. #GameItUp</p>
    <form method="POST" action="backend/auth.php?action=login">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <label class="inline"><input type="checkbox" name="remember"> Remember Me</label>
      <div class="actions">
        <button type="submit">Log in</button>
        <a class="btn secondary" href="index.php?page=signup">Sign up</a>
      </div>
      <a class="muted" href="#">Forgot Password?</a>
    </form>
  </div>
  <div class="panel">
    <h2>Welcome to Flarify</h2>
    <p>#GameItUp — showcase your projects, connect with studios, and level up.</p>
  </div>
</div>