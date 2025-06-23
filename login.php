<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Redirect to homepage after successful login
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Check if user is already logged in
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
    <title>من جديد - تسجيل الدخول</title>
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
    <meta name="description" content="تسجيل الدخول إلى منصة من جديد - منصة لمشاركة الاقتباسات والأفكار الملهمة">
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            min-height: 100vh;
            font-family: 'Cairo', 'Tajawal', Arial, sans-serif;
        }
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .login-container {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 8px 40px rgba(106,130,251,0.15);
            padding: 3.2rem 2.2rem 2.4rem 2.2rem;
            max-width: 420px;
            width: 100%;
            margin: 2rem auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.2rem;
        }
        .login-logo img {
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
        .login-title {
            color: #2d2e83;
            font-size: 2.1rem;
            font-weight: 900;
            margin-bottom: 0.7rem;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .login-subtitle {
            color: #6366f1;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .login-body {
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
        .btn-primary, .btn-social {
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
        .btn-primary:hover, .btn-social:hover {
            background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .btn-primary:active {
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
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        @media (max-width: 600px) {
            .login-container {
                padding: 1.2rem 0.3rem 1.2rem 0.3rem;
                max-width: 98vw;
            }
            .login-logo img {
                width: 70px;
                height: 70px;
            }
            .login-title {
                font-size: 1.3rem;
            }
        }
        .btn-signup-navbar {
            background: linear-gradient(90deg, #fc5c7d 0%, #6a82fb 100%);
            color: #fff !important;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.05rem;
            padding: 0.5rem 1.2rem;
            margin-right: 0.5rem;
            margin-left: 0.5rem;
            transition: 0.2s;
            border: none;
            box-shadow: 0 2px 8px rgba(106,130,251,0.10);
            display: inline-block;
        }
        .btn-signup-navbar:hover, .btn-signup-navbar:focus {
            background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%);
            color: #fff !important;
            text-decoration: none;
            transform: translateY(-2px) scale(1.04);
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
                    <li><a href="login.php" class="nav-link active">تسجيل الدخول</a></li>
                    <li><a href="signup.php" class="btn btn-signup-navbar">إنشاء حساب</a></li>
                </ul>
            </div>
        </header>
        <div class="login-page">
            <div class="login-container">
                <div class="login-header">
                    <div class="login-logo">
                        <img src="img/logo.svg" alt="شعار من جديد" style="width: 60px; height: 60px;">
                    </div>
                    <div class="login-title">تسجيل الدخول</div>
                    <div class="login-subtitle">مرحباً بعودتك إلى منصة من جديد</div>
                </div>
                <div class="login-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom:1.5rem;">
                            <i class="fas fa-check-circle"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" autocomplete="on">
                        <div class="form-group">
                            <label class="form-label" for="email">البريد الإلكتروني</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com" required autofocus>
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-label-group">
                                <label class="form-label" for="password">كلمة المرور</label>
                                <a href="pages/forgot-password.html" class="small" style="color:var(--primary-color);text-decoration:underline;">نسيت كلمة المرور؟</a>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="إظهار/إخفاء كلمة المرور" onclick="togglePassword()"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group text-center" style="margin-top:2rem;">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-4 mb-2">
                        <span class="text-muted">أو يمكنك تسجيل الدخول عبر</span>
                    </div>
                    <div class="social-auth">
                        <button type="button" class="btn-social btn-google-signin" onclick="signInWithGoogle()">
                            <i class="fab fa-google"></i>
                            تسجيل الدخول عبر Google
                        </button>
                        <button type="button" class="btn-social btn-facebook-signin" onclick="signInWithFacebook()">
                            <i class="fab fa-facebook-f"></i>
                            تسجيل الدخول عبر Facebook
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <span>ليس لديك حساب؟</span>
                        <a href="signup.php" class="text-primary">إنشاء حساب جديد</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function togglePassword() {
        var input = document.getElementById('password');
        var icon = document.querySelector('.password-toggle i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyB7LgxJHT33r0v7Spjq6b2GwufGo99pYSc",
        authDomain: "min-jaded.firebaseapp.com",
        projectId: "min-jaded",
        storageBucket: "min-jaded.firebasestorage.app",
        messagingSenderId: "302914558220",
        appId: "1:302914558220:web:314e0ef56785315f229129",
        measurementId: "G-TJEBM71DEE"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();
    
    console.log('Firebase initialized successfully');
    console.log('Current domain:', window.location.hostname + ':' + window.location.port);
    
    // Check if we're on localhost and show helpful message
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('Running on localhost - make sure this domain is authorized in Firebase Console');
        console.log('Go to: Firebase Console > Authentication > Settings > Authorized domains');
        console.log('Add: localhost, localhost:8000, 127.0.0.1, 127.0.0.1:8000');
        
        // Get current port for the simple test page link
        const currentPort = window.location.port || '80';
        const simpleTestUrl = `http://localhost:${currentPort}/simple-test.html`;
        
        // Add a note to the page about the domain issue
        const noteDiv = document.createElement('div');
        noteDiv.className = 'alert alert-info';
        noteDiv.innerHTML = `
            <i class="fas fa-info-circle"></i>
            <strong>ملاحظة:</strong> إذا واجهت مشكلة في تسجيل الدخول عبر Google أو Facebook، 
            <a href="${simpleTestUrl}" class="btn btn-primary btn-sm">استخدم صفحة الاختبار البسيط</a>
            <br><br>
            <strong>للاختبار السريع:</strong> 
            <a href="${simpleTestUrl}" style="color: #007bff; text-decoration: underline;">اضغط هنا للذهاب مباشرة لصفحة الاختبار البسيط</a>
        `;
        
        const loginBody = document.querySelector('.login-body');
        const form = loginBody.querySelector('form');
        // loginBody.insertBefore(noteDiv, form);
    } else if (window.location.hostname === 'min-jaded.ct.ws') {
        console.log('Running on online domain: min-jaded.ct.ws');
        console.log('This should work perfectly with Firebase social authentication!');
        
        // Add a success note for online domain
        const noteDiv = document.createElement('div');
        noteDiv.className = 'alert alert-success';
        noteDiv.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <strong>ممتاز!</strong> أنت تستخدم النطاق الإلكتروني min-jaded.ct.ws
            <br>تسجيل الدخول الاجتماعي يجب أن يعمل بشكل مثالي!
        `;
        
        const loginBody = document.querySelector('.login-body');
        const form = loginBody.querySelector('form');
        loginBody.insertBefore(noteDiv, form);
    }

    // Listen for auth state changes
    auth.onAuthStateChanged((user) => {
        if (user) {
            console.log('User is signed in:', user);
        } else {
            console.log('User is signed out');
        }
    });

    // Google Auth Provider
    const googleProvider = new firebase.auth.GoogleAuthProvider();
    googleProvider.addScope('email');
    googleProvider.addScope('profile');

    // Facebook Auth Provider
    const facebookProvider = new firebase.auth.FacebookAuthProvider();
    facebookProvider.addScope('email');
    facebookProvider.addScope('public_profile');

    // Handle Google Sign In
    function signInWithGoogle() {
        // First try the main login page
        auth.signInWithPopup(googleProvider)
            .then((result) => {
                const user = result.user;
                console.log('Google sign in successful:', user);
                
                // Send user data to backend
                sendSocialAuthData(user, 'google');
            })
            .catch((error) => {
                console.error('Google sign in error:', error);
                
                if (error.code === 'auth/unauthorized-domain') {
                    if (window.location.hostname === 'min-jaded.ct.ws') {
                        showError(`
                            <strong>مشكلة في النطاق المصرح به</strong><br>
                            النطاق الحالي: ${window.location.hostname}<br>
                            تأكد من إضافة النطاق في Firebase Console
                        `);
                    } else {
                        // Automatically redirect to simple test page for localhost
                        const currentPort = window.location.port || '80';
                        const simpleTestUrl = `http://localhost:${currentPort}/simple-test.html`;
                        showError(`
                            <strong>سيتم توجيهك لصفحة الاختبار البسيط</strong><br>
                            النطاق الحالي: ${window.location.hostname}:${window.location.port}<br>
                            <a href="${simpleTestUrl}" class="btn btn-primary btn-sm">اذهب الآن</a>
                        `);
                        
                        // Auto redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = simpleTestUrl;
                        }, 3000);
                    }
                } else {
                    showError('فشل في تسجيل الدخول عبر Google: ' + error.message);
                }
            });
    }

    // Handle Facebook Sign In
    function signInWithFacebook() {
        // First try the main login page
        auth.signInWithPopup(facebookProvider)
            .then((result) => {
                const user = result.user;
                console.log('Facebook sign in successful:', user);
                
                // Send user data to backend
                sendSocialAuthData(user, 'facebook');
            })
            .catch((error) => {
                console.error('Facebook sign in error:', error);
                
                if (error.code === 'auth/unauthorized-domain') {
                    if (window.location.hostname === 'min-jaded.ct.ws') {
                        showError(`
                            <strong>مشكلة في النطاق المصرح به</strong><br>
                            النطاق الحالي: ${window.location.hostname}<br>
                            تأكد من إضافة النطاق في Firebase Console
                        `);
                    } else {
                        // Automatically redirect to simple test page for localhost
                        const currentPort = window.location.port || '80';
                        const simpleTestUrl = `http://localhost:${currentPort}/simple-test.html`;
                        showError(`
                            <strong>سيتم توجيهك لصفحة الاختبار البسيط</strong><br>
                            النطاق الحالي: ${window.location.hostname}:${window.location.port}<br>
                            <a href="${simpleTestUrl}" class="btn btn-primary btn-sm">اذهب الآن</a>
                        `);
                        
                        // Auto redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = simpleTestUrl;
                        }, 3000);
                    }
                } else {
                    showError('فشل في تسجيل الدخول عبر Facebook: ' + error.message);
                }
            });
    }

    // Send social auth data to backend
    function sendSocialAuthData(user, provider) {
        const userData = {
            uid: user.uid,
            email: user.email,
            displayName: user.displayName,
            photoURL: user.photoURL,
            provider: provider
        };

        fetch('api/social-auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('تم تسجيل الدخول بنجاح!');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                showError(data.error || 'فشل في تسجيل الدخول');
            }
        })
        .catch(error => {
            console.error('Error sending social auth data:', error);
            showError('حدث خطأ أثناء تسجيل الدخول');
        });
    }

    // Show error message
    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        
        const loginBody = document.querySelector('.login-body');
        const form = loginBody.querySelector('form');
        loginBody.insertBefore(alertDiv, form);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Show success message
    function showSuccess(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success';
        alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        
        const loginBody = document.querySelector('.login-body');
        const form = loginBody.querySelector('form');
        loginBody.insertBefore(alertDiv, form);
    }

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