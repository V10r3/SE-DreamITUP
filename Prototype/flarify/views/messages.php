<?php
if (!isset($_SESSION['user'])) { header("Location:index.php?page=login"); exit; }
require "config.php";
$user = $_SESSION['user'];

$inboxStmt = $pdo->prepare("SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON m.sender_id=u.id WHERE receiver_id=? ORDER BY timestamp DESC");
$inboxStmt->execute([$user['id']]);
$inbox = $inboxStmt->fetchAll();
?>
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="notice"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="grid cols-2">
  <div class="panel">
    <h2>Messages</h2>
    <div class="chat">
      <?php if (!$inbox): ?>
        <p class="muted">No messages yet.</p>
      <?php else: ?>
        <?php foreach ($inbox as $m): ?>
          <div class="bubble them">
            <strong><?= htmlspecialchars($m['sender_name']) ?>:</strong>
            <div><?= nl2br(htmlspecialchars($m['content'])) ?></div>
            <div class="muted" style="font-size:12px;"><?= htmlspecialchars($m['timestamp']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <h2>Send</h2>
    <form id="message-form" method="POST" action="backend/messages.php">
      <input type="text" name="receiver_id" placeholder="Receiver user ID" required>
      <textarea name="content" placeholder="Message" required></textarea>
      <div class="actions"><button type="submit">Send</button></div>
    </form>
  </div>
</div>