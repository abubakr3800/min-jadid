<?php
require_once 'includes/Database.php';

// Initialize database
$db = new Database();

// Get the user ID for ahmed.mo.abubakr@gmail.com
$user = $db->getUserByEmail('ahmed.mo.abubakr@gmail.com');
if (!$user) {
    die("User not found: ahmed.mo.abubakr@gmail.com\n");
}

$userId = $user['id'];
echo "Found user: {$user['firstName']} {$user['lastName']} (ID: $userId)\n";

// Read Bukhari hadiths
$bukhariData = json_decode(file_get_contents('islamic/bukhari.json'), true);
if (!$bukhariData || !isset($bukhariData['data']['hadiths'])) {
    die("Error reading Bukhari data\n");
}

$hadiths = $bukhariData['data']['hadiths'];
echo "Found " . count($hadiths) . " hadiths in Bukhari collection\n";

// Get the last 20 hadiths
$lastHadiths = array_slice($hadiths, -20);
echo "Adding last 20 hadiths (numbers " . $lastHadiths[0]['number'] . " to " . end($lastHadiths)['number'] . ")\n";

// Get all existing post titles for this user
$existingPosts = $db->getPosts(['userId' => $userId]);
$existingTitles = array_map(function($p) { return $p['title']; }, $existingPosts);

// Add posts
$addedCount = 0;
foreach ($lastHadiths as $hadith) {
    $title = 'من احاديث كتاب البخاري رقم ' . $hadith['number'];
    if (in_array($title, $existingTitles)) {
        echo "Skipped (already exists): $title\n";
        continue;
    }
    $postData = [
        'title' => $title,
        'content' => $hadith['arab'],
        'categoryId' => '6', // اسلاميات category
        'tags' => ['حديث', 'بخاري', 'إسلامي'],
        'userId' => $userId,
        'status' => 'published'
    ];
    $newPost = $db->addPost($postData);
    if ($newPost) {
        $addedCount++;
        echo "Added: $title\n";
    } else {
        echo "Failed to add: $title\n";
    }
}
echo "\nCompleted! Added $addedCount posts from the last 20 Bukhari hadiths.\n";
?> 