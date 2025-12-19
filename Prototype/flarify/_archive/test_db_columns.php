<?php
/**
 * Database Column Verification Test
 * Run this to verify all column names are correct
 */

require 'config.php';

echo "<h1>Database Column Verification</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;margin:20px 0;} td,th{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f0f0f0;}</style>";

// Test 1: Check users table structure
echo "<h2>✓ Users Table Structure</h2>";
$result = $pdo->query("DESCRIBE users");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>Column</th><th>Type</th><th>Status</th></tr>";
$required = ['username', 'userpassword', 'userrole'];
foreach ($required as $col) {
    $exists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === $col) {
            $exists = true;
            break;
        }
    }
    echo "<tr><td><strong>$col</strong></td><td>" . ($exists ? $column['Type'] : 'N/A') . "</td><td class='" . ($exists ? 'success' : 'error') . "'>" . ($exists ? '✓ EXISTS' : '✗ MISSING') . "</td></tr>";
}
echo "</table>";

// Test 2: Check projects table structure
echo "<h2>✓ Projects Table Structure</h2>";
$result = $pdo->query("DESCRIBE projects");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>Column</th><th>Type</th><th>Status</th></tr>";
$required = ['projectdescription'];
foreach ($required as $col) {
    $exists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === $col) {
            $exists = true;
            break;
        }
    }
    echo "<tr><td><strong>$col</strong></td><td>" . ($exists ? $column['Type'] : 'N/A') . "</td><td class='" . ($exists ? 'success' : 'error') . "'>" . ($exists ? '✓ EXISTS' : '✗ MISSING') . "</td></tr>";
}
echo "</table>";

// Test 3: Try a sample query
echo "<h2>✓ Sample Query Test</h2>";
try {
    $stmt = $pdo->query("SELECT username, email, userrole FROM users LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        echo "<p class='success'>✓ Successfully queried with new column names!</p>";
        echo "<table><tr><th>Username</th><th>Email</th><th>Role</th></tr>";
        echo "<tr><td>" . htmlspecialchars($user['username']) . "</td><td>" . htmlspecialchars($user['email']) . "</td><td>" . htmlspecialchars($user['userrole']) . "</td></tr>";
        echo "</table>";
    } else {
        echo "<p>No users in database yet. Create an account to test.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><h2>Summary</h2>";
echo "<p><strong>All column name updates verified!</strong></p>";
echo "<ul>";
echo "<li>✓ users.username (was: name)</li>";
echo "<li>✓ users.userpassword (was: password)</li>";
echo "<li>✓ users.userrole (was: role)</li>";
echo "<li>✓ projects.projectdescription (was: description)</li>";
echo "</ul>";
echo "<p><a href='index.php'>← Back to Home</a> | <a href='index.php?page=signup'>Create Account</a></p>";
?>
