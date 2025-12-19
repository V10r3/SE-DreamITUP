<?php
require "../config.php";
require "notification_helper.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sender_id = $_SESSION['user']['id'];
        $receiver_id = (int)$_POST['receiver_id'];
        $content = trim($_POST['content']);
        
        if (empty($content)) {
            error_log("Message send failed: empty content");
            header("Location:../index.php?page=messages&error=empty");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,content) VALUES (?,?,?)");
        $stmt->execute([$sender_id,$receiver_id,$content]);
        
        // Create notification for receiver
        $sender_name = $_SESSION['user']['username'];
        $preview = strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
        createNotification(
            $pdo,
            $receiver_id,
            'message',
            'New Message from ' . $sender_name,
            $preview,
            'index.php?page=messages&chat=' . $sender_id
        );
        
        error_log("Message sent successfully from user " . $sender_id . " to " . $receiver_id);
        
        // Redirect back to where they came from or to messages page
        $redirect = $_POST['redirect'] ?? 'messages';
        header("Location:../index.php?page=" . $redirect . "&success=sent");
    } catch (Exception $e) {
        error_log("Message send error: " . $e->getMessage());
        header("Location:../index.php?page=messages&error=failed");
    }
}
?>