<?php
/**
 * Database Structure Checker
 * Diagnoses and fixes the users table structure
 */

require 'config.php';

echo "<h2>Checking Database Structure...</h2>";

// Check if users table exists and get its structure
$result = $pdo->query("DESCRIBE users");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Current 'users' table structure:</h3>";
echo "<pre>";
foreach ($columns as $col) {
    echo "Column: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']}\n";
}
echo "</pre>";

// Check if password column exists
$hasPassword = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'password') {
        $hasPassword = true;
        break;
    }
}

if (!$hasPassword) {
    echo "<p style='color: red;'><strong>ERROR: 'password' column is missing!</strong></p>";
    echo "<p>Attempting to fix...</p>";
    
    try {
        // Add password column
        $pdo->exec("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL COMMENT 'Argon2ID hashed password' AFTER email");
        echo "<p style='color: green;'><strong>SUCCESS: Added 'password' column to users table!</strong></p>";
        
        // Show updated structure
        $result = $pdo->query("DESCRIBE users");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Updated 'users' table structure:</h3>";
        echo "<pre>";
        foreach ($columns as $col) {
            echo "Column: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']}\n";
        }
        echo "</pre>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Failed to add column: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: green;'><strong>SUCCESS: 'password' column exists!</strong></p>";
}

echo "<hr><p><a href='index.php?page=signup'>Go to Signup Page</a></p>";
?>
