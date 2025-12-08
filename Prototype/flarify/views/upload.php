<?php if (!isset($_SESSION['user'])) { header("Location:index.php?page=login"); exit; } ?>
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="notice"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="panel">
  <h2>Uploads</h2>
  <p class="muted">File size limit: 1 GB. Contact us if you need more space.</p>

  <form method="POST" action="backend/projects.php" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Project Title" required>
    <textarea name="description" placeholder="Description"></textarea>

    <div class="input-row">
      <input type="number" step="0.01" name="price" placeholder="Price (USD)">
      <label class="inline"><input type="checkbox" name="demo_flag"> This file is a demo and can be downloaded for free</label>
    </div>

    <div class="helpers">
      <label class="inline"><input type="checkbox" name="preorder"> This file is a pre-order placeholder</label>
      <label class="inline"><input type="checkbox" name="hide"> Hide this file and prevent it from being downloaded</label>
    </div>

    <div class="list">
      <div class="item">
        <div>
          <strong>Upload file</strong>
          <div id="file-name" class="muted">No file selected</div>
        </div>
        <div><input type="file" name="file" required></div>
      </div>
    </div>

    <div class="actions" style="margin-top:12px;">
      <button type="submit">Upload</button>
      <a href="index.php?page=dashboard" class="btn secondary">Cancel</a>
    </div>

    <div class="notice" style="margin-top:10px;">
      TIP: Use CLI later for uploads; it only uploads changed parts and can automate patching.
    </div>
  </form>
</div>