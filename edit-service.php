<?php
require_once 'includes/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}
$currentUser = $auth->getCurrentUser();
$servicesFile = 'data/services.json';
$allServices = file_exists($servicesFile) ? json_decode(file_get_contents($servicesFile), true) : [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;
foreach ($allServices as $srv) {
    if ($srv['id'] == $id) {
        $service = $srv;
        break;
    }
}
if (!$service || $service['userId'] != $currentUser['id']) {
    die('<div style="text-align:center;margin:3rem auto;font-size:1.3rem;color:#b91c1c;">غير مصرح لك بتعديل هذه الخدمة أو الخدمة غير موجودة.</div>');
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['desc'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $imagePath = $service['image'];
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $uploadsDir = 'img/uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imgName = 'service_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $targetPath = $uploadsDir . $imgName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        }
    }
    if ($title && $desc && $price) {
        // تحديث الخدمة في القائمة
        foreach ($allServices as &$srv) {
            if ($srv['id'] == $id && $srv['userId'] == $currentUser['id']) {
                $srv['title'] = $title;
                $srv['desc'] = $desc;
                $srv['price'] = $price;
                $srv['image'] = $imagePath;
                break;
            }
        }
        unset($srv);
        file_put_contents($servicesFile, json_encode($allServices, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $success = 'تم تحديث الخدمة بنجاح!';
        // إعادة تحميل البيانات المحدثة
        $service['title'] = $title;
        $service['desc'] = $desc;
        $service['price'] = $price;
        $service['image'] = $imagePath;
    } else {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل خدمة - من جديد</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .add-service-container {
            max-width: 500px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 2.5rem 2rem;
        }
        .add-service-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 1rem;
        }
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f8cff 0%, #764ba2 100%);
            color: #fff;
            border: none;
            font-weight: 700;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #764ba2 0%, #4f8cff 100%);
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .alert-success { background: #e6ffed; color: #1a7f37; }
        .alert-error { background: #ffe6e6; color: #b91c1c; }
        .service-img-preview {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <header class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <img src="img/logo.svg" alt="شعار من جديد">
                </a>
                <ul class="navbar-nav">
                    <li><a href="index.php" class="nav-link">الرئيسية</a></li>
                    <li><a href="explore.php" class="nav-link">استكشاف</a></li>
                    <li><a href="about.php" class="nav-link">عن المنصة</a></li>
                    <li><a href="services.php" class="nav-link">خدماتنا</a></li>
                    <li><a href="profile.php" class="nav-link">الملف الشخصي</a></li>
                    <li><a href="logout.php" class="btn btn-outline-primary">تسجيل الخروج</a></li>
                </ul>
            </div>
        </header>
        <main class="main-content">
            <div class="add-service-container">
                <div class="add-service-title">
                    <i class="fas fa-edit"></i> تعديل الخدمة
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                <?php if (!empty($service['image'])): ?>
                    <img src="<?= htmlspecialchars($service['image']) ?>" alt="صورة الخدمة" class="service-img-preview">
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="title">عنوان الخدمة <span style="color:red">*</span></label>
                        <input class="form-control" type="text" id="title" name="title" required maxlength="100" value="<?= htmlspecialchars($service['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="desc">وصف الخدمة <span style="color:red">*</span></label>
                        <textarea class="form-control" id="desc" name="desc" rows="4" required maxlength="1000"><?= htmlspecialchars($service['desc']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="price">السعر بالجنيه المصري <span style="color:red">*</span></label>
                        <input class="form-control" type="number" id="price" name="price" min="1" required value="<?= htmlspecialchars($service['price']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="image">تغيير صورة الخدمة (اختياري)</label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> حفظ التعديلات
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 