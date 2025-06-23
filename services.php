<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - خدماتنا</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="خدمات منصة من جديد - إنشاء السيرة الذاتية، مراجعة المحتوى، والاستشارات">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <style>
        /* Enhanced Service Styles */
        .service-section {
            padding: var(--spacing-3xl) 0;
            position: relative;
            overflow: hidden;
        }

        .service-section:nth-child(even) {
            background: linear-gradient(135deg, var(--light) 0%, #f8fafc 100%);
        }

        .service-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(124, 58, 237, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .service-content {
            padding: var(--spacing-2xl);
            position: relative;
            z-index: 2;
        }

        .service-icon {
            font-size: 4rem;
            margin-bottom: var(--spacing-lg);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }

        .service-title {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            margin-bottom: var(--spacing-lg);
            color: var(--text-darker);
            line-height: var(--line-height-tight);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .service-description {
            font-size: var(--font-size-lg);
            line-height: var(--line-height-relaxed);
            color: var(--text-muted);
            margin-bottom: var(--spacing-2xl);
        }

        .service-features {
            list-style: none;
            padding: 0;
            margin-bottom: var(--spacing-2xl);
        }

        .service-features li {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-base);
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--white);
            border-radius: var(--border-radius-md);
            transition: var(--transition-normal);
            box-shadow: var(--shadow-sm);
        }

        .service-features li:hover {
            background: var(--primary-lighter);
            transform: translateX(-4px);
            box-shadow: var(--shadow-md);
        }

        .service-features i {
            font-size: var(--font-size-sm);
            color: var(--success-color);
            background: var(--accent-light);
            padding: var(--spacing-xs);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-pricing {
            margin-bottom: var(--spacing-2xl);
            text-align: center;
            padding: var(--spacing-xl);
            background: linear-gradient(135deg, var(--primary-lighter) 0%, var(--secondary-light) 100%);
            border-radius: var(--border-radius-lg);
            border: 2px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .service-pricing::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .service-pricing:hover::before {
            left: 100%;
        }

        .price {
            font-size: var(--font-size-4xl);
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
            line-height: 1;
        }

        .price-note {
            color: var(--text-muted);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }

        .service-image {
            text-align: center;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 2;
        }

        .service-image .placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, var(--primary-lighter) 0%, var(--secondary-light) 100%);
            border-radius: var(--border-radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6rem;
            color: var(--primary-color);
            box-shadow: var(--shadow-xl);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .service-image .placeholder::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), transparent, rgba(255,255,255,0.1));
            transform: translateX(-100%) rotate(45deg);
            transition: transform 0.6s ease;
        }

        .service-image:hover .placeholder::before {
            transform: translateX(100%) rotate(45deg);
        }

        .service-image:hover .placeholder {
            transform: scale(1.05);
            box-shadow: var(--shadow-2xl);
        }

        /* Enhanced Feature Cards */
        .feature-card {
            background: var(--white);
            padding: var(--spacing-2xl);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            height: 100%;
            border: 1px solid var(--border-color);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: var(--spacing-lg);
            transition: var(--transition-normal);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h3 {
            font-size: var(--font-size-xl);
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            color: var(--text-darker);
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: var(--line-height-relaxed);
            margin-bottom: 0;
        }

        /* Enhanced CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="%23ffffff" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .cta-section .container {
            position: relative;
            z-index: 2;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .service-content {
                padding: var(--spacing-lg);
            }
            
            .service-title {
                font-size: var(--font-size-2xl);
            }
            
            .service-image .placeholder {
                height: 300px;
                font-size: 4rem;
            }
            
            .feature-card {
                margin-bottom: var(--spacing-lg);
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
                    <li><a href="services.php" class="nav-link active">خدماتنا</a></li>
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
                    <h1 class="hero-title typewriter">خدماتنا الاحترافية</h1>
                    <p class="hero-subtitle fade-in-up">نقدم مجموعة متنوعة من الخدمات لمساعدتك في تطوير مسيرتك المهنية والشخصية</p>
                </div>
            </section>

            <!-- قسم الخدمات -->
            <section class="section">
                <div class="container">
                    <!-- خدمة إنشاء السيرة الذاتية -->
                    <div id="cv" class="service-section mb-xl">
                        <div class="row items-center">
                            <div class="col-lg-6">
                                <div class="service-content">
                                    <div class="service-icon mb-md">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <h2 class="service-title">إنشاء السيرة الذاتية</h2>
                                    <p class="service-description">
                                        نساعدك في إنشاء سيرة ذاتية احترافية ومميزة تعكس خبراتك ومهاراتك بشكل مثالي. 
                                        نستخدم أحدث المعايير العالمية لضمان أن سيرتك الذاتية تجذب انتباه أصحاب العمل.
                                    </p>
                                    <ul class="service-features">
                                        <li><i class="fas fa-check"></i> تصميم احترافي ومميز</li>
                                        <li><i class="fas fa-check"></i> صياغة محتوى قوي ومقنع</li>
                                        <li><i class="fas fa-check"></i> تحسين محركات البحث (ATS)</li>
                                        <li><i class="fas fa-check"></i> مراجعة وتعديل مجاني</li>
                                        <li><i class="fas fa-check"></i> إرشادات للمقابلات</li>
                                    </ul>
                                    <div class="service-pricing">
                                        <div class="price">200 جنيه مصري</div>
                                        <div class="price-note">شامل التصميم والمحتوى والمراجعة</div>
                                    </div>
                                    <a href="contact.php?service=cv" class="btn btn-primary">
                                        <i class="fas fa-envelope"></i>
                                        طلب الخدمة
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="service-image">
                                    <div class="placeholder">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- خدمة مراجعة المحتوى -->
                    <div id="review" class="service-section mb-xl">
                        <div class="row items-center">
                            <div class="col-lg-6 order-lg-2">
                                <div class="service-content">
                                    <div class="service-icon mb-md">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <h2 class="service-title">مراجعة المحتوى</h2>
                                    <p class="service-description">
                                        نقدم خدمة مراجعة وتحرير شاملة للمحتوى المكتوب. سواء كان مقالاً، تقريراً، 
                                        أو أي محتوى آخر، نساعدك في تحسين الجودة والوضوح والاحترافية.
                                    </p>
                                    <ul class="service-features">
                                        <li><i class="fas fa-check"></i> مراجعة لغوية شاملة</li>
                                        <li><i class="fas fa-check"></i> تحسين البنية والتنظيم</li>
                                        <li><i class="fas fa-check"></i> تصحيح الأخطاء الإملائية والنحوية</li>
                                        <li><i class="fas fa-check"></i> تحسين الأسلوب والوضوح</li>
                                        <li><i class="fas fa-check"></i> اقتراحات للتحسين</li>
                                    </ul>
                                    <div class="service-pricing">
                                        <div class="price">150 جنيه مصري</div>
                                        <div class="price-note">لكل 1000 كلمة</div>
                                    </div>
                                    <a href="contact.php?service=review" class="btn btn-secondary">
                                        <i class="fas fa-envelope"></i>
                                        طلب الخدمة
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6 order-lg-1">
                                <div class="service-image">
                                    <div class="placeholder">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- خدمة الاستشارات -->
                    <div id="consultation" class="service-section">
                        <div class="row items-center">
                            <div class="col-lg-6">
                                <div class="service-content">
                                    <div class="service-icon mb-md">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h2 class="service-title">الاستشارات المهنية</h2>
                                    <p class="service-description">
                                        نقدم استشارات متخصصة في مجالات التطوير الشخصي والمهني. 
                                        نساعدك في تحديد أهدافك، تطوير مهاراتك، والتخطيط لمستقبلك المهني.
                                    </p>
                                    <ul class="service-features">
                                        <li><i class="fas fa-check"></i> تحديد الأهداف المهنية</li>
                                        <li><i class="fas fa-check"></i> تطوير المهارات الشخصية</li>
                                        <li><i class="fas fa-check"></i> التخطيط الاستراتيجي للمسيرة</li>
                                        <li><i class="fas fa-check"></i> نصائح للمقابلات الوظيفية</li>
                                        <li><i class="fas fa-check"></i> حل المشاكل المهنية</li>
                                    </ul>
                                    <div class="service-pricing">
                                        <div class="price">300 جنيه مصري</div>
                                        <div class="price-note">للساعة الواحدة</div>
                                    </div>
                                    <a href="contact.php?service=consultation" class="btn btn-accent">
                                        <i class="fas fa-envelope"></i>
                                        طلب الخدمة
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="service-image">
                                    <div class="placeholder">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم المميزات -->
            <section class="section bg-light">
                <div class="container">
                    <div class="section-header text-center">
                        <h2 class="section-title">لماذا تختار خدماتنا؟</h2>
                        <p class="section-subtitle">نتميز بالجودة والاحترافية في جميع خدماتنا</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon bg-primary-light">
                                    <i class="fas fa-award text-primary"></i>
                                </div>
                                <h3>جودة عالية</h3>
                                <p>نلتزم بأعلى معايير الجودة في جميع خدماتنا لضمان رضاك التام</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon bg-secondary-light">
                                    <i class="fas fa-clock text-secondary"></i>
                                </div>
                                <h3>سرعة في التنفيذ</h3>
                                <p>نحترم وقتك ونلتزم بمواعيد التسليم المتفق عليها</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon bg-accent-light">
                                    <i class="fas fa-headset text-accent"></i>
                                </div>
                                <h3>دعم مستمر</h3>
                                <p>نوفر لك الدعم والمراجعة المجانية حتى تكون راضياً تماماً</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- قسم الاتصال -->
            <section class="section cta-section">
                <div class="container text-center">
                    <h2 class="text-white mb-md">هل تريد معرفة المزيد؟</h2>
                    <p class="text-white mb-lg">تواصل معنا للحصول على استشارة مجانية أو لمعرفة المزيد عن خدماتنا</p>
                    <a href="contact.php" class="btn btn-light">
                        <i class="fas fa-envelope"></i>
                        تواصل معنا
                    </a>
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