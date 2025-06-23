<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/Auth.php';
require_once '../includes/Database.php';

$auth = new Auth();
$db = new Database();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول أولاً']);
    exit;
}

$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'طريقة طلب غير صحيحة']);
    exit;
}

try {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryName = trim($_POST['category'] ?? '');
    $tags = json_decode($_POST['tags'] ?? '[]', true);
    
    // Validate required fields
    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'العنوان مطلوب']);
        exit;
    }
    
    if (empty($content)) {
        echo json_encode(['success' => false, 'error' => 'المحتوى مطلوب']);
        exit;
    }
    
    if (empty($categoryName)) {
        echo json_encode(['success' => false, 'error' => 'التصنيف مطلوب']);
        exit;
    }
    
    // Get category ID
    $categories = $db->getCategories();
    $categoryId = null;
    foreach ($categories as $category) {
        if ($category['name'] === $categoryName) {
            $categoryId = $category['id'];
            break;
        }
    }
    
    if (!$categoryId) {
        echo json_encode(['success' => false, 'error' => 'التصنيف غير صحيح']);
        exit;
    }
    
    // Handle cover image upload
    $coverImagePath = '';
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['coverImage'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = $file['type'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'نوع الملف غير مسموح به']);
            exit;
        }
        
        // Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'حجم الملف كبير جداً. الحد الأقصى هو 2 ميجابايت']);
            exit;
        }
        
        // Get file extension
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($ext, $allowedExtensions)) {
            echo json_encode(['success' => false, 'error' => 'امتداد الملف غير مسموح به']);
            exit;
        }
        
        // Create covers directory if it doesn't exist
        $coverDir = '../img/covers/';
        if (!is_dir($coverDir)) {
            if (!mkdir($coverDir, 0777, true)) {
                echo json_encode(['success' => false, 'error' => 'فشل في إنشاء مجلد الصور']);
                exit;
            }
        }
        
        // Generate unique filename
        $filename = 'cover_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $coverDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $coverImagePath = 'img/covers/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'فشل في حفظ الصورة']);
            exit;
        }
    }
    
    // Create post data
    $postData = [
        'id' => uniqid(),
        'title' => $title,
        'content' => $content,
        'categoryId' => $categoryId,
        'category' => $categoryName,
        'tags' => $tags,
        'coverImage' => $coverImagePath,
        'userId' => $currentUser['id'],
        'author' => $currentUser['name'],
        'authorAvatar' => $currentUser['avatar'],
        'createdAt' => date('c'),
        'updatedAt' => date('c'),
        'likes' => 0,
        'views' => 0,
        'rating' => 0,
        'ratingCount' => 0
    ];
    
    // Save post
    $result = $db->createPost($postData);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'تم نشر المقال بنجاح',
            'postId' => $postData['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'فشل في حفظ المقال']);
    }
    
} catch (Exception $e) {
    error_log('Create Post Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'حدث خطأ غير متوقع']);
}
?> 