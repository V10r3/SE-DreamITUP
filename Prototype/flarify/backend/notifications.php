<?php
session_start();
require "../config.php";

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? 'get';

if ($action === 'get') {
    // Get unread notifications
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    
} elseif ($action === 'mark_read' && isset($_POST['notification_id'])) {
    // Mark notification as read
    $notification_id = (int)$_POST['notification_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $user_id]);
    
    echo json_encode(['success' => true]);
    
} elseif ($action === 'mark_all_read') {
    // Mark all notifications as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true]);
    
} elseif ($action === 'delete' && isset($_POST['notification_id'])) {
    // Delete notification
    $notification_id = (int)$_POST['notification_id'];
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $user_id]);
    
    echo json_encode(['success' => true]);
}
