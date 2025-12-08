<?php if (!empty($_SESSION['flash'])): ?>
  <div class="notice"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="grid cols-2">
  <div class="panel">
    <h2>Sign up</h2>
    <form method="POST" action="backend/auth.php?action=signup">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <div class="input-row">
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
      </div>

      <hr class="sep">
      <h3>A Developers Journey — Choose how you'll participate</h3>
      <div class="helpers">
        <label class="inline"><input type="radio" name="role" value="developer" checked> Developer</label>
        <label class="inline"><input type="radio" name="role" value="investor"> Investor</label>
        <label class="inline"><input type="radio" name="role" value="tester"> Tester</label>
        <span class="muted">Selected: <strong id="role-preview">Developer</strong></span>
      </div>

      <div class="actions">
        <button type="submit">Create Account</button>
        <a class="btn secondary" href="index.php?page=login">Log in</a>
      </div>
    </form>
  </div>

  <div class="panel">
    <h2>Role overview</h2>

    <div class="role-slide" data-role="developer">
      <h3>Developer</h3>
      <p class="muted">Builds and improves the game and its features.</p>
    </div>
    <div class="role-slide" data-role="investor" style="display:none;">
      <h3>Investor</h3>
      <p class="muted">Provides funding and support to help the platform grow.</p>
    </div>
    <div class="role-slide" data-role="tester" style="display:none;">
      <h3>Tester</h3>
      <p class="muted">Tests games and features to make sure everything works properly.</p>
    </div>
  </div>
</div>