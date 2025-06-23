<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/Auth.php';
require_once '../includes/Database.php';

$auth = new Auth();
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $uid = $input['uid'] ?? '';
    $email = $input['email'] ?? '';
    $firstName = $input['firstName'] ?? '';
    $lastName = $input['lastName'] ?? '';
    $photoURL = $input['photoURL'] ?? '';
    $provider = $input['provider'] ?? 'google';
    
    if (empty($uid) || empty($email)) {
        throw new Exception('Missing required fields');
    }
    
    // Check if user already exists
    $existingUser = $db->getUserByEmail($email);
    
    if ($existingUser) {
        // User exists, log them in
        $userData = $existingUser;
        unset($userData['password']);
        
        // Start session and store user data
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = $userData;
        $_SESSION['logged_in'] = true;
        $_SESSION['auth_provider'] = $provider;
        $_SESSION['firebase_uid'] = $uid;
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $userData,
            'action' => 'login'
        ]);
    } else {
        // Create new user
        $userData = [
            'id' => $uid, // Use Firebase UID as user ID
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => '', // No password for OAuth users
            'linkedin' => '',
            'bio' => '',
            'avatar' => $photoURL,
            'createdAt' => date('Y-m-d'),
            'authProvider' => $provider,
            'firebaseUid' => $uid
        ];
        
        $newUser = $db->addUser($userData);
        
        if ($newUser) {
            // Start session and store user data
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = $newUser;
            $_SESSION['logged_in'] = true;
            $_SESSION['auth_provider'] = $provider;
            $_SESSION['firebase_uid'] = $uid;
            
            echo json_encode([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'user' => $newUser,
                'action' => 'register'
            ]);
        } else {
            throw new Exception('فشل في إنشاء الحساب');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 