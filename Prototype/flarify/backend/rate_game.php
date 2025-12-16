<?php
session_start();
require "../config.php";
require "notification_helper.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?page=login");
    exit;
}

$user = $_SESSION['user'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = (int)($_POST['project_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'Invalid rating value.';
        echo json_encode($response);
        exit;
    }
    
    // Check if project exists and get developer_id
    $stmt = $pdo->prepare("SELECT developer_id FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        $response['message'] = 'Game not found.';
        echo json_encode($response);
        exit;
    }
    
    // Prevent developers from rating their own games
    if ($user['role'] === 'developer' && $project['developer_id'] == $user['id']) {
        $response['message'] = 'You cannot rate your own game.';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if user already rated this game
        $stmt = $pdo->prepare("SELECT id, rating FROM project_ratings WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$project_id, $user['id']]);
        $existing_rating = $stmt->fetch();
        
        if ($existing_rating) {
            // Update existing rating
            $stmt = $pdo->prepare("UPDATE project_ratings SET rating = ? WHERE id = ?");
            $stmt->execute([$rating, $existing_rating['id']]);
            $response['message'] = 'Rating updated successfully!';
        } else {
            // Insert new rating
            $stmt = $pdo->prepare("INSERT INTO project_ratings (project_id, user_id, rating) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $user['id'], $rating]);
            $response['message'] = 'Rating submitted successfully!';
        }
        
        // Recalculate average rating
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total 
            FROM project_ratings 
            WHERE project_id = ?
        ");
        $stmt->execute([$project_id]);
        $stats = $stmt->fetch();
        
        // Update project with new average
        $stmt = $pdo->prepare("UPDATE projects SET rating = ?, total_ratings = ? WHERE id = ?");
        $stmt->execute([round($stats['avg_rating'], 2), $stats['total'], $project_id]);
        
        // Get project info for notification
        $stmt = $pdo->prepare("SELECT title, developer_id FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project_info = $stmt->fetch();
        
        // Create notification for developer (only if it's a new rating, not an update)
        if (!$existing_rating && $project_info) {
            createNotification(
                $pdo,
                $project_info['developer_id'],
                'rating',
                'New Rating Received',
                $user['name'] . ' rated your game "' . $project_info['title'] . '" ' . $rating . ' stars!',
                'index.php?page=game&id=' . $project_id
            );
        }
        
        $response['success'] = true;
        $response['new_rating'] = round($stats['avg_rating'], 1);
        $response['total_ratings'] = $stats['total'];
        
    } catch (Exception $e) {
        error_log("Rating error: " . $e->getMessage());
        $response['message'] = 'Failed to submit rating. Please try again.';
    }
}

echo json_encode($response);
