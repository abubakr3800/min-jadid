<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Login user
    public function login($email, $password) {
        $user = $this->db->getUserByEmail($email);
        
        if ($user && $user['password'] === $password) {
            // Store user data in session (without password)
            $userData = $user;
            unset($userData['password']);
            $_SESSION['user'] = $userData;
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'user' => $userData
            ];
        }
        
        return [
            'success' => false,
            'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
        ];
    }
    
    // Register new user
    public function register($userData) {
        // Check if email already exists
        if ($this->db->getUserByEmail($userData['email'])) {
            return [
                'success' => false,
                'message' => 'البريد الإلكتروني مستخدم بالفعل'
            ];
        }
        
        // Add user to database
        $newUser = $this->db->addUser($userData);
        
        if ($newUser) {
            // Store user data in session (without password)
            $userData = $newUser;
            unset($userData['password']);
            $_SESSION['user'] = $userData;
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'user' => $userData
            ];
        }
        
        return [
            'success' => false,
            'message' => 'حدث خطأ أثناء إنشاء الحساب'
        ];
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ];
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Get current user
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
    
    // Get current user ID
    public function getCurrentUserId() {
        $user = $this->getCurrentUser();
        return $user ? $user['id'] : null;
    }
    
    // Require authentication (redirect if not logged in)
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /min/login.php');
            exit();
        }
    }
    
    // Login with Firebase UID (for social auth)
    public function loginWithFirebaseUid($firebaseUid) {
        $user = $this->db->getUserByFirebaseUid($firebaseUid);
        
        if ($user) {
            // Store user data in session (without password)
            $userData = $user;
            unset($userData['password']);
            $_SESSION['user'] = $userData;
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'user' => $userData
            ];
        }
        
        return [
            'success' => false,
            'message' => 'لم يتم العثور على المستخدم'
        ];
    }
    
    // Optional authentication (don't redirect, just return status)
    public function optionalAuth() {
        return $this->isLoggedIn();
    }
}
?> 