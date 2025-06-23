<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();

$service = $_GET['service'] ?? '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $service = $_POST['service'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } else {
        // In a real application, you would send an email or save to database
        $success = 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - اتصل بنا</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="تواصل معنا - منصة من جديد">
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
                    <?php if ($isLoggedIn): ?>
                        <li><a href="new-post.php" class="nav-link">مقال جديد</a></li>
                        <li><a href="profile.php" class="nav-link">الملف الشخصي</a></li>
                        <li><a href="logout.php" class="btn btn-outline-primary">تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">تسجيل الدخول</a></li>
                        <li><a href="signup.php" class="btn btn-primary">إنشاء حساب</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </header>

        <!-- القسم الرئيسي -->
        <main class="main-content">
            <section class="section">
                <div class="container">
                    <div class="row justify-center">
                        <div class="col-lg-8">
                            <div class="text-center mb-lg">
                                <h1 class="section-title">تواصل معنا</h1>
                                <p class="section-subtitle">نحن هنا لمساعدتك في جميع احتياجاتك</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-error mb-md">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success mb-md">
                                    <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="contact-info">
                                        <h3>معلومات التواصل</h3>
                                        <div class="contact-item">
                                            <i class="fas fa-envelope"></i>
                                            <div>
                                                <strong>البريد الإلكتروني</strong>
                                                <p>info@minjadid.com</p>
                                            </div>
                                        </div>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <div>
                                                <strong>الهاتف</strong>
                                                <p>+966 50 123 4567</p>
                                            </div>
                                        </div>
                                        <div class="contact-item">
                                            <i class="fas fa-clock"></i>
                                            <div>
                                                <strong>ساعات العمل</strong>
                                                <p>الأحد - الخميس: 9 ص - 6 م</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h3>أرسل لنا رسالة</h3>
                                            <form method="POST" class="contact-form">
                                                <div class="form-group">
                                                    <label for="name" class="form-label">الاسم الكامل</label>
                                                    <input type="text" id="name" name="name" class="form-control" 
                                                           placeholder="أدخل اسمك الكامل" required 
                                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="email" class="form-label">البريد الإلكتروني</label>
                                                    <input type="email" id="email" name="email" class="form-control" 
                                                           placeholder="أدخل بريدك الإلكتروني" required 
                                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="service" class="form-label">الخدمة المطلوبة</label>
                                                    <select id="service" name="service" class="form-control">
                                                        <option value="">اختر الخدمة</option>
                                                        <option value="cv" <?= $service === 'cv' ? 'selected' : '' ?>>إنشاء السيرة الذاتية</option>
                                                        <option value="review" <?= $service === 'review' ? 'selected' : '' ?>>مراجعة المحتوى</option>
                                                        <option value="consultation" <?= $service === 'consultation' ? 'selected' : '' ?>>الاستشارات المهنية</option>
                                                        <option value="other">خدمة أخرى</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="message" class="form-label">الرسالة</label>
                                                    <textarea id="message" name="message" class="form-control" rows="5" 
                                                              placeholder="اكتب رسالتك هنا..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                                </div>
                                                
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fas fa-paper-plane"></i>
                                                    إرسال الرسالة
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- تذييل الصفحة -->
        <footer class="footer">
            <div class="container">
                <div class="footer-bottom">
                    <p>&copy; 2025 من جديد. جميع الحقوق محفوظة.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- سكريبت JavaScript -->
    <script src="js/ui.js"></script>
    <script src="js/animations.js"></script>
</body>
</html> 