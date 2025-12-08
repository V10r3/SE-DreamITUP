<?php // expects $p (project row) ?>
<div class="card">
  <div class="thumb"></div>
  <div class="body">
    <div class="row">
      <div class="title"><?= htmlspecialchars($p['title']) ?></div>
      <?php if ($p['preorder']): ?><span class="badge">Pre-order</span><?php endif; ?>
      <?php if ($p['demo_flag']): ?><span class="badge accent">Free demo</span><?php endif; ?>
      <?php if ($p['hidden']): ?><span class="badge danger">Hidden</span><?php endif; ?>
    </div>
    <div class="meta">
      $<?= number_format((float)$p['price'], 2) ?> · #Fantasy #PixelArt
    </div>
    <p class="muted"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
    <div class="actions">
      <?php if (!empty($p['file_path']) && !$p['hidden']): ?>
        <a class="btn secondary" href="<?= htmlspecialchars($p['file_path']) ?>" download>Download</a>
      <?php endif; ?>
      <a class="btn" href="#">Share</a>
    </div>
  </div>
</div>