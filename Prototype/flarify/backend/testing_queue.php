<?php
/**
 * Testing Queue API
 * 
 * Manages the testing queue for testers to track game testing workflow.
 * Restricted to users with 'tester' role only.
 * 
 * Available Actions:
 * - add: Add a game to testing queue
 * - list: Get tester's queue (filterable by status)
 * - update_status: Change testing status (pending/in_progress/completed)
 * - update_notes: Update testing notes
 * - remove: Remove game from queue
 * - check: Check if game is in queue
 * 
 * Status Flow: pending → in_progress → completed
 * 
 * @package Flarify
 * @author Flarify Team
 */

require_once 'init.php';
requireRole('tester'); // Only testers can access this API

// Get authenticated tester ID
$user_id = $_SESSION['user']['id'];

// Determine action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // Action: Add game to testing queue
    if ($action === 'add') {
        $project_id = $_POST['project_id'] ?? 0;
        $notes = trim($_POST['notes'] ?? '');
        
        // INSERT IGNORE prevents duplicate entries (unique constraint on tester_id + project_id)
        $stmt = $pdo->prepare("INSERT IGNORE INTO testing_queue (tester_id, project_id, notes) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $project_id, $notes]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Game added to testing queue'
        ]);
        
    } elseif ($action === 'list') {
        // Get tester's queue
        $status = $_GET['status'] ?? 'all';
        
        $sql = "
            SELECT tq.id as id, tq.tester_id, tq.project_id, tq.status, tq.notes, tq.added_at, tq.completed_at,
                   p.title, p.description, p.banner_path, p.icon_path, p.screenshots, p.developer_id,
                   u.name as dev_name 
            FROM testing_queue tq 
            JOIN projects p ON tq.project_id = p.id 
            JOIN users u ON p.developer_id = u.id 
            WHERE tq.tester_id = ?
        ";
        
        if ($status !== 'all') {
            $sql .= " AND tq.status = ?";
        }
        
        $sql .= " ORDER BY tq.added_at DESC";
        
        $stmt = $pdo->prepare($sql);
        if ($status !== 'all') {
            $stmt->execute([$user_id, $status]);
        } else {
            $stmt->execute([$user_id]);
        }
        
        $queue = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'queue' => $queue
        ]);
        
    } elseif ($action === 'update_status') {
        // Update testing status
        $queue_id = $_POST['queue_id'] ?? 0;
        $status = $_POST['status'] ?? 'pending';
        
        if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
            throw new Exception('Invalid status');
        }
        
        $completed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("
            UPDATE testing_queue 
            SET status = ?, completed_at = ? 
            WHERE id = ? AND tester_id = ?
        ");
        $stmt->execute([$status, $completed_at, $queue_id, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Status updated'
        ]);
        
    } elseif ($action === 'update_notes') {
        // Update testing notes
        $queue_id = $_POST['queue_id'] ?? 0;
        $notes = trim($_POST['notes'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE testing_queue SET notes = ? WHERE id = ? AND tester_id = ?");
        $stmt->execute([$notes, $queue_id, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notes updated'
        ]);
        
    } elseif ($action === 'remove') {
        // Remove from queue
        $queue_id = $_POST['queue_id'] ?? 0;
        
        if (!$queue_id) {
            throw new Exception('No queue ID provided');
        }
        
        // First check if the item exists
        $checkStmt = $pdo->prepare("SELECT * FROM testing_queue WHERE id = ?");
        $checkStmt->execute([$queue_id]);
        $item = $checkStmt->fetch();
        
        if (!$item) {
            throw new Exception("Queue item ID $queue_id not found in database");
        }
        
        if ($item['tester_id'] != $user_id) {
            throw new Exception("This item belongs to another user (tester_id: {$item['tester_id']}, your id: $user_id)");
        }
        
        $stmt = $pdo->prepare("DELETE FROM testing_queue WHERE id = ?");
        $stmt->execute([$queue_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Removed from testing queue'
        ]);
        
    } elseif ($action === 'check') {
        // Check if game is in queue
        $project_id = $_GET['project_id'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT * FROM testing_queue WHERE tester_id = ? AND project_id = ?");
        $stmt->execute([$user_id, $project_id]);
        $item = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'in_queue' => $item ? true : false,
            'status' => $item ? $item['status'] : null
        ]);
        
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
