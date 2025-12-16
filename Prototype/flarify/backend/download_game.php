<?php
/**
 * Game Download Handler
 * 
 * Handles file downloads with authentication and download counter.
 * Note: Does not use init.php to avoid JSON header conflict.
 */

// Start session and load database config
session_start();
require_once __DIR__ . "/../config.php";

// Debug logging
error_log("Download attempt - Session exists: " . (isset($_SESSION['user']) ? 'yes' : 'no'));
error_log("Download attempt - Project ID: " . ($_GET['id'] ?? 'none'));

if (!isset($_GET['id'])) {
    error_log("Download failed: No project ID provided");
    die("Error: No game specified");
}

$project_id = (int)$_GET['id'];

if (!isset($_SESSION['user'])) {
    error_log("Download failed: User not logged in");
    header("Location: ../index.php?page=login");
    exit;
}

$user_id = $_SESSION['user']['id'];

try {
    // Get the project details first
    $stmt = $pdo->prepare("SELECT file_path, title, developer_id FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        error_log("Download failed: Project not found - ID: $project_id");
        die("Error: Game not found");
    }
    
    // Handle both relative and absolute paths
    $file_path = $project['file_path'];
    
    error_log("Checking file existence - Original path from DB: " . $file_path);
    
    // Build absolute path from project root
    $root_dir = dirname(__DIR__); // Go up from backend/ to project root
    
    // Remove leading ../ if present
    $file_path = str_replace('../', '', $file_path);
    
    // Build full path
    $full_path = $root_dir . '/' . $file_path;
    
    error_log("Checking file existence - Resolved path: " . $full_path);
    
    if (!file_exists($full_path)) {
        error_log("Download failed: File not found - Path: " . $full_path);
        die("Error: Game file not found on server. Path: " . htmlspecialchars($full_path));
    }
    
    $file_path = $full_path;
    
    // Only increment downloads if user is NOT the developer
    if ($project['developer_id'] != $user_id) {
        $stmt = $pdo->prepare("UPDATE projects SET downloads = downloads + 1 WHERE id = ?");
        $stmt->execute([$project_id]);
        error_log("Download counter incremented for project $project_id by user $user_id");
    } else {
        error_log("Download by developer - counter not incremented for project $project_id");
    }
    
    // Force download
    error_log("Starting file download: " . $file_path);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache');
    readfile($file_path);
    exit;
    
} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    die("Error: " . htmlspecialchars($e->getMessage()));
}

