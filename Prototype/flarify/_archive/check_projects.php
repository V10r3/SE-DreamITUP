<?php
require 'config.php';

echo "=== Database Check ===\n\n";

// Check projects
$stmt = $pdo->query('SELECT COUNT(*) as count FROM projects');
$result = $stmt->fetch();
echo "Total projects: " . $result['count'] . "\n\n";

// Show all projects
$stmt = $pdo->query('SELECT id, title, developer_id FROM projects LIMIT 10');
$projects = $stmt->fetchAll();

if (count($projects) > 0) {
    echo "Sample projects:\n";
    foreach ($projects as $p) {
        echo "  - ID: {$p['id']}, Title: {$p['title']}, Developer ID: {$p['developer_id']}\n";
    }
} else {
    echo "No projects found in database.\n";
}
