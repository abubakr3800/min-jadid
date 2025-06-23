<?php
session_start();
file_put_contents(__DIR__ . '/debug.log', date('c') . "\n" . print_r([
    'session' => $_SESSION,
    'post' => $_POST,
    'method' => $_SERVER['REQUEST_METHOD']
], true) . "\n---\n", FILE_APPEND);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'طريقة طلب غير مدعومة']);
        break;
}

function handleGet() {
    global $db;
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $categoryId = $_GET['category'] ?? null;
            $limit = (int)($_GET['limit'] ?? 10);
            
            $filters = [];
            if ($categoryId) {
                $filters['categoryId'] = $categoryId;
            }
            
            $posts = $db->getPosts($filters);
            
            // Apply limit
            if ($limit > 0) {
                $posts = array_slice($posts, 0, $limit);
            }
            
            echo json_encode(['success' => true, 'data' => $posts]);
            break;
            
        case 'single':
            $postId = $_GET['id'] ?? '';
            if (empty($postId)) {
                http_response_code(400);
                echo json_encode(['error' => 'معرف المقال مطلوب']);
                return;
            }
            
            $post = $db->getPostById($postId);
            if ($post) {
                echo json_encode(['success' => true, 'data' => $post]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'المقال غير موجود']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'إجراء غير صحيح']);
            break;
    }
}

function handlePost() {
    global $db, $currentUser;

    // دعم استقبال action من JSON body أو من POST عادي
    $input = [];
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
    }
    $action = $_POST['action'] ?? $input['action'] ?? '';

    switch ($action) {
        case 'create':
            // Validate required fields
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            if (empty($title) || empty($content) || empty($category)) {
                http_response_code(400);
                echo json_encode(['error' => 'جميع الحقول المطلوبة يجب ملؤها']);
                exit;
            }
            
            // Validate title length
            if (strlen($title) > 200) {
                http_response_code(400);
                echo json_encode(['error' => 'عنوان المقال يجب أن يكون أقل من 200 حرف']);
                exit;
            }
            
            // Validate content length
            if (strlen($content) > 10000) {
                http_response_code(400);
                echo json_encode(['error' => 'محتوى المقال يجب أن يكون أقل من 10000 حرف']);
                exit;
            }
            
            // Get category ID from name
            $categories = $db->getCategories();
            $categoryId = null;
            foreach ($categories as $cat) {
                if ($cat['name'] === $category) {
                    $categoryId = $cat['id'];
                    break;
                }
            }
            
            if (!$categoryId) {
                http_response_code(400);
                echo json_encode(['error' => 'التصنيف المحدد غير صحيح']);
                exit;
            }
            
            // Prepare post data
            $postData = [
                'userId' => $currentUser['id'],
                'title' => $title,
                'content' => $content,
                'categoryId' => $categoryId,
                'tags' => $tags
            ];
            
            // Add post to database
            $newPost = $db->addPost($postData);
            
            // Notify all users except the author
            if ($newPost) {
                $allUsers = $db->getUsers();
                foreach ($allUsers as $user) {
                    if ($user['id'] != $currentUser['id']) {
                        $msg = 'تم نشر مقال جديد: ' . $title;
                        $link = 'post.php?id=' . $newPost['id'];
                        $db->addNotification($user['id'], $msg, $link, 'info');
                    }
                }
            }
            
            if ($newPost) {
                echo json_encode([
                    'success' => true,
                    'message' => 'تم نشر المقال بنجاح',
                    'postId' => $newPost['id'],
                    'post' => $newPost
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'حدث خطأ أثناء نشر المقال']);
            }
            break;
            
        case 'delete':
            // Allow delete via POST (for JS compatibility)
            $input = json_decode(file_get_contents('php://input'), true);
            $postId = $input['postId'] ?? '';
            if (empty($postId)) {
                http_response_code(400);
                echo json_encode(['error' => 'معرف المقال مطلوب']);
                exit;
            }
            $post = $db->getPostById($postId);
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'المقال غير موجود']);
                exit;
            }
            if ($post['userId'] !== $currentUser['id'] && !$db->isAdmin($currentUser['email'])) {
                http_response_code(403);
                echo json_encode(['error' => 'غير مصرح لك بحذف هذا المقال']);
                exit;
            }
            $result = $db->deletePost($postId);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'تم حذف المقال بنجاح'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'حدث خطأ أثناء حذف المقال']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'إجراء غير صحيح']);
            break;
    }
}

function handlePut() {
    global $db, $currentUser;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $postId = $input['postId'] ?? '';
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $category = trim($input['category'] ?? '');
            $tags = trim($input['tags'] ?? '');
            
            if (empty($postId) || empty($title) || empty($content) || empty($category)) {
                http_response_code(400);
                echo json_encode(['error' => 'جميع الحقول المطلوبة يجب ملؤها']);
                exit;
            }
            
            // Get the post to check ownership
            $post = $db->getPostById($postId);
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'المقال غير موجود']);
                exit;
            }
            
            // Check if user owns the post
            if ($post['userId'] !== $currentUser['id']) {
                http_response_code(403);
                echo json_encode(['error' => 'غير مصرح لك بتعديل هذا المقال']);
                exit;
            }
            
            // Get category ID from name
            $categories = $db->getCategories();
            $categoryId = null;
            foreach ($categories as $cat) {
                if ($cat['name'] === $category) {
                    $categoryId = $cat['id'];
                    break;
                }
            }
            
            if (!$categoryId) {
                http_response_code(400);
                echo json_encode(['error' => 'التصنيف المحدد غير صحيح']);
                exit;
            }
            
            // Update post data
            $postData = [
                'id' => $postId,
                'title' => $title,
                'content' => $content,
                'categoryId' => $categoryId,
                'tags' => $tags
            ];
            
            // Update post in database
            $updatedPost = $db->updatePost($postData);
            
            if ($updatedPost) {
                echo json_encode([
                    'success' => true,
                    'message' => 'تم تحديث المقال بنجاح',
                    'post' => $updatedPost
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'حدث خطأ أثناء تحديث المقال']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'إجراء غير صحيح']);
            break;
    }
}

function handleDelete() {
    global $db, $currentUser;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $postId = $input['postId'] ?? '';
            
            if (empty($postId)) {
                http_response_code(400);
                echo json_encode(['error' => 'معرف المقال مطلوب']);
                exit;
            }
            
            // Get the post to check ownership
            $post = $db->getPostById($postId);
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'المقال غير موجود']);
                exit;
            }
            
            // Check if user owns the post or is admin
            if ($post['userId'] !== $currentUser['id'] && !$db->isAdmin($currentUser['email'])) {
                http_response_code(403);
                echo json_encode(['error' => 'غير مصرح لك بحذف هذا المقال']);
                exit;
            }
            
            // Delete post from database
            $result = $db->deletePost($postId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'تم حذف المقال بنجاح'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'حدث خطأ أثناء حذف المقال']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'إجراء غير صحيح']);
            break;
    }
}
?> 