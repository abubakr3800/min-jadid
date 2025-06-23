<?php
echo "Starting database test...\n";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Error reporting enabled\n";

try {
    echo "Loading Database.php...\n";
    require_once 'includes/Database.php';
    echo "Database.php loaded successfully\n";
    
    echo "Creating Database instance...\n";
    $db = new Database();
    echo "Database initialized successfully\n";
    
    echo "Testing getUserByEmail...\n";
    $user = $db->getUserByEmail('ahmed.mo.abubakr@gmail.com');
    if (!$user) {
        die("User not found\n");
    }

    $userId = $user['id'];
    echo "Found user: {$user['firstName']} {$user['lastName']} (ID: $userId)\n";
    
    // Test adding one article
    $postData = [
        'title' => 'قوة الإرادة والعزيمة',
        'content' => 'الإرادة والعزيمة هما المحرك الأساسي لتحقيق النجاح في الحياة.',
        'categoryId' => '2',
        'tags' => ['قوة الإرادة', 'العزيمة', 'تحقيق الأهداف'],
        'userId' => $userId,
        'status' => 'published'
    ];

    echo "Attempting to add test article...\n";
    $newPost = $db->addPost($postData);

    if ($newPost) {
        echo "Successfully added test article\n";
    } else {
        echo "Failed to add test article\n";
    }

    echo "Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "Script completed!\n";
?> 