<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$categories = $db->getCategories();
$isLoggedIn = $auth->isLoggedIn();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء مقال جديد - من جديد</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/omzsk362r3utt1ymlppi704ycstkfj52ky9f5z80bv7sdcub/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
        }
        
        /* Navbar Styles - Matching main.css exactly */
        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
            border-bottom: 1px solid var(--border-light);
        }
        
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-md) var(--spacing-lg);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 700;
            font-size: var(--font-size-xl);
            color: var(--text-dark);
        }
        
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        
        .navbar-toggler {
            display: none;
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: var(--font-size-lg);
            cursor: pointer;
            padding: var(--spacing-sm);
            border-radius: var(--border-radius-sm);
            transition: var(--transition-normal);
        }
        
        .navbar-toggler:hover {
            background: var(--light-gray);
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-link {
            color: var(--text-medium);
            text-decoration: none;
            font-weight: 500;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-md);
            transition: var(--transition-normal);
            position: relative;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color);
            background: var(--primary-lighter);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .navbar-toggler {
                display: block;
            }
            
            .navbar-nav {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                left: 0;
                background: var(--white);
                flex-direction: column;
                padding: var(--spacing-md);
                box-shadow: var(--shadow-md);
                border-top: 1px solid var(--border-light);
                z-index: var(--z-dropdown);
            }
            
            .navbar-nav.show {
                display: flex;
            }
            
            .nav-link {
                width: 100%;
                text-align: center;
                padding: var(--spacing-md);
            }
        }
        
        /* Desktop styles to override responsive.css */
        @media (min-width: 769px) {
            .navbar-nav {
                display: flex !important;
                flex-direction: row !important;
                position: static !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
                z-index: auto !important;
            }
            
            .navbar-toggler {
                display: none !important;
            }
        }
        
        .create-post-container {
            padding: 2rem 0;
        }
        
        .post-form-card {
            background: var(--white);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            border: none;
        }
        
        .post-form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .post-form-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        .post-form-icon {
            width: 80px;
            height: 80px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--primary-color);
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
        }
        
        .post-form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .post-form-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .post-form-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            font-size: 1rem;
            transition: all var(--transition-normal);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem var(--primary-lighter);
            outline: none;
        }
        
        /* Cover Image Upload Styles */
        .cover-upload-section {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .cover-drop-zone {
            width: 100%;
            max-width: 400px;
            height: 250px;
            border: 3px dashed var(--border-color);
            border-radius: var(--border-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--light) 0%, var(--primary-lighter) 100%);
            cursor: pointer;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        .cover-drop-zone:hover {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-lighter) 0%, var(--accent-light) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .cover-drop-zone.dragover {
            border-color: var(--accent-color);
            background: linear-gradient(135deg, var(--accent-light) 0%, var(--primary-lighter) 100%);
            transform: scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .cover-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius-lg);
        }
        
        .cover-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            color: var(--text-muted);
            padding: 2rem;
        }
        
        .cover-placeholder i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .cover-placeholder span {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .cover-placeholder small {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .cover-error {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: var(--error-light);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--error-color);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius-lg);
            font-weight: 600;
            transition: all var(--transition-normal);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-secondary {
            background: var(--secondary-color);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        .modal-body { 
            max-height: 70vh; 
            overflow-y: auto; 
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                <li><a href="new-post.php" class="nav-link active">مقال جديد</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="profile.php" class="nav-link">الملف الشخصي</a></li>
                    <li><a href="logout.php" class="nav-link">تسجيل الخروج</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">تسجيل الدخول</a></li>
                    <li><a href="signup.php" class="nav-link">إنشاء حساب</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="create-post-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="post-form-card">
                            <div class="post-form-header">
                                <div class="post-form-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h1 class="post-form-title">إنشاء مقال جديد</h1>
                                <p class="post-form-subtitle">شارك أفكارك ومعرفتك مع المجتمع</p>
                            </div>
                            
                            <div class="post-form-body">
                                <form id="newPostForm" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="form-label" for="title">
                                            <i class="fas fa-heading"></i> عنوان المقال
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               placeholder="اكتب عنوان المقال هنا...">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="coverImage">
                                            <i class="fas fa-image"></i> صورة الغلاف
                                        </label>
                                        <div class="cover-upload-section">
                                            <div id="coverDropZone" class="cover-drop-zone">
                                                <div class="cover-placeholder">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                    <span>اسحب الصورة هنا أو اضغط للاختيار</span>
                                                    <small>JPG, PNG, GIF, WebP - حتى 5MB</small>
                                                </div>
                                                <img id="coverPreview" class="cover-preview" style="display: none;" alt="معاينة الغلاف">
                                                <input type="file" id="coverImage" name="coverImage" accept="image/*" style="display: none;">
                                            </div>
                                            <div id="coverError" class="cover-error"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="content">
                                            <i class="fas fa-file-alt"></i> محتوى المقال
                                        </label>
                                        <textarea class="form-control" id="content" name="content" required 
                                                  placeholder="اكتب محتوى المقال هنا..."></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="categoryId">
                                            <i class="fas fa-tags"></i> التصنيف
                                        </label>
                                        <select class="form-select" id="categoryId" name="categoryId" required>
                                            <option value="">اختر تصنيفاً</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="tags">
                                            <i class="fas fa-hashtag"></i> الوسوم (اختياري)
                                        </label>
                                        <input type="text" class="form-control" id="tags" name="tags" 
                                               placeholder="اكتب الوسوم مفصولة بفاصلة...">
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> نشر المقال
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> إلغاء
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Cover Cropper Modal -->
    <div class="modal fade" id="coverCropperModal" tabindex="-1" aria-labelledby="coverCropperModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="coverCropperModalLabel">تعديل صورة الغلاف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center">
                    <div style="width: 400px; height: 225px; margin: 0 auto;">
                        <img id="coverCropperImg" src="" style="max-width:100%; max-height:100%; display:block; margin:0 auto;">
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="cropCoverBtn">استخدام الصورة</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cropper.js -->
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    
    <script>
        // TinyMCE Configuration with stable plugins
        tinymce.init({
            selector: '#content',
            directionality: 'rtl',
            plugins: 'lists link image table autolink preview',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | preview',
            height: 400,
            menubar: false,
            branding: false,
            content_style: 'body { font-family: Tajawal, Arial, sans-serif; font-size: 1.1rem; direction: rtl; line-height: 1.6; }',
            block_formats: 'فقرة=p; عنوان 1=h1; عنوان 2=h2; عنوان 3=h3; اقتباس=blockquote; كود=pre',
            language: 'ar',
            placeholder: 'اكتب محتوى المقال هنا...',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Simple Rich Text Editor (Fallback from TinyMCE)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('New Post DOM loaded');
            console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
            console.log('Cropper available:', typeof Cropper !== 'undefined');
            
            // Navbar Toggle Functionality
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
            
            // Simple textarea styling for content
            const contentTextarea = document.getElementById('content');
            if (contentTextarea) {
                contentTextarea.style.minHeight = '300px';
                contentTextarea.style.fontSize = '1.1rem';
                contentTextarea.style.lineHeight = '1.6';
                contentTextarea.style.fontFamily = 'Tajawal, Arial, sans-serif';
            }
            
            // Image Cropping Variables
            let coverCropper, croppedCoverBlob;
            
            const coverInput = document.getElementById('coverImage');
            const coverPreview = document.getElementById('coverPreview');
            const coverDropZone = document.getElementById('coverDropZone');
            const coverError = document.getElementById('coverError');
            const coverCropperModal = document.getElementById('coverCropperModal');
            const coverCropperImg = document.getElementById('coverCropperImg');
            const cropCoverBtn = document.getElementById('cropCoverBtn');
            let coverCropperModalInstance;
            
            console.log('Cover elements found:', {
                coverInput: !!coverInput,
                coverPreview: !!coverPreview,
                coverDropZone: !!coverDropZone,
                coverCropperModal: !!coverCropperModal,
                coverCropperImg: !!coverCropperImg,
                cropCoverBtn: !!cropCoverBtn
            });
            
            function openCoverCropper(file) {
                if (!coverCropperImg || !coverCropperModal) return;
                coverError && (coverError.textContent = '');
                var reader = new FileReader();
                reader.onload = function(e) {
                    coverCropperImg.src = e.target.result;
                    coverCropperModalInstance = new bootstrap.Modal(coverCropperModal);
                    coverCropperModalInstance.show();
                };
                reader.readAsDataURL(file);
            }
            
            function validateCoverImage(file) {
                if (!coverError) return false;
                coverError.textContent = '';
                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
                    coverError.textContent = 'الرجاء اختيار صورة بصيغة jpg, png, gif, أو webp';
                    return false;
                }
                if (file.size > 5 * 1024 * 1024) {
                    coverError.textContent = 'الحد الأقصى لحجم الصورة هو 5 ميجابايت';
                    return false;
                }
                return true;
            }
            
            if (coverDropZone && coverInput && coverPreview) {
                coverDropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    coverDropZone.classList.add('dragover');
                });
                
                coverDropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    coverDropZone.classList.remove('dragover');
                });
                
                coverDropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    coverDropZone.classList.remove('dragover');
                    var file = e.dataTransfer.files[0];
                    if (file && validateCoverImage(file)) {
                        openCoverCropper(file);
                    }
                });
                
                coverDropZone.addEventListener('click', function() {
                    coverInput.click();
                });
                
                coverInput.addEventListener('change', function() {
                    if (this.files && this.files[0] && validateCoverImage(this.files[0])) {
                        openCoverCropper(this.files[0]);
                    }
                });
            }
            
            if (coverCropperModal) {
                coverCropperModal.addEventListener('shown.bs.modal', function () {
                    if (coverCropper) coverCropper.destroy();
                    if (!coverCropperImg) return;
                    coverCropper = new Cropper(coverCropperImg, {
                        aspectRatio: 16/9,
                        viewMode: 1,
                        dragMode: 'move',
                        guides: true,
                        center: true,
                        background: false,
                        highlight: false,
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        minContainerWidth: 400,
                        minContainerHeight: 225
                    });
                });
            }
            
            cropCoverBtn && cropCoverBtn.addEventListener('click', function() {
                if (coverCropper) {
                    coverCropper.getCroppedCanvas({ width: 800, height: 450, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
                        croppedCoverBlob = blob;
                        var url = URL.createObjectURL(blob);
                        coverPreview && (coverPreview.src = url);
                        coverPreview && (coverPreview.style.display = 'block');
                        // Set a fake File in the input for form submission
                        var dt = new DataTransfer();
                        var file = new File([blob], 'cover.png', { type: 'image/png' });
                        dt.items.add(file);
                        coverInput && (coverInput.files = dt.files);
                        coverCropperModalInstance && coverCropperModalInstance.hide();
                    }, 'image/png');
                }
            });
            
            // Clean up cropper when modal is hidden
            coverCropperModal && coverCropperModal.addEventListener('hidden.bs.modal', function() {
                if (coverCropper) {
                    coverCropper.destroy();
                    coverCropper = null;
                }
                // Clean up any leftover backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
            });
            
            // Comprehensive modal backdrop cleanup
            function cleanupModalBackdrops() {
                // Remove all modal backdrops
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
                
                // Remove any inline styles that might be blocking interaction
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
            
            // Clean up on page load
            cleanupModalBackdrops();
            
            // Clean up on window focus (in case modal was left open)
            window.addEventListener('focus', cleanupModalBackdrops);
            
            // Clean up on any click outside modal
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal-backdrop')) {
                    cleanupModalBackdrops();
                }
            });
            
            // Force cleanup every 5 seconds as a safety measure
            setInterval(cleanupModalBackdrops, 5000);
            
            // Form submission
            const form = document.getElementById('newPostForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Save TinyMCE content before form submission
                    if (window.tinymce && tinymce.get('content')) {
                        tinymce.get('content').save();
                    }
                    
                    // Validate form
                    const title = document.getElementById('title').value.trim();
                    const content = document.getElementById('content').value.trim();
                    const category = document.getElementById('categoryId').value;
                    
                    if (!title) {
                        e.preventDefault();
                        alert('يرجى إدخال عنوان المقال');
                        return false;
                    }
                    
                    if (!content) {
                        e.preventDefault();
                        alert('يرجى إدخال محتوى المقال');
                        return false;
                    }
                    
                    if (!category) {
                        e.preventDefault();
                        alert('يرجى اختيار تصنيف للمقال');
                        return false;
                    }
                });
            }
            
            // Check if TinyMCE loaded successfully and provide fallback
            setTimeout(function() {
                if (!window.tinymce || !tinymce.get('content')) {
                    console.log('TinyMCE not loaded, using enhanced textarea');
                    const contentTextarea = document.getElementById('content');
                    if (contentTextarea) {
                        contentTextarea.style.display = 'block';
                        contentTextarea.style.minHeight = '300px';
                        contentTextarea.style.fontSize = '1.1rem';
                        contentTextarea.style.lineHeight = '1.6';
                        contentTextarea.style.fontFamily = 'Tajawal, Arial, sans-serif';
                        contentTextarea.style.padding = '1rem';
                        contentTextarea.style.border = '2px solid var(--border-color)';
                        contentTextarea.style.borderRadius = 'var(--border-radius-lg)';
                        contentTextarea.style.resize = 'vertical';
                    }
                }
            }, 2000);
        });
    </script>
</body>
</html> 