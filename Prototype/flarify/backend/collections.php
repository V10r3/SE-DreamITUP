<?php
/**
 * Collections API
 * 
 * Handles CRUD operations for user game collections.
 * Users can organize their games into custom collections.
 * 
 * Available Actions:
 * - create: Create a new collection
 * - list: Get all user's collections with game counts
 * - get: Get specific collection details with games
 * - add_game: Add a game to a collection
 * - remove_game: Remove a game from a collection
 * - delete: Delete a collection
 * - update: Update collection name/description
 * 
 * @package Flarify
 * @author Flarify Team
 */

require_once 'init.php';
requireAuth();

// Get authenticated user ID
$user_id = $_SESSION['user']['id'];

// Determine action from POST or GET parameters
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // Action: Create new collection
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validate collection name
        if (empty($name)) {
            throw new Exception('Collection name is required');
        }
        
        $stmt = $pdo->prepare("INSERT INTO collections (user_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $description]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Collection created successfully',
            'collection_id' => $pdo->lastInsertId()
        ]);
        
    } elseif ($action === 'list') {
        // Get user's collections with game counts
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(ci.id) as game_count 
            FROM collections c 
            LEFT JOIN collection_items ci ON c.id = ci.collection_id 
            WHERE c.user_id = ? 
            GROUP BY c.id 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $collections = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'collections' => $collections
        ]);
        
    } elseif ($action === 'get') {
        // Get collection details with games
        $collection_id = $_GET['id'] ?? 0;
        
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$collection_id, $user_id]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            throw new Exception('Collection not found');
        }
        
        // Get games in collection
        $stmt = $pdo->prepare("
            SELECT p.*, u.name as dev_name, ci.added_at 
            FROM collection_items ci 
            JOIN projects p ON ci.project_id = p.id 
            JOIN users u ON p.developer_id = u.id 
            WHERE ci.collection_id = ? 
            ORDER BY ci.added_at DESC
        ");
        $stmt->execute([$collection_id]);
        $games = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'collection' => $collection,
            'games' => $games
        ]);
        
    } elseif ($action === 'add_game') {
        // Add game to collection
        $collection_id = $_POST['collection_id'] ?? 0;
        $project_id = $_POST['project_id'] ?? 0;
        
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$collection_id, $user_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Collection not found');
        }
        
        // Add game
        $stmt = $pdo->prepare("INSERT IGNORE INTO collection_items (collection_id, project_id) VALUES (?, ?)");
        $stmt->execute([$collection_id, $project_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Game added to collection'
        ]);
        
    } elseif ($action === 'remove_game') {
        // Remove game from collection
        $collection_id = $_POST['collection_id'] ?? 0;
        $project_id = $_POST['project_id'] ?? 0;
        
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$collection_id, $user_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Collection not found');
        }
        
        $stmt = $pdo->prepare("DELETE FROM collection_items WHERE collection_id = ? AND project_id = ?");
        $stmt->execute([$collection_id, $project_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Game removed from collection'
        ]);
        
    } elseif ($action === 'delete') {
        // Delete collection
        $collection_id = $_POST['collection_id'] ?? 0;
        
        $stmt = $pdo->prepare("DELETE FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$collection_id, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Collection deleted'
        ]);
        
    } elseif ($action === 'update') {
        // Update collection
        $collection_id = $_POST['collection_id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            throw new Exception('Collection name is required');
        }
        
        $stmt = $pdo->prepare("UPDATE collections SET name = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $description, $collection_id, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Collection updated'
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
