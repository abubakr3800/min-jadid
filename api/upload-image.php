<?php
header('Content-Type: application/json');

$dir = '../img/uploads/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'لم يتم رفع الصورة بنجاح']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed)) {
    echo json_encode(['error' => 'صيغة الصورة غير مدعومة']);
    exit;
}

$filename = 'img_' . time() . '_' . rand(1000,9999) . '.' . $ext;
$target = $dir . $filename;
if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
    $url = 'img/uploads/' . $filename;
    echo json_encode(['location' => $url]);
} else {
    echo json_encode(['error' => 'حدث خطأ أثناء حفظ الصورة']);
} 