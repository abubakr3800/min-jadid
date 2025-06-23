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
    
    if (!isset($input['postId']) || !isset($input['comment'])) {
        http_response_code(400);
        echo json_encode(['error' => 'بيانات غير مكتملة']);
        exit;
    }
    
    $postId = $input['postId'];
    $comment = trim($input['comment']);
    
    // Validate comment
    if (empty($comment)) {
        http_response_code(400);
        echo json_encode(['error' => 'التعليق لا يمكن أن يكون فارغاً']);
        exit;
    }
    
    if (strlen($comment) > 1000) {
        http_response_code(400);
        echo json_encode(['error' => 'التعليق يجب أن يكون أقل من 1000 حرف']);
        exit;
    }
    
    // Check if post exists
    $post = $db->getPostById($postId);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'المقال غير موجود']);
        exit;
    }
    
    // Add comment
    $commentData = [
        'userId' => $userId,
        'postId' => $postId,
        'comment' => $comment
    ];
    
    if ($db->addComment($commentData)) {
        // Get updated comments for the post
        $comments = $db->getPostComments($postId);
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة التعليق بنجاح',
            'comments' => $comments
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'حدث خطأ أثناء إضافة التعليق']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $postId = $_GET['postId'] ?? '';
    
    if (empty($postId)) {
        http_response_code(400);
        echo json_encode(['error' => 'معرف المقال مطلوب']);
        exit;
    }
    
    // Check if post exists
    $post = $db->getPostById($postId);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'المقال غير موجود']);
        exit;
    }
    
    // Get comments for the post
    $comments = $db->getPostComments($postId);
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'طريقة طلب غير مدعومة']);
}
?> 