<?php
require_once 'includes/Database.php';

// Initialize database
$db = new Database();

// Get a test post (first post from user with email ahmed.mo.abubakr@gmail.com)
$user = $db->getUserByEmail('ahmed.mo.abubakr@gmail.com');
if (!$user) {
    die("User not found\n");
}

$posts = $db->getPosts(['userId' => $user['id']]);
if (empty($posts)) {
    die("No posts found for user\n");
}

$testPost = $posts[0];
echo "Testing post editing functionality...\n";
echo "Post ID: " . $testPost['id'] . "\n";
echo "Original Title: " . $testPost['title'] . "\n";
echo "Original Content Length: " . strlen($testPost['content']) . "\n";

// Test updating the post
$updateData = [
    'id' => $testPost['id'],
    'title' => $testPost['title'] . ' (TEST)',
    'content' => $testPost['content'] . "\n\nتم اختبار التعديل بنجاح!",
    'categoryId' => $testPost['categoryId'],
    'tags' => $testPost['tags'],
    'coverImage' => $testPost['coverImage'] ?? ''
];

$result = $db->updatePost($updateData);

if ($result) {
    echo "✅ Post update successful!\n";
    echo "Updated Title: " . $result['title'] . "\n";
    echo "Updated Content Length: " . strlen($result['content']) . "\n";
    echo "Updated At: " . $result['updatedAt'] . "\n";
} else {
    echo "❌ Post update failed!\n";
}

// Test the edit URL
echo "\nEdit URL: edit-post.php?id=" . $testPost['id'] . "\n";
echo "View URL: post.php?id=" . $testPost['id'] . "\n";
?> 