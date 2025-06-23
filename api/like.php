<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/Auth.php';
require_once '../includes/Database.php';

$auth = new Auth();
$db = new Database();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'يجب تسجيل الدخول أولاً']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = $currentUser['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['postId'])) {
        http_response_code(400);
        echo json_encode(['error' => 'معرف المقال مطلوب']);
        exit;
    }
    
    $postId = $input['postId'];
    
    // Check if post exists
    $post = $db->getPostById($postId);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'المقال غير موجود']);
        exit;
    }
    
    // Check if user already liked the post
    $hasLiked = $db->hasUserLikedPost($userId, $postId);
    
    if ($hasLiked) {
        // Remove like
        if ($db->removeLike($userId, $postId)) {
            $updatedPost = $db->getPostById($postId);
            echo json_encode([
                'success' => true,
                'message' => 'تم إلغاء الإعجاب',
                'liked' => false,
                'post' => $updatedPost
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'حدث خطأ أثناء إلغاء الإعجاب']);
        }
    } else {
        // Add like
        $likeData = [
            'userId' => $userId,
            'postId' => $postId
        ];
        
        if ($db->addLike($likeData)) {
            $updatedPost = $db->getPostById($postId);
            echo json_encode([
                'success' => true,
                'message' => 'تم الإعجاب بنجاح',
                'liked' => true,
                'post' => $updatedPost
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'حدث خطأ أثناء الإعجاب']);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'طريقة طلب غير مدعومة']);
}
?> 