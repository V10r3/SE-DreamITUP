<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config.php';

echo "=== Dashboard Debug ===\n\n";

// Check if teams table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'teams'");
    $teamsExists = $stmt->rowCount() > 0;
    echo "Teams table exists: " . ($teamsExists ? "YES" : "NO") . "\n";
} catch (PDOException $e) {
    echo "Error checking teams table: " . $e->getMessage() . "\n";
}

// Check if team columns exist in projects
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'team_id'");
    $teamIdExists = $stmt->rowCount() > 0;
    echo "Projects.team_id exists: " . ($teamIdExists ? "YES" : "NO") . "\n";
} catch (PDOException $e) {
    echo "Error checking projects columns: " . $e->getMessage() . "\n";
}

echo "\n";

// Try the dashboard query
try {
    $stmt = $pdo->query("
        SELECT p.*, u.username AS dev_name, t.team_name 
        FROM projects p 
        LEFT JOIN users u ON p.developer_id = u.id
        LEFT JOIN teams t ON p.team_id = t.id
        ORDER BY p.created_at DESC
    ");
    $projects = $stmt->fetchAll();
    echo "Query executed successfully!\n";
    echo "Projects found: " . count($projects) . "\n\n";
    
    if (count($projects) > 0) {
        echo "First project:\n";
        print_r($projects[0]);
    }
} catch (PDOException $e) {
    echo "ERROR executing query: " . $e->getMessage() . "\n";
}
