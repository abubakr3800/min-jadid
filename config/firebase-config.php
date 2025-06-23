<?php
// Firebase Configuration
define('FIREBASE_API_KEY', 'AIzaSyB7LgxJHT33r0v7Spjq6b2GwufGo99pYSc');
define('FIREBASE_AUTH_DOMAIN', 'min-jaded.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'min-jaded');
define('FIREBASE_STORAGE_BUCKET', 'min-jaded.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '302914558220');
define('FIREBASE_APP_ID', '1:302914558220:web:314e0ef56785315f229129');
define('MEASUREMENT_ID', 'G-TJEBM71DEE');

// Database configuration (using JSON files for now, can be replaced with Firebase)
define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');
define('POSTS_FILE', DATA_DIR . 'posts.json');
define('CATEGORIES_FILE', DATA_DIR . 'categories.json');
define('RATINGS_FILE', DATA_DIR . 'ratings.json');

// Ensure data directory exists
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Firebase configuration
require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use GuzzleHttp\Client;

class FirebaseConfig {
    private $firebase;
    private $auth;
    private $databaseUrl;
    private $serviceAccount;
    private $httpClient;
    private $debugLog;
    
    public function __construct() {
        // Path to your service account file
        $this->serviceAccount = __DIR__ . '/firebase-credentials.json';
        // Your Firebase Realtime Database URL (no trailing slash)
        $this->databaseUrl = 'https://min-jaded-default-rtdb.firebaseio.com';
        $this->debugLog = __DIR__ . '/firebase_debug.log';

        $this->firebase = (new Factory)
            ->withServiceAccount($this->serviceAccount);
        $this->auth = $this->firebase->createAuth();
        $this->httpClient = new Client();
        
        // Log initialization
        $this->log("FirebaseConfig initialized with database URL: " . $this->databaseUrl);
    }
    
    private function log($message) {
        $logEntry = date('Y-m-d H:i:s') . " - " . $message . "\n";
        file_put_contents($this->debugLog, $logEntry, FILE_APPEND);
    }
    
    public function getAuth() {
        return $this->auth;
    }
    
    // REST API for Realtime Database
    public function dbGet($path) {
        try {
            $url = rtrim($this->databaseUrl, '/') . '/' . ltrim($path, '/') . '.json';
            $this->log("dbGet: " . $url);
            $response = $this->httpClient->get($url);
            $result = json_decode($response->getBody(), true);
            $this->log("dbGet result: " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            $this->log("dbGet error: " . $e->getMessage());
            return null;
        }
    }
    
    public function dbSet($path, $data) {
        try {
            $url = rtrim($this->databaseUrl, '/') . '/' . ltrim($path, '/') . '.json';
            $this->log("dbSet: " . $url . " with data: " . json_encode($data));
            $response = $this->httpClient->put($url, [
                'json' => $data
            ]);
            $result = json_decode($response->getBody(), true);
            $this->log("dbSet result: " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            $this->log("dbSet error: " . $e->getMessage());
            return null;
        }
    }
    
    public function dbPush($path, $data) {
        try {
            $url = rtrim($this->databaseUrl, '/') . '/' . ltrim($path, '/') . '.json';
            $this->log("dbPush: " . $url . " with data: " . json_encode($data));
            $response = $this->httpClient->post($url, [
                'json' => $data
            ]);
            $result = json_decode($response->getBody(), true);
            $this->log("dbPush result: " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            $this->log("dbPush error: " . $e->getMessage());
            return null;
        }
    }
    
    public function dbUpdate($path, $data) {
        try {
            $url = rtrim($this->databaseUrl, '/') . '/' . ltrim($path, '/') . '.json';
            $this->log("dbUpdate: " . $url . " with data: " . json_encode($data));
            $response = $this->httpClient->patch($url, [
                'json' => $data
            ]);
            $result = json_decode($response->getBody(), true);
            $this->log("dbUpdate result: " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            $this->log("dbUpdate error: " . $e->getMessage());
            return null;
        }
    }
    
    public function dbDelete($path) {
        try {
            $url = rtrim($this->databaseUrl, '/') . '/' . ltrim($path, '/') . '.json';
            $this->log("dbDelete: " . $url);
            $response = $this->httpClient->delete($url);
            $result = json_decode($response->getBody(), true);
            $this->log("dbDelete result: " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            $this->log("dbDelete error: " . $e->getMessage());
            return null;
        }
    }
    
    // User authentication methods
    public function createUser($email, $password, $displayName) {
        try {
            $this->log("createUser: " . $email . " - " . $displayName);
            $userProperties = [
                'email' => $email,
                'password' => $password,
                'displayName' => $displayName
            ];
            
            $user = $this->auth->createUser($userProperties);
            $this->log("createUser success: " . $user->uid);
            return $user;
        } catch (Exception $e) {
            $this->log("createUser error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public function signInUser($email, $password) {
        try {
            $this->log("signInUser: " . $email);
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            $this->log("signInUser success: " . $signInResult->data()['localId']);
            return $signInResult;
        } catch (Exception $e) {
            $this->log("signInUser error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    // Database operations for posts
    public function createPost($userId, $title, $content, $category, $tags = []) {
        try {
            $this->log("createPost: " . $userId . " - " . $title);
            $postData = [
                'userId' => $userId,
                'title' => $title,
                'content' => $content,
                'category' => $category,
                'tags' => $tags,
                'createdAt' => time(),
                'updatedAt' => time(),
                'views' => 0,
                'rating' => 0,
                'ratingCount' => 0,
                'likes' => 0,
                'comments' => []
            ];
            
            $result = $this->dbPush('posts', $postData);
            $this->log("createPost result: " . json_encode($result));
            return $result['name'];
        } catch (Exception $e) {
            $this->log("createPost error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPosts($limit = 10, $category = null) {
        try {
            $this->log("getPosts: limit=" . $limit . ", category=" . $category);
            $query = 'posts';
            
            if ($category) {
                $query .= '?orderBy="category"&equalTo="' . $category . '"';
            } else {
                $query .= '?orderBy="createdAt"';
            }
            
            $posts = $this->dbGet($query);
            $this->log("getPosts result count: " . ($posts ? count($posts) : 0));
            return $posts ? array_reverse($posts) : [];
        } catch (Exception $e) {
            $this->log("getPosts error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPost($postId) {
        try {
            $this->log("getPost: " . $postId);
            $post = $this->dbGet('posts/' . $postId);
            
            if ($post) {
                // Get comments for this post
                $comments = $this->dbGet('posts/' . $postId . '/comments') ?: [];
                $post['comments'] = $comments;
                
                // Get likes count
                $likes = $this->dbGet('posts/' . $postId . '/likes') ?: 0;
                $post['likes'] = $likes;
                
                // Get views count
                $views = $this->dbGet('posts/' . $postId . '/views') ?: 0;
                $post['views'] = $views;
                
                $this->log("getPost result: found with " . count($comments) . " comments, " . $likes . " likes, " . $views . " views");
            } else {
                $this->log("getPost result: not found");
            }
            
            return $post;
        } catch (Exception $e) {
            $this->log("getPost error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    public function updatePostViews($postId) {
        try {
            $this->log("updatePostViews: " . $postId);
            $currentViews = $this->dbGet('posts/' . $postId . '/views') ?: 0;
            $newViews = $currentViews + 1;
            $this->dbSet('posts/' . $postId . '/views', $newViews);
            $this->log("updatePostViews: updated to " . $newViews);
            return true;
        } catch (Exception $e) {
            $this->log("updatePostViews error: " . $e->getMessage());
            return false;
        }
    }
    
    public function ratePost($postId, $userId, $rating) {
        try {
            $this->log("ratePost: " . $postId . " by " . $userId . " with rating " . $rating);
            $ratingsRef = 'post_ratings/' . $postId . '/' . $userId;
            $userRating = $this->dbGet($ratingsRef);
            
            if ($userRating) {
                // Update existing rating
                $this->dbSet($ratingsRef, $rating);
                $this->log("ratePost: updated existing rating");
            } else {
                // Add new rating
                $this->dbSet($ratingsRef, $rating);
                $this->log("ratePost: added new rating");
            }
            
            // Calculate new average rating
            $allRatings = $this->dbGet('post_ratings/' . $postId) ?: [];
            $totalRating = array_sum($allRatings);
            $ratingCount = count($allRatings);
            $averageRating = $ratingCount > 0 ? round($totalRating / $ratingCount, 1) : 0;
            
            // Update post with new rating
            $this->dbSet('posts/' . $postId . '/rating', $averageRating);
            $this->dbSet('posts/' . $postId . '/ratingCount', $ratingCount);
            
            $this->log("ratePost: updated post rating to " . $averageRating . " with " . $ratingCount . " ratings");
            return true;
        } catch (Exception $e) {
            $this->log("ratePost error: " . $e->getMessage());
            return false;
        }
    }
    
    public function likePost($postId, $userId) {
        try {
            $this->log("likePost: " . $postId . " by " . $userId);
            $likesRef = 'post_likes/' . $postId . '/' . $userId;
            $userLike = $this->dbGet($likesRef);
            
            if ($userLike) {
                // Unlike
                $this->dbDelete($likesRef);
                $this->updatePostLikes($postId, -1);
                $this->log("likePost: unliked");
                return ['action' => 'unliked'];
            } else {
                // Like
                $this->dbSet($likesRef, true);
                $this->updatePostLikes($postId, 1);
                $this->log("likePost: liked");
                return ['action' => 'liked'];
            }
        } catch (Exception $e) {
            $this->log("likePost error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    private function updatePostLikes($postId, $increment) {
        try {
            $this->log("updatePostLikes: " . $postId . " increment " . $increment);
            $currentLikes = $this->dbGet('posts/' . $postId . '/likes') ?: 0;
            $newLikes = max(0, $currentLikes + $increment);
            $this->dbSet('posts/' . $postId . '/likes', $newLikes);
            $this->log("updatePostLikes: updated to " . $newLikes);
        } catch (Exception $e) {
            $this->log("updatePostLikes error: " . $e->getMessage());
        }
    }
    
    public function addComment($postId, $userId, $comment) {
        try {
            $this->log("addComment: " . $postId . " by " . $userId);
            $commentData = [
                'userId' => $userId,
                'comment' => $comment,
                'createdAt' => time()
            ];
            
            $result = $this->dbPush('posts/' . $postId . '/comments', $commentData);
            $this->log("addComment result: " . json_encode($result));
            return true;
        } catch (Exception $e) {
            $this->log("addComment error: " . $e->getMessage());
            return false;
        }
    }
    
    // User profile operations
    public function getUserProfile($userId) {
        try {
            $this->log("getUserProfile: " . $userId);
            $user = $this->dbGet('users/' . $userId);
            $this->log("getUserProfile result: " . ($user ? "found" : "not found"));
            return $user;
        } catch (Exception $e) {
            $this->log("getUserProfile error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateUserProfile($userId, $profileData) {
        try {
            $this->log("updateUserProfile: " . $userId);
            $result = $this->dbSet('users/' . $userId, $profileData);
            $this->log("updateUserProfile result: " . json_encode($result));
            return true;
        } catch (Exception $e) {
            $this->log("updateUserProfile error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserPosts($userId) {
        try {
            $this->log("getUserPosts: " . $userId);
            $posts = $this->dbGet('posts?orderBy="userId"&equalTo="' . $userId . '"');
            $this->log("getUserPosts result count: " . ($posts ? count($posts) : 0));
            return $posts ? array_reverse($posts) : [];
        } catch (Exception $e) {
            $this->log("getUserPosts error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPostRatings($postId) {
        $this->log("getPostRatings: $postId");
        $result = $this->dbGet("post_ratings/$postId");
        $this->log("getPostRatings result: " . json_encode($result));
        return $result ?: [];
    }
    
    public function getCategoryById($categoryId) {
        $this->log("getCategoryById: $categoryId");
        $result = $this->dbGet("categories/$categoryId");
        $this->log("getCategoryById result: " . ($result ? "found" : "not found"));
        return $result;
    }
    
    public function incrementPostViews($postId) {
        try {
            $this->log("incrementPostViews: " . $postId);
            $post = $this->getPost($postId);
            if ($post) {
                $post['views'] = ($post['views'] ?? 0) + 1;
                $this->dbUpdate('posts/' . $postId, $post);
                $this->log("incrementPostViews success: " . $post['views']);
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->log("incrementPostViews error: " . $e->getMessage());
            return false;
        }
    }
    
    // Password reset methods
    public function sendPasswordResetEmail($email) {
        try {
            $this->log("sendPasswordResetEmail: " . $email);
            
            // Configure action code settings for password reset
            $actionCodeSettings = [
                'url' => 'https://min-jaded.firebaseapp.com/reset-password.html',
                'handleCodeInApp' => true
            ];
            
            $this->auth->sendPasswordResetLink($email, $actionCodeSettings);
            $this->log("sendPasswordResetEmail success for: " . $email);
            return true;
        } catch (Exception $e) {
            $this->log("sendPasswordResetEmail error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function confirmPasswordReset($oobCode, $newPassword) {
        try {
            $this->log("confirmPasswordReset with oobCode: " . substr($oobCode, 0, 10) . "...");
            
            $email = $this->auth->confirmPasswordReset($oobCode, $newPassword);
            $this->log("confirmPasswordReset success for: " . $email);
            return $email;
        } catch (Exception $e) {
            $this->log("confirmPasswordReset error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function verifyPasswordResetCode($oobCode) {
        try {
            $this->log("verifyPasswordResetCode with oobCode: " . substr($oobCode, 0, 10) . "...");
            
            $email = $this->auth->verifyPasswordResetCode($oobCode);
            $this->log("verifyPasswordResetCode success for: " . $email);
            return $email;
        } catch (Exception $e) {
            $this->log("verifyPasswordResetCode error: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 