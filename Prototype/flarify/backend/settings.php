<?php
/**
 * User Settings API
 * 
 * Handles user preference updates including theme selection.
 * Currently supports theme customization (light/dark/auto).
 * 
 * Available Actions:
 * - update_theme: Change user's display theme preference
 * 
 * Theme Options:
 * - light: Light mode (default)
 * - dark: Dark mode
 * - auto: Follows system preference
 * 
 * @package Flarify
 * @author Flarify Team
 */

require_once 'init.php';
requireAuth();

// Get authenticated user ID
$user_id = $_SESSION['user']['id'];

// Get requested action
$action = $_POST['action'] ?? '';

try {
    // Action: Update theme preference
    if ($action === 'update_theme') {
        $theme = $_POST['theme'] ?? 'light';
        
        // Validate theme value (must be one of: light, dark, auto)
        if (!in_array($theme, ['light', 'dark', 'auto'])) {
            throw new Exception('Invalid theme value');
        }
        
        // Update user's theme preference
        $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $user_id]);
        
        // Update session
        $_SESSION['user']['theme'] = $theme;
        
        echo json_encode([
            'success' => true,
            'message' => 'Theme updated successfully',
            'theme' => $theme
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
