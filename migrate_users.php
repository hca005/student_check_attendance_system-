<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $queries = [
        "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN gender ENUM('Male', 'Female', 'Other') DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN date_of_birth DATE DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN id_card_number VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN hometown VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN qualification VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN class_name VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN academic_year VARCHAR(50) DEFAULT NULL"
    ];

    foreach ($queries as $query) {
        try {
            $db->exec($query);
            echo "Successfully executed: $query\n";
        } catch (PDOException $e) {
            // Ignore duplicate column errors
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "Column already exists, skipping: $query\n";
            } else {
                echo "Error executing $query: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Migration completed.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
