<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/Auth.php';
require_once '../includes/Database.php';

$auth = new Auth();
$db = new Database();

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'طريقة الطلب غير صحيحة']);
    exit;
}

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');

if (!$firstName || !$lastName) {
    echo json_encode(['success' => false, 'error' => 'يرجى إدخال الاسم الأول واسم العائلة']);
    exit;
}

$avatarPath = $currentUser['avatar'] ?? '';

// Handle avatar upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['avatar'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileType = $file['type'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'نوع الملف غير مسموح به. يرجى اختيار صورة بصيغة JPG, PNG, GIF, أو WebP']);
        exit;
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($fileSize > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'حجم الملف كبير جداً. الحد الأقصى هو 5 ميجابايت']);
        exit;
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'امتداد الملف غير مسموح به']);
        exit;
    }
    
    // Create avatars directory if it doesn't exist
    $avatarDir = '../img/avatars/';
    if (!is_dir($avatarDir)) {
        if (!mkdir($avatarDir, 0777, true)) {
            echo json_encode(['success' => false, 'error' => 'فشل في إنشاء مجلد الصور الشخصية']);
            exit;
        }
    }
    
    // Generate unique filename
    $filename = 'user_' . $currentUser['id'] . '_' . time() . '.' . $ext;
    $targetPath = $avatarDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmp, $targetPath)) {
        // Delete old avatar if exists
        if (!empty($currentUser['avatar']) && file_exists('../' . $currentUser['avatar'])) {
            unlink('../' . $currentUser['avatar']);
        }
        
        $avatarPath = 'img/avatars/' . $filename;
    } else {
        echo json_encode(['success' => false, 'error' => 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى']);
        exit;
    }
}

$updateData = [
    'id' => $currentUser['id'],
    'firstName' => $firstName,
    'lastName' => $lastName,
    'bio' => $bio,
    'linkedin' => $linkedin,
    'avatar' => $avatarPath
];

$result = $db->updateUser($updateData);
if ($result) {
    // Update session
    foreach ($updateData as $k => $v) {
        if ($k !== 'id') $_SESSION['user'][$k] = $v;
    }
    echo json_encode([
        'success' => true, 
        'message' => 'تم تحديث الملف الشخصي بنجاح', 
        'avatar' => $avatarPath,
        'user' => $updateData
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'حدث خطأ أثناء تحديث الملف الشخصي']);
} 