<?php
/**
 * Backend Initialization File
 * 
 * This file serves as a common initialization point for all backend API scripts.
 * It handles session management, database connection, and provides authentication helpers.
 * 
 * Usage: require_once 'init.php'; at the top of any backend API file
 * 
 * @package Flarify
 * @author Flarify Team
 */

// Start session for user authentication
session_start();

// Load database configuration
require_once __DIR__ . "/../config.php";

// Set JSON response header for all API endpoints
header('Content-Type: application/json');

/**
 * Verify user authentication
 * 
 * Checks if a user is logged in by verifying the session.
 * Returns 401 Unauthorized if not authenticated.
 * 
 * @return void Exits script if not authenticated
 */
function requireAuth() {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
}

/**
 * Verify user has specific role
 * 
 * Checks if authenticated user has the required role (developer, tester, investor).
 * Returns 403 Forbidden if user doesn't have the required role.
 * 
 * @param string $role Required role (developer|tester|investor)
 * @return void Exits script if role doesn't match
 */
function requireRole($role) {
    requireAuth();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
}
?>
