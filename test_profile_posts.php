<?php
require_once 'includes/Database.php';

$db = new Database();

// Get user by email
$user = $db->getUserByEmail('ahmed.mo.abubakr@gmail.com');
if (!$user) {
    die("User not found\n");
}

echo "User ID: " . $user['id'] . "\n";
echo "User Name: " . $user['firstName'] . " " . $user['lastName'] . "\n\n";

// Get all posts for this user
$userPosts = $db->getPosts(['userId' => $user['id']]);

echo "Total posts found: " . count($userPosts) . "\n\n";

// Show first 5 posts
echo "First 5 posts:\n";
for ($i = 0; $i < min(5, count($userPosts)); $i++) {
    $post = $userPosts[$i];
    echo ($i + 1) . ". ID: " . $post['id'] . " - Title: " . $post['title'] . " - Created: " . $post['createdAt'] . "\n";
}

echo "\nLast 5 posts:\n";
$lastPosts = array_slice($userPosts, -5);
foreach ($lastPosts as $i => $post) {
    echo ($i + 1) . ". ID: " . $post['id'] . " - Title: " . $post['title'] . " - Created: " . $post['createdAt'] . "\n";
}
?> 