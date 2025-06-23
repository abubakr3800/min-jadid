<?php
// Script to make ahmed.mo.abubakr@gmail.com an admin account

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting admin script...\n";

require_once 'includes/Database.php';

$db = new Database();
$adminEmail = 'ahmed.mo.abubakr@gmail.com';

echo "Making $adminEmail an admin account...\n";

// Check if user exists
$user = $db->getUserByEmail($adminEmail);
if (!$user) {
    echo "Error: User with email $adminEmail not found!\n";
    exit(1);
}

echo "Found user: " . $user['firstName'] . " " . $user['lastName'] . "\n";

// Make user admin
$result = $db->makeAdmin($adminEmail);

if ($result) {
    echo "✅ Successfully made $adminEmail an admin account!\n";
    echo "Admin permissions granted:\n";
    echo "- Delete any article (by any author)\n";
    echo "- View all users table\n";
    echo "- Manage users\n";
    echo "- Manage categories\n";
    echo "- View analytics\n";
    
    // Verify admin status
    if ($db->isAdmin($adminEmail)) {
        echo "✅ Admin status verified successfully!\n";
    } else {
        echo "❌ Warning: Admin status verification failed!\n";
    }
} else {
    echo "❌ Error: Failed to make user admin!\n";
    exit(1);
}

echo "\nYou can now:\n";
echo "1. Delete any article from any author\n";
echo "2. View the users table in the admin panel\n";
echo "3. Access admin-only features\n";
echo "\nLog in with your account to access admin features.\n";
?> 