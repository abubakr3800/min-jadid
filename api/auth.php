<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/firebase-config.php';

$firebase = new FirebaseConfig();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'forgot-password':
        handleForgotPassword();
        break;
    case 'reset-password':
        handleResetPassword();
        break;
    case 'verify-reset-code':
        handleVerifyResetCode();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleRegister() {
    global $firebase;
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    $displayName = $firstName . ' ' . $lastName;
    $result = $firebase->createUser($email, $password, $displayName);
    
    if (is_array($result) && isset($result['error'])) {
        http_response_code(500);
        echo json_encode(['error' => $result['error']]);
        return;
    }
    
    // Create user profile in database
    $userId = $result->uid;
    $profileData = [
        'id' => $userId,
        'email' => $email,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'bio' => $bio,
        'linkedin' => $linkedin,
        'createdAt' => time(),
        'postsCount' => 0,
        'totalViews' => 0,
        'totalLikes' => 0
    ];
    $firebase->updateUserProfile($userId, $profileData);
    
    // Start session and store user data
    session_start();
    $_SESSION['user'] = $profileData;
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => $profileData
    ]);
}

function handleLogin() {
    global $firebase;
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        return;
    }
    
    $result = $firebase->signInUser($email, $password);
    
    if (is_array($result) && isset($result['error'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }
    
    $userId = $result->data()['localId'];
    $userProfile = $firebase->getUserProfile($userId);
    // If userProfile is missing or incomplete, update it
    if (!$userProfile || empty($userProfile['email'])) {
        // You may want to fetch firstName/lastName from your own DB or ask user to update profile
        $userProfile = [
            'id' => $userId,
            'email' => $email,
            'firstName' => '',
            'lastName' => '',
            'bio' => '',
            'linkedin' => '',
            'createdAt' => time(),
            'postsCount' => 0,
            'totalViews' => 0,
            'totalLikes' => 0
        ];
        $firebase->updateUserProfile($userId, $userProfile);
    }
    // Start session and store user data
    session_start();
    $_SESSION['user'] = $userProfile;
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => $userProfile
    ]);
}

function handleLogout() {
    session_start();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
}

function handleForgotPassword() {
    global $firebase;
    
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email is required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    try {
        // Send password reset email using Firebase
        $firebase->sendPasswordResetEmail($email);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'حدث خطأ أثناء إرسال رابط إعادة تعيين كلمة المرور',
            'details' => $e->getMessage()
        ]);
    }
}

function handleResetPassword() {
    global $firebase;
    
    $oobCode = $_POST['oobCode'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    
    if (empty($oobCode) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['error' => 'Reset code and new password are required']);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    try {
        // Confirm password reset using Firebase
        $email = $firebase->confirmPasswordReset($oobCode, $newPassword);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
            'email' => $email
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => 'رمز إعادة التعيين غير صحيح أو منتهي الصلاحية',
            'details' => $e->getMessage()
        ]);
    }
}

function handleVerifyResetCode() {
    global $firebase;
    
    $oobCode = $_POST['oobCode'] ?? '';
    
    if (empty($oobCode)) {
        http_response_code(400);
        echo json_encode(['error' => 'Reset code is required']);
        return;
    }
    
    try {
        // Verify the password reset code using Firebase
        $email = $firebase->verifyPasswordResetCode($oobCode);
        
        echo json_encode([
            'success' => true,
            'message' => 'رمز إعادة التعيين صحيح',
            'email' => $email
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => 'رمز إعادة التعيين غير صحيح أو منتهي الصلاحية',
            'details' => $e->getMessage()
        ]);
    }
}
?> 