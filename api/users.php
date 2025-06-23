<?php
session_start();
require_once '../includes/Auth.php';
require_once '../includes/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$auth = new Auth();
$db = new Database();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'يجب تسجيل الدخول أولاً']);
    exit;
}

$currentUser = $auth->getCurrentUser();
if (!$db->isAdmin($currentUser['email'])) {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح لك']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'stats' && isset($_GET['userId'])) {
            $userId = $_GET['userId'];
            $user = $db->getUserById($userId);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'المستخدم غير موجود']);
                exit;
            }
            $posts = $db->getPosts(['userId' => $userId]);
            $totalLikes = 0;
            $totalViews = 0;
            foreach ($posts as $post) {
                $totalLikes += $post['likes'] ?? 0;
                $totalViews += $post['views'] ?? 0;
            }
            echo json_encode([
                'success' => true,
                'data' => [
                    'postsCount' => count($posts),
                    'totalLikes' => $totalLikes,
                    'totalViews' => $totalViews,
                    'posts' => $posts
                ]
            ]);
            exit;
        } else if ($action === 'list') {
            $users = $db->getAllUsersForAdmin();
            echo json_encode(['success' => true, 'data' => $users]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['error' => 'إجراء غير صحيح']);
        break;
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['userId'] ?? '';
        if (empty($userId)) {
            http_response_code(400);
            echo json_encode(['error' => 'معرف المستخدم مطلوب']);
            exit;
        }
        if ($userId == $currentUser['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'لا يمكنك حذف نفسك!']);
            exit;
        }
        $result = $db->deleteUserAndData($userId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم وكل بياناته بنجاح']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'حدث خطأ أثناء حذف المستخدم']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'طريقة طلب غير مدعومة']);
        break;
} 