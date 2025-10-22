<?php
require_once __DIR__ . '/bootstrap.php';

use Angel\IapGroupProject\Database;

echo "Inserting basic reference data...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Insert user roles
    echo "🔧 Inserting user roles...\n";
    $roles = [
        ['client'],
        ['rehomer'], 
        ['admin']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO user_roles (role_name) VALUES (?)");
    foreach ($roles as $role) {
        $stmt->execute($role);
    }
    echo "✅ User roles inserted!\n";
    
    // Insert user genders
    echo "🔧 Inserting user genders...\n";
    $genders = [
        ['Male'],
        ['Female'],
        ['Other']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO user_gender (gender_name) VALUES (?)");
    foreach ($genders as $gender) {
        $stmt->execute($gender);
    }
    echo "✅ User genders inserted!\n";
    
    // Verify the data
    $stmt = $db->query("SELECT * FROM user_roles");
    $roles = $stmt->fetchAll();
    echo "\n📊 User roles in database:\n";
    foreach ($roles as $role) {
        echo "   - ID: {$role['role_id']}, Name: {$role['role_name']}\n";
    }
    
    $stmt = $db->query("SELECT * FROM user_gender");
    $genders = $stmt->fetchAll();
    echo "\n📊 User genders in database:\n";
    foreach ($genders as $gender) {
        echo "   - ID: {$gender['gender_id']}, Name: {$gender['gender_name']}\n";
    }
    
    echo "\n✅ Reference data setup complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>