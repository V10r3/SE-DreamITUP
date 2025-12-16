<?php
session_start();
require "../config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user = $_SESSION['user'];
$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $project_id = (int)($_POST['project_id'] ?? 0);

        if (!$project_id) {
            $response['message'] = 'Invalid project ID';
            echo json_encode($response);
            exit;
        }

        if ($action === 'add') {
            // Add to watchlist
            $stmt = $pdo->prepare("INSERT IGNORE INTO watchlist (user_id, project_id) VALUES (?, ?)");
            $stmt->execute([$user['id'], $project_id]);
            
            $response['success'] = true;
            $response['message'] = 'Added to watchlist';
            $response['action'] = 'added';
            
        } elseif ($action === 'remove') {
            // Remove from watchlist
            $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND project_id = ?");
            $stmt->execute([$user['id'], $project_id]);
            
            $response['success'] = true;
            $response['message'] = 'Removed from watchlist';
            $response['action'] = 'removed';
            
        } elseif ($action === 'check') {
            // Check if in watchlist
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ? AND project_id = ?");
            $stmt->execute([$user['id'], $project_id]);
            $count = $stmt->fetchColumn();
            
            $response['success'] = true;
            $response['in_watchlist'] = $count > 0;
        } else {
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred';
    error_log("Watchlist error: " . $e->getMessage());
}

echo json_encode($response);
