<?php
require "config.php";

try {
    // Create teams table
    $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_name VARCHAR(100) NOT NULL UNIQUE,
        teamdescription TEXT,
        owner_id INT NOT NULL,
        avatar_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "✓ Teams table created/verified\n";
    
    // Create team_members table
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_id INT NOT NULL,
        user_id INT NOT NULL,
        memberrole ENUM('owner', 'admin', 'member') DEFAULT 'member',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_member (team_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "✓ Team_members table created/verified\n";
    
    // Check if projects table has team columns
    $result = $pdo->query("SHOW COLUMNS FROM projects LIKE 'team_id'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE projects ADD COLUMN team_id INT DEFAULT NULL AFTER developer_id");
        $pdo->exec("ALTER TABLE projects ADD COLUMN credit_type ENUM('developer', 'team', 'both') DEFAULT 'developer' AFTER team_id");
        $pdo->exec("ALTER TABLE projects ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL");
        echo "✓ Added team_id and credit_type columns to projects table\n";
    } else {
        echo "✓ Projects table already has team columns\n";
    }
    
    echo "\n✅ Team functionality setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
