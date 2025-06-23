<?php
$currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentPort = $_SERVER['SERVER_PORT'] ?? '8000';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تصحيح مشكلة تسجيل الدخول</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn {
            background: #4285f4;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #3367d6;
        }
        
        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .warning {
            background: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffcc02;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تصحيح مشكلة تسجيل الدخول</h1>
        
        <div class="status info">
            <h3>معلومات النطاق الحالي:</h3>
            <p><strong>النطاق:</strong> <?= $currentDomain ?></p>
            <p><strong>المنفذ:</strong> <?= $currentPort ?></p>
            <p><strong>الرابط الكامل:</strong> <?= $currentDomain ?>:<?= $currentPort ?></p>
        </div>
        
        <div class="status warning">
            <h3>⚠️ مشكلة النطاق المصرح به</h3>
            <p>يبدو أن النطاق الحالي غير مصرح به في Firebase</p>
            <p>لإصلاح هذه المشكلة:</p>
            <ol style="text-align: left;">
                <li>اذهب إلى <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
                <li>اختر مشروعك: <strong>min-jaded</strong></li>
                <li>اذهب إلى <strong>Authentication</strong> → <strong>Settings</strong> → <strong>Authorized domains</strong></li>
                <li>أضف هذه النطاقات:
                    <ul>
                        <li><code><?= $currentDomain ?></code></li>
                        <li><code><?= $currentDomain ?>:<?= $currentPort ?></code></li>
                        <li><code>localhost</code></li>
                        <li><code>localhost:8000</code></li>
                        <li><code>127.0.0.1</code></li>
                        <li><code>127.0.0.1:8000</code></li>
                    </ul>
                </li>
                <li>احفظ التغييرات</li>
                <li>جرب مرة أخرى</li>
            </ol>
        </div>
        
        <div>
            <button class="btn" onclick="testFirebaseConnection()">اختبار اتصال Firebase</button>
            <button class="btn" onclick="testGoogleAuth()">اختبار Google</button>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="login.php" class="btn">العودة لصفحة تسجيل الدخول</a>
            <a href="index.php" class="btn">العودة للصفحة الرئيسية</a>
        </div>
        
        <div id="testResult" class="status info" style="display: none;">
            <h3>نتيجة الاختبار</h3>
            <div id="testContent"></div>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>

    <script>
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
        
        console.log('Firebase initialized');
        console.log('Current domain:', window.location.hostname + ':' + window.location.port);

        function showTestResult(message, type) {
            const resultDiv = document.getElementById('testResult');
            const contentDiv = document.getElementById('testContent');
            contentDiv.innerHTML = message;
            resultDiv.className = `status ${type}`;
            resultDiv.style.display = 'block';
        }

        function testFirebaseConnection() {
            showTestResult('جاري اختبار الاتصال بـ Firebase...', 'info');
            
            // Test if Firebase is properly initialized
            if (typeof firebase !== 'undefined' && firebase.app) {
                showTestResult(`
                    <h3>✅ Firebase متصل بنجاح!</h3>
                    <p><strong>النطاق الحالي:</strong> ${window.location.hostname}:${window.location.port}</p>
                    <p><strong>مشروع Firebase:</strong> ${firebaseConfig.projectId}</p>
                    <p>يمكنك الآن تجربة تسجيل الدخول عبر Google</p>
                `, 'success');
            } else {
                showTestResult(`
                    <h3>❌ فشل في الاتصال بـ Firebase</h3>
                    <p>تأكد من تحميل مكتبات Firebase بشكل صحيح</p>
                `, 'error');
            }
        }

        function testGoogleAuth() {
            showTestResult('جاري محاولة تسجيل الدخول عبر Google...', 'info');
            
            const googleProvider = new firebase.auth.GoogleAuthProvider();
            googleProvider.addScope('email');
            googleProvider.addScope('profile');

            auth.signInWithPopup(googleProvider)
                .then((result) => {
                    const user = result.user;
                    showTestResult(`
                        <h3>✅ تم تسجيل الدخول بنجاح!</h3>
                        <p><strong>الاسم:</strong> ${user.displayName || 'غير محدد'}</p>
                        <p><strong>البريد الإلكتروني:</strong> ${user.email || 'غير محدد'}</p>
                        <p><strong>معرف المستخدم:</strong> ${user.uid}</p>
                        <p>الآن يمكنك العودة إلى صفحة تسجيل الدخول الرئيسية</p>
                    `, 'success');
                })
                .catch((error) => {
                    console.error('Auth error:', error);
                    
                    if (error.code === 'auth/unauthorized-domain') {
                        showTestResult(`
                            <h3>❌ خطأ في النطاق المصرح به</h3>
                            <p><strong>الخطأ:</strong> ${error.message}</p>
                            <p><strong>النطاق الحالي:</strong> ${window.location.hostname}:${window.location.port}</p>
                            <h4>لإصلاح هذه المشكلة:</h4>
                            <ol style="text-align: left;">
                                <li>اذهب إلى <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
                                <li>اختر مشروعك: <strong>min-jaded</strong></li>
                                <li>اذهب إلى <strong>Authentication</strong> → <strong>Settings</strong> → <strong>Authorized domains</strong></li>
                                <li>أضف: <code>${window.location.hostname}</code> و <code>${window.location.hostname}:${window.location.port}</code></li>
                                <li>احفظ التغييرات</li>
                                <li>جرب مرة أخرى</li>
                            </ol>
                        `, 'error');
                    } else {
                        showTestResult(`
                            <h3>❌ خطأ في تسجيل الدخول</h3>
                            <p><strong>الخطأ:</strong> ${error.message}</p>
                            <p><strong>رمز الخطأ:</strong> ${error.code}</p>
                        `, 'error');
                    }
                });
        }
    </script>
</body>
</html> 