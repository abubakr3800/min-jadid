<?php
require_once 'config/firebase-config.php';

// Initialize Firebase
$firebase = new FirebaseConfig();

echo "Starting migration to Firebase...\n";

// Migrate users
echo "Migrating users...\n";
$users = json_decode(file_get_contents('data/users.json'), true);
foreach ($users as $user) {
    $userData = [
        'id' => $user['id'],
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'email' => $user['email'],
        'linkedin' => $user['linkedin'],
        'createdAt' => time()
    ];
    
    $result = $firebase->dbSet('users/' . $user['id'], $userData);
    if ($result !== null) {
        echo "✓ User {$user['firstName']} {$user['lastName']} migrated\n";
    } else {
        echo "✗ Failed to migrate user {$user['firstName']} {$user['lastName']}\n";
    }
}

// Migrate posts
echo "\nMigrating posts...\n";
$posts = json_decode(file_get_contents('data/posts.json'), true);
foreach ($posts as $post) {
    $postData = [
        'id' => $post['id'],
        'userId' => $post['userId'],
        'title' => $post['title'],
        'content' => $post['content'],
        'category' => $post['categoryId'],
        'tags' => explode(',', $post['tags']),
        'featured' => $post['featured'],
        'rating' => $post['averageRating'],
        'ratingCount' => $post['totalRatings'],
        'views' => $post['views'],
        'likes' => 0,
        'comments' => [],
        'createdAt' => strtotime($post['createdAt']),
        'updatedAt' => strtotime($post['createdAt'])
    ];
    
    $result = $firebase->dbSet('posts/' . $post['id'], $postData);
    if ($result !== null) {
        echo "✓ Post '{$post['title']}' migrated\n";
    } else {
        echo "✗ Failed to migrate post '{$post['title']}'\n";
    }
}

// Migrate categories
echo "\nMigrating categories...\n";
$categories = json_decode(file_get_contents('data/categories.json'), true);
foreach ($categories as $category) {
    $categoryData = [
        'id' => $category['id'],
        'name' => $category['name'],
        'description' => $category['description'],
        'createdAt' => time()
    ];
    
    $result = $firebase->dbSet('categories/' . $category['id'], $categoryData);
    if ($result !== null) {
        echo "✓ Category '{$category['name']}' migrated\n";
    } else {
        echo "✗ Failed to migrate category '{$category['name']}'\n";
    }
}

// Migrate ratings
echo "\nMigrating ratings...\n";
$ratings = json_decode(file_get_contents('data/ratings.json'), true);
foreach ($ratings as $rating) {
    $ratingData = [
        'postId' => $rating['postId'],
        'userId' => $rating['userId'],
        'rating' => $rating['rating'],
        'createdAt' => time()
    ];
    
    $result = $firebase->dbSet('post_ratings/' . $rating['postId'] . '/' . $rating['userId'], $rating['rating']);
    if ($result !== null) {
        echo "✓ Rating for post {$rating['postId']} by user {$rating['userId']} migrated\n";
    } else {
        echo "✗ Failed to migrate rating for post {$rating['postId']} by user {$rating['userId']}\n";
    }
}

echo "\nMigration completed! Check the Firebase console to verify the data.\n";
echo "Debug log available at: config/firebase_debug.log\n";
?> 