<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $auth->getCurrentUserId();
$user = $db->getUserById($userId);

if (!$user) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    // Validation
    if (empty($firstName) || empty($lastName)) {
        $message = 'الاسم الأول واسم العائلة مطلوبان';
        $messageType = 'error';
    } else {
        // Handle avatar upload
        $avatarPath = $user['avatar']; // Keep existing avatar by default
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileTmp = $file['tmp_name'];
            $fileType = $file['type'];
            
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($fileType, $allowedTypes)) {
                $message = 'يجب أن تكون الصورة بصيغة JPG أو PNG';
                $messageType = 'error';
            } elseif ($fileSize > $maxSize) {
                $message = 'حجم الصورة يجب أن يكون أقل من 5 ميجابايت';
                $messageType = 'error';
            } else {
                // Generate unique filename
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = 'user_' . $userId . '_' . time() . '.' . $extension;
                $uploadPath = 'img/avatars/' . $newFileName;
                
                // Create avatars directory if it doesn't exist
                if (!is_dir('img/avatars')) {
                    mkdir('img/avatars', 0755, true);
                }
                
                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    $avatarPath = $uploadPath;
                } else {
                    $message = 'فشل في رفع الصورة';
                    $messageType = 'error';
                }
            }
        }
        
        // Update user data
        if (empty($message)) {
            $updateData = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'bio' => $bio,
                'linkedin' => $linkedin,
                'avatar' => $avatarPath
            ];
            
            if ($db->updateUser($userId, $updateData)) {
                $message = 'تم تحديث الملف الشخصي بنجاح';
                $messageType = 'success';
                
                // Update user data for display
                $user = array_merge($user, $updateData);
            } else {
                $message = 'حدث خطأ أثناء تحديث الملف الشخصي';
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - تعديل الملف الشخصي</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    <meta name="description" content="تعديل الملف الشخصي في منصة من جديد">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <style>
        .edit-profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .edit-profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .edit-profile-header h1 {
            color: #495057;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .edit-profile-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: #ffffff;
            color: #495057;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            font-style: italic;
        }
        
        .avatar-section {
            text-align: center;
            margin: 2rem 0;
        }
        
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto 1rem;
            display: block;
        }
        
        .avatar-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .avatar-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .avatar-upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .avatar-upload-btn:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .avatar-info {
            margin-top: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .avatar-info small {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .avatar-info i {
            color: #667eea;
            margin-right: 0.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f8f9fa;
        }
        
        .btn {
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            min-width: 120px;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #5a6fd8;
        }
        
        @media (max-width: 768px) {
            .edit-profile-container {
                margin: 1rem;
                padding: 1.5rem;
                border-radius: 15px;
            }
            
            .edit-profile-header h1 {
                font-size: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                padding: 1rem;
                font-size: 16px;
            }
            
            .avatar-preview {
                width: 120px;
                height: 120px;
            }
        }
        
        @media (max-width: 480px) {
            .edit-profile-container {
                margin: 0.5rem;
                padding: 1rem;
                border-radius: 10px;
            }
            
            .edit-profile-header h1 {
                font-size: 1.25rem;
            }
            
            .form-control {
                padding: 1rem;
                font-size: 16px;
            }
            
            .avatar-preview {
                width: 100px;
                height: 100px;
            }
        }
        
        /* Navbar Styles - Overriding responsive.css with higher specificity */
        header.navbar {
            background: var(--white) !important;
            box-shadow: var(--shadow-sm) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: var(--z-sticky) !important;
            border-bottom: 1px solid var(--border-light) !important;
            padding: 0 !important;
        }
        
        header.navbar .container {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: var(--spacing-md) var(--spacing-lg) !important;
        }
        
        header.navbar .navbar-brand {
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            font-weight: 700 !important;
            font-size: var(--font-size-xl) !important;
            color: var(--text-dark) !important;
        }
        
        header.navbar .navbar-brand img {
            height: 40px !important;
            margin-left: var(--spacing-sm) !important;
        }
        
        /* Desktop navbar styles - ensure horizontal layout */
        header.navbar .navbar-nav {
            display: flex !important;
            align-items: center !important;
            gap: var(--spacing-lg) !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            flex-direction: row !important;
            position: static !important;
            background: transparent !important;
            box-shadow: none !important;
            border-top: none !important;
        }
        
        header.navbar .nav-link {
            color: var(--text-medium) !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            transition: all var(--transition-fast) !important;
            padding: var(--spacing-sm) var(--spacing-md) !important;
            border-radius: var(--border-radius-md) !important;
            border-bottom: none !important;
        }
        
        header.navbar .nav-link:hover,
        header.navbar .nav-link.active {
            color: var(--primary-color) !important;
            background: var(--primary-lighter) !important;
        }
        
        header.navbar .navbar-toggler {
            display: none !important;
            background: none !important;
            border: none !important;
            font-size: var(--font-size-lg) !important;
            color: var(--text-dark) !important;
            cursor: pointer !important;
            padding: var(--spacing-sm) !important;
            border-radius: var(--border-radius-sm) !important;
        }
        
        /* Mobile styles only */
        @media (max-width: 768px) {
            header.navbar .navbar-nav {
                display: none !important;
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                right: 0 !important;
                background: var(--white) !important;
                flex-direction: column !important;
                padding: var(--spacing-md) !important;
                gap: var(--spacing-sm) !important;
                box-shadow: var(--shadow-lg) !important;
                border-top: 1px solid var(--border-light) !important;
            }
            
            header.navbar .navbar-nav.show {
                display: flex !important;
            }
            
            header.navbar .navbar-nav li {
                width: 100% !important;
            }
            
            header.navbar .navbar-nav .nav-link {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                padding: var(--spacing-md) !important;
                border-bottom: 1px solid var(--border-light) !important;
            }
            
            header.navbar .navbar-nav .nav-link:last-child {
                border-bottom: none !important;
            }
            
            header.navbar .navbar-nav .btn {
                width: 100% !important;
                margin-top: var(--spacing-sm) !important;
            }
            
            header.navbar .navbar-toggler {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- الشريط العلوي -->
        <header class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <img src="img/logo.svg" alt="شعار من جديد">
                </a>
                <button class="navbar-toggler" id="navbarToggler" aria-label="فتح القائمة">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="navbar-nav" id="navbarNav">
                    <li><a href="index.php" class="nav-link">الرئيسية</a></li>
                    <li><a href="explore.php" class="nav-link">استكشاف</a></li>
                    <li><a href="about.php" class="nav-link">عن المنصة</a></li>
                    <li><a href="services.php" class="nav-link">خدماتنا</a></li>
                    <li><a href="new-post.php" class="nav-link">مقال جديد</a></li>
                    <li><a href="profile.php" class="nav-link active">الملف الشخصي</a></li>
                    <li><a href="logout.php" class="btn btn-outline-primary">تسجيل الخروج</a></li>
                </ul>
            </div>
        </header>

        <!-- القسم الرئيسي -->
        <main class="main-content">
            <div class="container">
                <div class="edit-profile-container">
                    <!-- Back Link -->
                    <a href="profile.php" class="back-link">
                        <i class="fas fa-arrow-right"></i>
                        العودة إلى الملف الشخصي
                    </a>
                    
                    <!-- Header -->
                    <div class="edit-profile-header">
                        <h1><i class="fas fa-edit"></i> تعديل الملف الشخصي</h1>
                        <p>قم بتحديث معلوماتك الشخصية وصورتك</p>
                    </div>
                    
                    <!-- Message Display -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Edit Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> المعلومات الشخصية</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="firstName" class="form-label">الاسم الأول</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" 
                                               value="<?= htmlspecialchars($user['firstName']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lastName" class="form-label">اسم العائلة</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" 
                                               value="<?= htmlspecialchars($user['lastName']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">نبذة شخصية</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" 
                                          placeholder="اكتب نبذة مختصرة عن نفسك..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="linkedin" class="form-label">رابط LinkedIn</label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                       value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" 
                                       placeholder="https://linkedin.com/in/username">
                            </div>
                        </div>
                        
                        <!-- Avatar Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-camera"></i> الصورة الشخصية</h3>
                            
                            <div class="avatar-section">
                                <img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'img/avatar-placeholder.png' ?>" 
                                     alt="الصورة الشخصية" class="avatar-preview" id="avatarPreview">
                                
                                <div class="avatar-upload">
                                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;">
                                    <button type="button" class="avatar-upload-btn" onclick="document.getElementById('avatar').click()">
                                        <i class="fas fa-upload"></i>
                                        تغيير الصورة
                                    </button>
                                </div>
                                
                                <div class="avatar-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i>
                                        يمكنك رفع صورة بحجم أقصى 5 ميجابايت بصيغة JPG أو PNG
                                    </small>
                                </div>
                                <div id="avatarError" class="text-danger mt-2" style="font-size:0.95em;"></div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <p>&copy; 2024 من جديد. جميع الحقوق محفوظة.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Avatar Cropper Modal -->
    <div class="modal fade" id="avatarCropperModal" tabindex="-1" aria-labelledby="avatarCropperModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarCropperModalLabel">تعديل الصورة الشخصية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                    <div style="width: 300px; height: 300px; margin: 0 auto;">
                        <img id="avatarCropperImg" src="" style="max-width:100%; max-height:100%; display:block; margin:0 auto;">
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="cropAvatarBtn">استخدام الصورة</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cropper.js -->
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Navbar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.getElementById('navbarToggler');
            const navbarNav = document.getElementById('navbarNav');
            
            if (navbarToggler && navbarNav) {
                navbarToggler.addEventListener('click', function() {
                    navbarNav.classList.toggle('show');
                });
                
                // Close navbar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!navbarToggler.contains(event.target) && !navbarNav.contains(event.target)) {
                        navbarNav.classList.remove('show');
                    }
                });
            }
        });
        
        // Image Cropping Variables
        let avatarCropper, croppedAvatarBlob;
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Edit Profile DOM loaded');
            console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
            console.log('Cropper available:', typeof Cropper !== 'undefined');
            
            const avatarInput = document.getElementById('avatar');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarError = document.getElementById('avatarError');
            const avatarCropperModal = document.getElementById('avatarCropperModal');
            const avatarCropperImg = document.getElementById('avatarCropperImg');
            const cropAvatarBtn = document.getElementById('cropAvatarBtn');
            let avatarCropperModalInstance;
            
            console.log('Avatar elements found:', {
                avatarInput: !!avatarInput,
                avatarPreview: !!avatarPreview,
                avatarCropperModal: !!avatarCropperModal,
                avatarCropperImg: !!avatarCropperImg,
                cropAvatarBtn: !!cropAvatarBtn
            });
            
            function openAvatarCropper(file) {
                if (!avatarCropperImg || !avatarCropperModal) return;
                avatarError && (avatarError.textContent = '');
                var reader = new FileReader();
                reader.onload = function(e) {
                    avatarCropperImg.src = e.target.result;
                    avatarCropperModalInstance = new bootstrap.Modal(avatarCropperModal);
                    avatarCropperModalInstance.show();
                };
                reader.readAsDataURL(file);
            }
            
            function validateAvatarImage(file) {
                if (!avatarError) return false;
                avatarError.textContent = '';
                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
                    avatarError.textContent = 'الرجاء اختيار صورة بصيغة jpg, png, gif, أو webp';
                    return false;
                }
                if (file.size > 5 * 1024 * 1024) {
                    avatarError.textContent = 'الحد الأقصى لحجم الصورة هو 5 ميجابايت';
                    return false;
                }
                return true;
            }
            
            if (avatarInput && avatarPreview) {
                avatarInput.addEventListener('change', function() {
                    if (this.files && this.files[0] && validateAvatarImage(this.files[0])) {
                        openAvatarCropper(this.files[0]);
                    }
                });
            }
            
            if (avatarCropperModal) {
                avatarCropperModal.addEventListener('shown.bs.modal', function () {
                    if (avatarCropper) avatarCropper.destroy();
                    if (!avatarCropperImg) return;
                    avatarCropper = new Cropper(avatarCropperImg, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        guides: true,
                        center: true,
                        background: false,
                        highlight: false,
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        minContainerWidth: 300,
                        minContainerHeight: 300
                    });
                });
            }
            
            cropAvatarBtn && cropAvatarBtn.addEventListener('click', function() {
                if (avatarCropper) {
                    avatarCropper.getCroppedCanvas({ width: 300, height: 300, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
                        croppedAvatarBlob = blob;
                        var url = URL.createObjectURL(blob);
                        avatarPreview && (avatarPreview.src = url);
                        // Set a fake File in the input for form submission
                        var dt = new DataTransfer();
                        var file = new File([blob], 'avatar.png', { type: 'image/png' });
                        dt.items.add(file);
                        avatarInput && (avatarInput.files = dt.files);
                        avatarCropperModalInstance && avatarCropperModalInstance.hide();
                    }, 'image/png');
                }
            });
            
            // Clean up cropper when modal is hidden
            avatarCropperModal && avatarCropperModal.addEventListener('hidden.bs.modal', function() {
                if (avatarCropper) {
                    avatarCropper.destroy();
                    avatarCropper = null;
                }
                // Clean up any leftover backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
            });
            
            // Form validation
            const form = document.querySelector('form');
            const firstName = document.getElementById('firstName');
            const lastName = document.getElementById('lastName');
            
            form.addEventListener('submit', function(e) {
                if (!firstName.value.trim() || !lastName.value.trim()) {
                    e.preventDefault();
                    alert('الاسم الأول واسم العائلة مطلوبان');
                    return false;
                }
            });
        });
    </script>
</body>
</html>