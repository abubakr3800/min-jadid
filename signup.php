<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $bio = $_POST['bio'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
    } elseif ($password !== $confirmPassword) {
        $error = 'كلمة المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } else {
        $userData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'linkedin' => $linkedin,
            'bio' => $bio
        ];
        
        $result = $auth->register($userData);
        
        if ($result['success']) {
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - إنشاء حساب</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="description" content="إنشاء حساب جديد في منصة من جديد - منصة لمشاركة الاقتباسات والأفكار الملهمة">
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            min-height: 100vh;
            font-family: 'Cairo', 'Tajawal', Arial, sans-serif;
        }
        .signup-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .signup-container {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 8px 40px rgba(106,130,251,0.15);
            padding: 3.2rem 2.2rem 2.4rem 2.2rem;
            max-width: 440px;
            width: 100%;
            margin: 2rem auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.2rem;
        }
        .signup-logo img {
            width: 140px;
            height: 140px;
            border-radius: 0;
            box-shadow: 0 2px 16px rgba(106,130,251,0.13);
            margin-bottom: 2rem;
            background: #f3f4f6;
            padding: 0;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .signup-title {
            color: #2d2e83;
            font-size: 2.1rem;
            font-weight: 900;
            margin-bottom: 0.7rem;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .signup-subtitle {
            color: #6366f1;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .signup-body {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .form-group {
            margin-bottom: 1.1rem;
            position: relative;
        }
        .form-label {
            font-weight: 700;
            color: #2d2e83;
            margin-bottom: 0.4rem;
            display: block;
            font-size: 1.05rem;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-control {
            border-radius: 16px;
            border: 1.5px solid #e0e7ff;
            padding: 1.1rem 2.7rem 1.1rem 1rem;
            font-size: 1.13rem;
            transition: border 0.2s, box-shadow 0.2s;
            width: 100%;
            background: #f8fafc;
            box-shadow: none;
            color: #22223b;
        }
        .form-control:focus {
            border-color: #6a82fb;
            box-shadow: 0 2px 12px #6a82fb22;
            background: #fff;
            outline: none;
        }
        .input-icon, .input-group .input-icon {
            color: #b1b6e3;
            font-size: 1.25rem;
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: color 0.2s;
        }
        .form-control:focus ~ .input-icon {
            color: #6a82fb;
        }
        .password-toggle {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b1b6e3;
            cursor: pointer;
            font-size: 1.18rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .form-control:focus ~ .password-toggle {
            color: #6a82fb;
        }
        .password-toggle:hover {
            background: #f3f4f6;
            color: #fc5c7d;
        }
        .btn-signup, .btn-social {
            background: linear-gradient(90deg, #2d2e83 0%, #6a82fb 100%);
            color: #fff;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.15rem;
            box-shadow: 0 2px 8px rgba(106,130,251,0.10);
            transition: 0.2s;
            border: none;
            padding: 1rem 0;
            margin-top: 0.2rem;
            width: 100%;
            letter-spacing: 0.5px;
        }
        .btn-signup:hover, .btn-social:hover {
            background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .btn-signup:active {
            background: #2d2e83;
            color: #fff;
            transform: scale(0.98);
        }
        .btn-social {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .social-auth {
            margin-bottom: 1.2rem;
        }
        .text-muted {
            color: #6b7280;
        }
        .text-primary {
            color: #6366f1;
        }
        .alert {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        @media (max-width: 600px) {
            .signup-container {
                padding: 1.2rem 0.3rem 1.2rem 0.3rem;
                max-width: 98vw;
            }
            .signup-logo img {
                width: 70px;
                height: 70px;
            }
            .signup-title {
                font-size: 1.3rem;
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
                    <li><a href="login.php" class="nav-link">تسجيل الدخول</a></li>
                    <li><a href="signup.php" class="btn btn-primary active">إنشاء حساب</a></li>
                </ul>
            </div>
        </header>
        <div class="signup-page">
            <div class="signup-container">
                <div class="signup-header">
                    <div class="signup-logo">
                        <img src="img/logo.svg" alt="شعار من جديد" style="width: 70px; height: 70px;">
                    </div>
                    <h1 class="signup-title">انضم إلينا</h1>
                    <p class="signup-subtitle">ابدأ رحلتك في مشاركة الإلهام والمعرفة</p>
                </div>
                <div class="signup-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="signup-form" id="signupForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName" class="form-label">الاسم الأول</label>
                                <div class="input-group">
                                    <input type="text" id="firstName" name="firstName" class="form-control" placeholder="أدخل اسمك الأول" required value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>">
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastName" class="form-label">اسم العائلة</label>
                                <div class="input-group">
                                    <input type="text" id="lastName" name="lastName" class="form-control" placeholder="أدخل اسم العائلة" required value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>">
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <input type="email" id="email" name="email" class="form-control" placeholder="أدخل بريدك الإلكتروني" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="passwordToggle" aria-label="إظهار/إخفاء كلمة المرور">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">يجب أن تحتوي كلمة المرور على 6 أحرف على الأقل</div>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">تأكيد كلمة المرور</label>
                            <div class="input-group">
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="أعد إدخال كلمة المرور" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="confirmPasswordToggle" aria-label="إظهار/إخفاء كلمة المرور">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="linkedin" class="form-label">رابط LinkedIn (اختياري)</label>
                            <div class="input-group">
                                <input type="url" id="linkedin" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/username" value="<?= htmlspecialchars($_POST['linkedin'] ?? '') ?>">
                                <i class="fab fa-linkedin input-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bio" class="form-label">نبذة عنك (اختياري)</label>
                            <div class="input-group">
                                <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="اكتب نبذة مختصرة عن نفسك"><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                                <i class="fas fa-info-circle input-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="agreeTerms" name="agreeTerms" class="form-check-input" required>
                                <label for="agreeTerms" class="form-check-label">
                                    أوافق على <a href="#" class="text-primary">الشروط والأحكام</a> و <a href="#" class="text-primary">سياسة الخصوصية</a>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-signup"><span>إنشاء الحساب</span></button>
                        </div>
                    </form>
                    <div class="auth-separator"><span>أو</span></div>
                    <div class="social-auth">
                        <button class="btn-social btn-google-signup" id="googleSignUpBtn">
                            <i class="fab fa-google"></i>
                            <span>إنشاء حساب باستخدام Google</span>
                        </button>
                    </div>
                    <div class="auth-footer">
                        <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/google-auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            });
            const form = document.querySelector('.signup-form');
            const submitBtn = form.querySelector('.btn-signup');
            form.addEventListener('submit', function(e) {
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value.trim();
                const confirmPassword = document.getElementById('confirmPassword').value.trim();
                const agreeTerms = document.getElementById('agreeTerms').checked;
                if (!firstName || !lastName || !email || !password || !confirmPassword) {
                    e.preventDefault();
                    alert('يرجى ملء جميع الحقول المطلوبة');
                    return;
                }
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('كلمة المرور غير متطابقة');
                    return;
                }
                if (password.length < 6) {
                    e.preventDefault();
                    alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                    return;
                }
                if (!agreeTerms) {
                    e.preventDefault();
                    alert('يجب الموافقة على الشروط والأحكام');
                    return;
                }
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري إنشاء الحساب...';
                submitBtn.disabled = true;
            });
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });

        // Navbar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.getElementById('navbarToggler');
            const navbarNav = document.getElementById('navbarNav');
            if (navbarToggler && navbarNav) {
                navbarToggler.addEventListener('click', function() {
                    navbarNav.classList.toggle('show');
                });
                document.addEventListener('click', function(event) {
                    if (!navbarToggler.contains(event.target) && !navbarNav.contains(event.target)) {
                        navbarNav.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html> 