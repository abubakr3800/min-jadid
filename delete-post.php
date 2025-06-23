<?php
session_start();
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: index.php');
    exit();
}

$postId = $_POST['id'];
$post = $db->getPostById($postId);
if (!$post) {
    header('Location: index.php');
    exit();
}

// Only author can delete
if (!$isLoggedIn || $post['userId'] != $currentUser['id']) {
    header('Location: post.php?id=' . $postId);
    exit();
}

$result = $db->deletePost($postId);
if ($result) {
    $_SESSION['success_message'] = 'تم حذف المقال بنجاح';
    header('Location: index.php');
    exit();
} else {
    $_SESSION['error_message'] = 'حدث خطأ أثناء حذف المقال';
    header('Location: post.php?id=' . $postId);
    exit();
} 