<?php
// Helper function to create notifications
// Include this file in scripts that need to create notifications

function createNotification($pdo, $user_id, $type, $title, $message, $link = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $type, $title, $message, $link]);
        return true;
    } catch (Exception $e) {
        error_log("Notification creation error: " . $e->getMessage());
        return false;
    }
}

// Notification types:
// - 'message' - New message received
// - 'rating' - Someone rated your game
// - 'download' - Your game was downloaded
// - 'upload' - New game uploaded (for followers)
// - 'system' - System announcements
