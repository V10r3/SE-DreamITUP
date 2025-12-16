<?php
session_start();
require "../config.php";

// Clear any output buffers and set JSON header
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'investor') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$user = $_SESSION['user'];
$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'invest') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $equity = isset($_POST['equity']) ? (float)$_POST['equity'] : null;
            $notes = $_POST['notes'] ?? '';

            if (!$project_id || $amount <= 0) {
                $response['message'] = 'Invalid investment details';
                echo json_encode($response);
                exit;
            }

            // Check if project exists
            $stmt = $pdo->prepare("SELECT id, title, developer_id FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch();

            if (!$project) {
                $response['message'] = 'Project not found';
                echo json_encode($response);
                exit;
            }

            // Insert investment
            $stmt = $pdo->prepare("
                INSERT INTO investments (investor_id, project_id, amount, equity_percentage, notes, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$user['id'], $project_id, $amount, $equity, $notes]);

            // Create notification for developer
            require_once "notification_helper.php";
            createNotification(
                $pdo,
                $project['developer_id'],
                'investment',
                'New Investment Received!',
                htmlspecialchars($user['name']) . ' invested $' . number_format($amount, 2) . ' in your game "' . htmlspecialchars($project['title']) . '"',
                'index.php?page=game&id=' . $project_id
            );

            $response['success'] = true;
            $response['message'] = 'Investment recorded successfully';
            $response['investment_id'] = $pdo->lastInsertId();

        } elseif ($action === 'update_status') {
            $investment_id = (int)($_POST['investment_id'] ?? 0);
            $status = $_POST['status'] ?? '';

            $valid_statuses = ['pending', 'active', 'completed', 'cancelled'];
            if (!in_array($status, $valid_statuses)) {
                $response['message'] = 'Invalid status';
                echo json_encode($response);
                exit;
            }

            // Verify ownership
            $stmt = $pdo->prepare("SELECT id FROM investments WHERE id = ? AND investor_id = ?");
            $stmt->execute([$investment_id, $user['id']]);
            if (!$stmt->fetch()) {
                $response['message'] = 'Investment not found';
                echo json_encode($response);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE investments SET status = ? WHERE id = ?");
            $stmt->execute([$status, $investment_id]);

            $response['success'] = true;
            $response['message'] = 'Status updated';

        } else {
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Investment error: " . $e->getMessage());
}

echo json_encode($response);
