<?php include 'includes/config.php'; if (!isset($_SESSION['user_id'])) header("Location: index.php"); 
// Fetch messages (simplified, assume chatting with one user)
$messages = $conn->query("SELECT * FROM messages WHERE from_id = {$_SESSION['user_id']} OR to_id = {$_SESSION['user_id']} ORDER BY timestamp");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Messages</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header><!-- Same --></header>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <h1>Messages</h1>
    <div class="chat">
        <?php while ($msg = $messages->fetch_assoc()): ?>
            <div class="message <?php echo $msg['from_id'] == $_SESSION['user_id'] ? 'my-message' : 'other-message'; ?>">
                <?php echo $msg['message']; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <form action="message-process.php" method="POST">
        <input type="text" name="message" placeholder="Type a message..." required>
        <button type="submit" style="background: #A020F0;">Send</button>
    </form>
</div>
<script src="js/script.js"></script>
</body>
</html>