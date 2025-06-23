<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/Auth.php';
require_once '../config/firebase-config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['uid', 'email', 'provider'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $uid = $input['uid'];
    $email = $input['email'];
    $displayName = $input['displayName'] ?? '';
    $photoURL = $input['photoURL'] ?? '';
    $provider = $input['provider'];
    
    // Initialize Auth class
    $auth = new Auth();
    
    // Check if user already exists
    $existingUser = $auth->getUserByEmail($email);
    
    if ($existingUser) {
        // User exists, log them in
        $auth->loginWithSocial($uid, $email, $provider);
        echo json_encode([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $existingUser
        ]);
    } else {
        // Create new user
        $userData = [
            'uid' => $uid,
            'email' => $email,
            'firstName' => $displayName ? explode(' ', $displayName)[0] : '',
            'lastName' => $displayName ? implode(' ', array_slice(explode(' ', $displayName), 1)) : '',
            'photoURL' => $photoURL,
            'provider' => $provider,
            'joinDate' => date('Y-m-d H:i:s'),
            'isActive' => true
        ];
        
        $newUser = $auth->createSocialUser($userData);
        
        if ($newUser) {
            echo json_encode([
                'success' => true,
                'message' => 'تم إنشاء الحساب وتسجيل الدخول بنجاح',
                'user' => $newUser
            ]);
        } else {
            throw new Exception('فشل في إنشاء الحساب');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 