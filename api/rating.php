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
    
    if (!isset($input['postId']) || !isset($input['rating'])) {
        http_response_code(400);
        echo json_encode(['error' => 'بيانات غير مكتملة']);
        exit;
    }
    
    $postId = $input['postId'];
    $rating = (int)$input['rating'];
    
    // Validate rating (1-5)
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'التقييم يجب أن يكون بين 1 و 5']);
        exit;
    }
    
    // Check if post exists
    $post = $db->getPostById($postId);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'المقال غير موجود']);
        exit;
    }
    
    // Add or update rating
    $ratingData = [
        'userId' => $userId,
        'postId' => $postId,
        'rating' => $rating
    ];
    
    if ($db->updateRating($ratingData)) {
        // Get updated post data
        $updatedPost = $db->getPostById($postId);
        $userRating = $db->getUserRating($userId, $postId);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم التقييم بنجاح',
            'post' => $updatedPost,
            'userRating' => $userRating
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'حدث خطأ أثناء التقييم']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'طريقة طلب غير مدعومة']);
}
?> 