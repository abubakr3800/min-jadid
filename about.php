<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();

// Platform statistics
$posts = json_decode(file_get_contents('data/posts.json'), true);
$users = json_decode(file_get_contents('data/users.json'), true);
$categories = json_decode(file_get_contents('data/categories.json'), true);

// Count unique authors
$authorIds = [];
foreach ($posts as $post) {
    if (!empty($post['userId'])) {
        $authorIds[$post['userId']] = true;
    }
}
$authorsCount = count($authorIds);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - عن المنصة</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="تعرف على منصة من جديد - منصة لمشاركة المقالات والأفكار الملهمة">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
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
            <!-- قسم الترحيب -->
            <section class="hero-section bg-primary-light fade-in">
                <div class="container text-center">
                    <h1 class="hero-title typewriter">عن منصة من جديد</h1>
                    <p class="hero-subtitle fade-in-up">منصة عربية ملهمة لمشاركة الأفكار والاقتباسات والتطوير الشخصي</p>
                </div>
            </section>

            <!-- قسم الرؤية والرسالة -->
            <section class="section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="about-content">
                                <h2 class="section-title">رؤيتنا</h2>
                                <p class="section-text">
                                    نسعى لأن نكون المنصة العربية الأولى في مجال مشاركة الأفكار الملهمة والتطوير الشخصي، 
                                    حيث نجمع مجتمعاً من المفكرين والكتاب والمهنيين لتبادل المعرفة والخبرات.
                                </p>
                                <p class="section-text">
                                    نؤمن بقوة الكلمة المكتوبة وقدرتها على تغيير حياة الناس وإلهامهم لتحقيق أهدافهم.
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="about-content">
                                <h2 class="section-title">رسالتنا</h2>
                                <p class="section-text">
                                    توفير مساحة آمنة ومحفزة لمشاركة الأفكار والاقتباسات الملهمة، 
                                    وبناء مجتمع داعم يساعد الأفراد على تطوير أنفسهم وتحقيق إمكاناتهم الكاملة.
                                </p>
                                <p class="section-text">
                                    نسعى لتعزيز الثقافة العربية وإحياء التراث الأدبي والفكري من خلال المحتوى المعاصر.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم القيم -->
            <section class="section bg-light">
                <div class="container">
                    <div class="section-header text-center">
                        <h2 class="section-title">قيمنا</h2>
                        <p class="section-subtitle">القيم التي نؤمن بها ونسعى لتحقيقها</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="value-card text-center">
                                <div class="value-icon bg-primary-light p-md rounded-full mx-auto mb-md">
                                    <i class="fas fa-lightbulb text-primary font-xl"></i>
                                </div>
                                <h3>الإبداع والابتكار</h3>
                                <p>نشجع الأفكار الإبداعية والابتكارية ونوفر منصة للتعبير عنها بحرية</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="value-card text-center">
                                <div class="value-icon bg-secondary-light p-md rounded-full mx-auto mb-md">
                                    <i class="fas fa-hands-helping text-secondary font-xl"></i>
                                </div>
                                <h3>التعاون والدعم</h3>
                                <p>نؤمن بقوة التعاون ونسعى لبناء مجتمع داعم ومتعاون</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="value-card text-center">
                                <div class="value-icon bg-accent-light p-md rounded-full mx-auto mb-md">
                                    <i class="fas fa-graduation-cap text-accent font-xl"></i>
                                </div>
                                <h3>التعلم المستمر</h3>
                                <p>نشجع على التعلم المستمر والتطوير الشخصي والمهني</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم الإحصائيات -->
            <section class="section">
                <div class="container">
                    <div class="section-header text-center">
                        <h2 class="section-title">إحصائيات المنصة</h2>
                        <p class="section-subtitle">أرقام تعكس نمو وتطور منصتنا</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?= count($posts) ?></div>
                                <div class="stat-label">مقال منشور</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?= count($users) ?></div>
                                <div class="stat-label">مستخدم نشط</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?= $authorsCount ?></div>
                                <div class="stat-label">كاتب مشارك</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card text-center">
                                <div class="stat-number"><?= count($categories) ?></div>
                                <div class="stat-label">تصنيفات رئيسية</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم الفريق -->
            <section class="section bg-light">
                <div class="container">
                    <div class="section-header text-center">
                        <h2 class="section-title">فريق العمل</h2>
                        <p class="section-subtitle">تعرف على الفريق الذي يقف وراء نجاح المنصة</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="team-card text-center">
                                <div class="team-avatar mb-md">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>أحمد محمد</h3>
                                <p class="team-role">المؤسس والمدير التنفيذي</p>
                                <p class="team-bio">خبير في مجال التطوير الشخصي والمهني مع خبرة 10 سنوات</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="team-card text-center">
                                <div class="team-avatar mb-md">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>سارة أحمد</h3>
                                <p class="team-role">مديرة المحتوى</p>
                                <p class="team-bio">كاتبة محترفة ومحررة محتوى مع شغف باللغة العربية</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="team-card text-center">
                                <div class="team-avatar mb-md">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>محمد علي</h3>
                                <p class="team-role">مدير التطوير التقني</p>
                                <p class="team-bio">مطور ويب محترف مع خبرة في بناء المنصات الرقمية</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم الانضمام -->
            <section class="section cta-section bg-primary">
                <div class="container text-center">
                    <h2 class="text-white mb-md">انضم إلى مجتمع من جديد</h2>
                    <p class="text-white mb-lg">كن جزءاً من مجتمعنا الملهم وابدأ رحلتك في مشاركة الإبداع والمعرفة</p>
                    <?php if (!$isLoggedIn): ?>
                        <a href="signup.php" class="btn btn-light">
                            <i class="fas fa-user-plus"></i>
                            إنشاء حساب مجاني
                        </a>
                    <?php else: ?>
                        <a href="new-post.php" class="btn btn-light">
                            <i class="fas fa-plus"></i>
                            نشر مقال جديد
                        </a>
                    <?php endif; ?>
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