<?php
session_start();
// var_dump($_SESSION);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// Get post ID from URL
$postId = $_GET['id'] ?? null;

if (!$postId) {
    header('Location: index.php');
    exit();
}

// Get post details from local database
$post = $db->getPostById($postId);

if (!$post) {
    header('Location: index.php');
    exit();
}

// Increment view count
$db->incrementViews($postId);

// Refresh post data to get updated view count
$post = $db->getPostById($postId);

// Get category details
$categoryId = $post['categoryId'] ?? null;
$category = $categoryId ? $db->getCategoryById($categoryId) : null;

// Get post ratings
$ratings = $db->getPostRatings($postId);

// Get user profile if logged in
$userProfile = $isLoggedIn ? $db->getUserById($currentUser['id']) : null;

// Get author profile
$authorProfile = $db->getUserById($post['userId']);
if ($isLoggedIn && $post['userId'] == $currentUser['id']) {
    $authorProfile = $currentUser;
}

// Handle rating submission
$ratingError = '';
$ratingSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$isLoggedIn) {
        $ratingError = 'يجب تسجيل الدخول لتقييم المقال';
    } else {
        $rating = (int)$_POST['rating'];
        if ($rating >= 1 && $rating <= 5) {
            $ratingData = [
                'userId' => $currentUser['id'],
                'postId' => $postId,
                'rating' => $rating
            ];
            $result = $db->updateRating($ratingData);
            if ($result) {
                $ratingSuccess = 'تم إضافة تقييمك بنجاح';
                // Refresh post data to get updated ratings
                $post = $db->getPostById($postId);
                $ratings = $db->getPostRatings($postId);
            } else {
                $ratingError = 'حدث خطأ أثناء إضافة التقييم';
            }
        } else {
            $ratingError = 'التقييم يجب أن يكون بين 1 و 5';
        }
    }
}

// Increment view count (simplified for now)
// In a real application, you'd want to track unique views
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - من جديد</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Standard favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <!-- PNG icons for better device support -->
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <!-- Optional: SVG favicon for modern browsers -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <!-- Optional: Apple Touch Icon for iOS -->
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <meta name="description" content="<?= htmlspecialchars(substr($post['content'], 0, 160)) ?>">
    <style>
        /* Post Page Specific Styles */
        .post-page {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .post-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .post-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .post-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .post-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            background-color: #000a;
        }
        
        .post-category {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .post-category i {
            margin-left: 0.5rem;
        }
        
        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .post-meta-item i {
            color: rgba(255,255,255,0.8);
        }
        
        .post-content-wrapper {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: -2rem auto 2rem;
            position: relative;
            z-index: 10;
            overflow: hidden;
        }
        
        .post-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            padding: 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .stat-icon.views { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.rating { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.likes { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.comments { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        
        .post-content {
            padding: 3rem;
            line-height: 1.8;
            font-size: 1.1rem;
            color: var(--text-dark);
        }
        
        .article-content {
            padding: 3rem;
            line-height: 1.8;
            font-size: 1.1rem;
            color: var(--text-dark);
            background: white;
            border-radius: 0 0 20px 20px;
            overflow-wrap: break-word;
            word-wrap: break-word;
            hyphens: auto;
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
            text-indent: 2rem;
        }
        
        .article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6 {
            color: var(--text-dark);
            margin: 2rem 0 1rem;
            font-weight: 600;
            line-height: 1.4;
        }
        
        .article-content h1 { font-size: 2rem; }
        .article-content h2 { font-size: 1.75rem; }
        .article-content h3 { font-size: 1.5rem; }
        .article-content h4 { font-size: 1.25rem; }
        
        .article-content ul, .article-content ol {
            margin: 1.5rem 0;
            padding-right: 2rem;
        }
        
        .article-content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }
        
        .article-content blockquote {
            border-right: 4px solid var(--primary-color);
            padding: 1rem 2rem;
            margin: 2rem 0;
            background: #f8f9fa;
            border-radius: 8px;
            font-style: italic;
            color: var(--text-muted);
        }
        
        .article-content code {
            background: #f1f3f4;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .article-content pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5rem 0;
            border: 1px solid #e9ecef;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .article-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .article-content th, .article-content td {
            padding: 0.75rem;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }
        
        .article-content th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .article-content tr:hover {
            background: #f8f9fa;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }
        
        .post-content h2, .post-content h3, .post-content h4 {
            color: var(--text-dark);
            margin: 2rem 0 1rem;
            font-weight: 600;
        }
        
        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 2rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .tag {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: transform 0.3s ease;
        }
        
        .tag:hover {
            transform: scale(1.05);
        }
        
        .post-actions {
            padding: 2rem 3rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffecd2, #fcb69f);
            color: #d97706;
            border-color: #f59e0b;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            color: #dc2626;
            border-color: #ef4444;
        }
        
        .author-section {
            padding: 3rem;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-top: 1px solid #e9ecef;
        }
        
        .author-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .author-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        
        .author-info p {
            color: var(--text-muted);
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .comments-section {
            padding: 3rem;
            background: white;
        }
        
        .comments-section h3 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
            text-align: center;
        }
        
        .comment-form {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 3rem;
        }
        
        .comment-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .comment-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .comment {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .comment-content {
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .sidebar {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .sidebar-widget {
            margin-bottom: 2rem;
        }
        
        .widget-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
        
        .similar-post-item {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .similar-post-item:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
        }
        
        .similar-post-item h4 {
            margin-bottom: 0.5rem;
        }
        
        .similar-post-item a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
        }
        
        .similar-post-item a:hover {
            color: var(--primary-color);
        }
        
        .similar-post-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            gap: 1rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .post-title {
                font-size: 2rem;
            }
            
            .post-meta {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .post-stats {
                grid-template-columns: repeat(2, 1fr);
                padding: 1.5rem;
            }
            
            .post-content {
                padding: 2rem 1.5rem;
            }
            
            .post-actions {
                padding: 1.5rem;
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .author-card {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .comments-section {
                padding: 2rem 1.5rem;
            }
            
            .sidebar {
                margin-top: 2rem;
                position: static;
            }
        }
        
        @media (max-width: 480px) {
            .post-hero {
                padding: 2rem 0 1rem;
            }
            
            .post-title {
                font-size: 1.5rem;
            }
            
            .post-stats {
                grid-template-columns: 1fr;
            }
            
            .post-content {
                padding: 1.5rem 1rem;
                font-size: 1rem;
            }
            
            .post-actions {
                padding: 1rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }
        
        .close:hover {
            color: var(--text-dark);
        }
        
        .rating-stars {
            display: flex;
            gap: 0.5rem;
            margin: 2rem 0;
            justify-content: center;
        }
        
        .rating-stars i {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: all 0.3s ease;
        }
        
        .rating-stars i.active {
            color: #ffc107;
            transform: scale(1.1);
        }
        
        .rating-stars i:hover,
        .rating-stars i:hover ~ i {
            color: #ffc107;
        }
        
        /* Delete Post Modal */
        .modal-dialog {
            max-width: 500px;
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            border-bottom: 1px solid #e9ecef;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-body {
            padding: 2rem;
            text-align: center;
        }
        
        .modal-footer {
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 16px 16px;
            padding: 1.5rem;
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-right: 4px solid var(--primary-color);
            animation: toastSlideIn 0.3s ease;
        }
        
        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .toast.success {
            border-right-color: #28a745;
        }
        
        .toast.error {
            border-right-color: #dc3545;
        }
        
        .toast.warning {
            border-right-color: #ffc107;
        }
        
        /* Additional Mobile Improvements */
        @media (max-width: 767.98px) {
            /* Improve overall mobile scrolling */
            html, body {
                -webkit-overflow-scrolling: touch !important;
                scroll-behavior: smooth !important;
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
            
            /* Prevent horizontal scroll on all containers */
            .container, .container-fluid {
                max-width: 100% !important;
                overflow-x: hidden !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            /* Fix post content wrapper */
            .post-content-wrapper {
                margin: -1rem 1rem 2rem !important;
                border-radius: 16px !important;
                overflow: hidden !important;
            }
            
            /* Fix post hero section */
            .post-hero {
                padding: 2rem 1rem 1rem !important;
            }
            
            .post-title {
                font-size: 1.75rem !important;
                line-height: 1.3 !important;
                margin-bottom: 1rem !important;
            }
            
            .post-meta {
                flex-direction: column !important;
                gap: 0.75rem !important;
                align-items: flex-start !important;
                padding: 1rem !important;
            }
            
            /* Fix post stats */
            .post-stats {
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
                padding: 1.5rem !important;
            }
            
            .stat-item {
                padding: 0.75rem !important;
                font-size: 0.9rem !important;
            }
            
            /* Fix post actions */
            .post-actions {
                padding: 1.5rem !important;
                flex-direction: column !important;
                gap: 1rem !important;
            }
            
            .action-buttons {
                width: 100% !important;
                justify-content: center !important;
            }
            
            .btn {
                padding: 0.75rem 1rem !important;
                font-size: 0.9rem !important;
            }
            
            /* Fix author section */
            .author-section {
                padding: 2rem 1.5rem !important;
            }
            
            .author-card {
                flex-direction: column !important;
                text-align: center !important;
                gap: 1rem !important;
                padding: 1.5rem !important;
            }
            
            /* Fix comments section */
            .comments-section {
                padding: 2rem 1.5rem !important;
            }
            
            /* Fix sidebar */
            .sidebar {
                margin-top: 2rem !important;
                padding: 0 1rem !important;
            }
            
            /* Ensure all content is properly contained */
            * {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            
            /* Fix any potential overflow issues */
            .article-content * {
                overflow-wrap: break-word !important;
                word-wrap: break-word !important;
                hyphens: auto !important;
            }
            
            /* Improve navbar dropdown scrolling */
            .navbar-nav.show {
                -webkit-overflow-scrolling: touch !important;
                overscroll-behavior: contain !important;
            }
            
            /* Prevent scroll on page when menu is open */
            body.navbar-open {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
            }
            
            /* Improve touch targets */
            .navbar-nav .nav-link,
            .navbar-nav .btn {
                min-height: 44px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            /* Better spacing for mobile */
            .navbar-nav .nav-link {
                padding: 1rem 1.5rem !important;
                margin: 0 !important;
            }
            
            .navbar-nav .btn {
                margin: 0.5rem 1.5rem !important;
                padding: 0.75rem 1.5rem !important;
            }
            
            /* Improve navbar positioning */
            .navbar {
                position: sticky !important;
                top: 0 !important;
                z-index: 1000 !important;
                background: white !important;
            }
            
            /* Better dropdown positioning */
            .navbar-nav {
                position: fixed !important;
                top: 60px !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                max-height: calc(100vh - 60px) !important;
                background: white !important;
                z-index: 999 !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
        }
        
        @media (max-width: 480px) {
            .navbar-nav {
                top: 50px !important;
                max-height: calc(100vh - 50px) !important;
            }
            
            .navbar-nav .nav-link {
                padding: 1rem !important;
                font-size: 0.875rem !important;
            }
            
            .navbar-nav .btn {
                margin: 0.25rem 0.5rem !important;
                padding: 0.75rem 1rem !important;
                font-size: 0.875rem !important;
            }
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus States */
        .btn:focus,
        .comment-input:focus,
        .rating-stars i:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Print Styles */
        @media print {
            .post-actions,
            .comments-section,
            .sidebar,
            .navbar,
            .footer {
                display: none !important;
            }
            
            .post-content-wrapper {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .post-hero {
                background: white !important;
                color: black !important;
            }
        }
        
        /* Navbar Mobile Styles */
        @media (max-width: 767.98px) {
            .navbar {
                padding: 0.5rem 1rem !important;
                position: relative !important;
            }
            
            .navbar .container {
                padding: 0.5rem 1rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            
            .navbar-brand {
                font-size: 1rem !important;
                flex: 1 !important;
                display: flex !important;
                align-items: center !important;
            }
            
            .navbar-brand img {
                height: 28px !important;
                margin-left: 0.5rem !important;
            }
            
            .navbar-nav {
                display: none !important;
                flex-direction: column !important;
                position: fixed !important;
                top: 60px !important;
                right: 0 !important;
                left: 0 !important;
                background-color: #ffffff !important;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
                padding: 0 !important;
                z-index: 999 !important;
                border-radius: 0 0 12px 12px !important;
                border-top: 1px solid #f4f4f5 !important;
                max-height: calc(100vh - 60px) !important;
                overflow-y: auto !important;
                margin: 0 !important;
                list-style: none !important;
                -webkit-overflow-scrolling: touch !important;
            }
            
            .navbar-nav.show {
                display: flex !important;
                animation: slideDown 0.3s ease !important;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .navbar-nav li {
                width: 100% !important;
                margin: 0 !important;
                border-bottom: 1px solid #f4f4f5 !important;
                list-style: none !important;
            }
            
            .navbar-nav li:last-child {
                border-bottom: none !important;
            }
            
            .navbar-nav .nav-link {
                display: block !important;
                padding: 1rem 1.5rem !important;
                width: 100% !important;
                text-align: center !important;
                border-radius: 0 !important;
                transition: all 0.3s ease !important;
                font-weight: 500 !important;
                color: #52525b !important;
                text-decoration: none !important;
            }
            
            .navbar-nav .nav-link:hover {
                background: #f5f3ff !important;
                color: #6366f1 !important;
                transform: translateX(-5px) !important;
            }
            
            .navbar-nav .btn {
                margin: 0.5rem 1.5rem !important;
                width: calc(100% - 3rem) !important;
                justify-content: center !important;
                border-radius: 0.75rem !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.5rem !important;
                padding: 0.75rem 1.5rem !important;
                font-size: 1rem !important;
                font-weight: 600 !important;
                text-decoration: none !important;
                border: 2px solid transparent !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
            }
            
            .navbar-nav .btn-primary {
                background: #6366f1 !important;
                color: #ffffff !important;
                border-color: #6366f1 !important;
            }
            
            .navbar-nav .btn-outline-primary {
                background: transparent !important;
                color: #6366f1 !important;
                border-color: #6366f1 !important;
            }
            
            .navbar-toggler {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: none !important;
                border: 2px solid #e4e4e7 !important;
                font-size: 1.125rem !important;
                color: #27272a !important;
                cursor: pointer !important;
                padding: 0.5rem !important;
                border-radius: 0.375rem !important;
                transition: all 0.3s ease !important;
                width: 40px !important;
                height: 40px !important;
            }
            
            .navbar-toggler:hover {
                background: #6366f1 !important;
                color: #ffffff !important;
                border-color: #6366f1 !important;
                transform: scale(1.05) !important;
            }
            
            .navbar-toggler:focus {
                outline: none !important;
                box-shadow: 0 0 0 3px #f5f3ff !important;
            }
            
            .navbar-toggler i {
                transition: transform 0.3s ease !important;
            }
            
            .navbar-toggler.active i {
                transform: rotate(180deg) !important;
            }
            
            /* Prevent body scroll when menu is open */
            body.navbar-open {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
            }
        }
        
        @media (max-width: 480px) {
            .navbar {
                padding: 0.25rem 0.5rem !important;
            }
            
            .navbar .container {
                padding: 0.25rem 0.5rem !important;
            }
            
            .navbar-brand img {
                height: 24px !important;
            }
            
            .navbar-nav {
                top: 50px !important;
                max-height: calc(100vh - 50px) !important;
            }
            
            .navbar-nav .nav-link {
                padding: 1rem !important;
                font-size: 0.875rem !important;
            }
            
            .navbar-nav .btn {
                margin: 0.25rem 0.5rem !important;
                width: calc(100% - 1rem) !important;
                padding: 0.5rem 1rem !important;
                font-size: 0.875rem !important;
            }
            
            .navbar-toggler {
                width: 36px !important;
                height: 36px !important;
                font-size: 1rem !important;
            }
        }
        
        /* Ensure desktop styles work */
        @media (min-width: 768px) {
            .navbar-nav {
                display: flex !important;
            }
            
            .navbar-toggler {
                display: none !important;
            }
        }
        
        .article-content tr:hover {
            background: #f8f9fa;
        }
        
        /* Mobile Responsive for Article Content */
        @media (max-width: 768px) {
            .article-content {
                padding: 2rem 1.5rem;
                font-size: 1rem;
                line-height: 1.7;
            }
            
            .article-content h1 { font-size: 1.75rem; }
            .article-content h2 { font-size: 1.5rem; }
            .article-content h3 { font-size: 1.25rem; }
            .article-content h4 { font-size: 1.1rem; }
            
            .article-content p {
                text-indent: 1.5rem;
                margin-bottom: 1.25rem;
            }
            
            .article-content blockquote {
                padding: 0.75rem 1.5rem;
                margin: 1.25rem 0;
                font-size: 0.95rem;
            }
            
            .article-content ul, .article-content ol {
                padding-right: 1.5rem;
                margin: 1.25rem 0;
            }
            
            .article-content li {
                margin-bottom: 0.5rem;
            }
            
            .article-content table {
            font-size: 0.9rem;
                margin: 1.25rem 0;
            }
            
            .article-content th, .article-content td {
                padding: 0.5rem;
                word-wrap: break-word;
                max-width: 200px;
            }
            
            .article-content pre {
                font-size: 0.85rem;
                padding: 0.75rem;
                margin: 1.25rem 0;
                overflow-x: auto;
                white-space: pre-wrap;
                word-wrap: break-word;
                max-width: 100%;
            }
            
            .article-content code {
                font-size: 0.9rem;
                padding: 0.25rem 0.5rem;
            }
            
            .article-content img {
                max-width: 100%;
                height: auto;
                margin: 1.25rem 0;
            }
        }
        
        @media (max-width: 480px) {
            .article-content {
                padding: 1.5rem 1rem;
                font-size: 0.95rem;
            }
            
            .article-content h1 { font-size: 1.5rem; }
            .article-content h2 { font-size: 1.3rem; }
            .article-content h3 { font-size: 1.1rem; }
            .article-content h4 { font-size: 1rem; }
            
            .article-content p {
                text-indent: 1rem;
            }
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }
        
        /* Social Media Sharing Styles */
        .social-sharing {
            width: 100%;
            text-align: center;
        }
        
        .sharing-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .sharing-buttons .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            min-width: 120px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .sharing-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .sharing-buttons .btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .sharing-buttons .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .sharing-buttons .btn:hover::before {
            left: 100%;
        }
        
        .sharing-buttons .btn i {
            margin-left: 0.5rem;
            font-size: 1.1rem;
        }
        
        /* Mobile responsive for sharing buttons */
        @media (max-width: 768px) {
            .sharing-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .sharing-buttons .btn {
                width: 200px;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .sharing-buttons .btn {
                width: 180px;
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper post-page">
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

        <!-- Hero Section -->
        <section class="post-hero">
            <div class="container">
                <div class="post-hero-content">
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                        <?php if ($category): ?>
                            <span class="post-category">
                                <i class="<?= $category['icon'] ?? 'fas fa-tag' ?>"></i>
                                <?= htmlspecialchars($category['name']) ?>
                            </span>
                        <?php endif; ?>
                        <div class="post-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= date('Y/m/d', strtotime($post['createdAt'] ?? 'now')) ?></span>
                </div>
                        <div class="post-meta-item">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars(($authorProfile['firstName'] ?? 'Unknown') . ' ' . ($authorProfile['lastName'] ?? 'Author')) ?></span>
            </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="row">
                    <!-- Article Content -->
                    <div class="col-lg-8">
                        <article class="post-content-wrapper">
            <!-- Post Stats -->
            <div class="post-stats">
                                <div class="stat-item">
                                    <div class="stat-icon views">
                    <i class="fas fa-eye"></i>
                </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?= $post['views'] ?? 0 ?></div>
                                        <div class="stat-label">مشاهدة</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon rating">
                    <i class="fas fa-star"></i>
                </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?= $post['averageRating'] ?? 0 ?></div>
                                        <div class="stat-label">تقييم</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon likes">
                    <i class="fas fa-heart"></i>
                </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?= $post['likes'] ?? 0 ?></div>
                                        <div class="stat-label">إعجاب</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon comments">
                    <i class="fas fa-comment"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?= count($db->getPostComments($postId)) ?></div>
                                        <div class="stat-label">تعليق</div>
                                    </div>
                </div>
            </div>
            
            <!-- Post Content -->
            <div class="post-content">
                <?php 
                $tags = [];
                if (!empty($post['tags']) && is_array($post['tags'])) {
                    $tags = $post['tags'];
                } elseif (!empty($post['tags']) && is_string($post['tags'])) {
                    $tags = array_filter(array_map('trim', explode(',', $post['tags'])));
                }
                ?>
                <?php if (!empty($tags)): ?>
                                    <div class="post-tags">
                        <?php foreach ($tags as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                                <div class="article-content">
                                    <!-- <?= nl2br(htmlspecialchars($post['content'])) ?> -->
                                    <?= nl2br($post['content']) ?>
                </div>
            </div>
            
            <!-- Post Actions -->
            <div class="post-actions" style="padding: 2rem 3rem; border-top: 2px dashed #eee; display: flex; gap: 1rem; align-items: center; justify-content: center; background: #f8f9fa;">
                <?php if ($isLoggedIn): ?>
                    <button type="button" class="btn btn-outline-primary" onclick="toggleLike(<?= $postId ?>)">
                        <i class="fas fa-heart <?= $db->hasUserLikedPost($currentUser['id'], $postId) ? 'text-danger' : '' ?>"></i>
                        <span id="likeCount"><?= $post['likes'] ?? 0 ?></span>
                    </button>
                <?php endif; ?>
                
                <!-- Social Media Sharing -->
                <div class="social-sharing">
                    <h4 style="margin-bottom: 1rem; text-align: center; color: var(--text-dark);">شارك المقال</h4>
                    <div class="sharing-buttons" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <button type="button" class="btn btn-primary" onclick="shareToFacebook()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white;">
                            <i class="fab fa-facebook-f"></i>
                            فيسبوك
                        </button>
                        <button type="button" class="btn btn-success" onclick="shareToWhatsApp()" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none; color: white;">
                            <i class="fab fa-whatsapp"></i>
                            واتساب
                        </button>
                        <button type="button" class="btn btn-danger" onclick="shareToInstagram()" style="background: linear-gradient(45deg, #ff6b6b 0%, #ee5a24 25%, #ff6348 50%, #ff4757 75%, #ff3838 100%); border: none; color: white;">
                            <i class="fab fa-instagram"></i>
                            انستغرام
                        </button>
                        <button type="button" class="btn btn-info" onclick="shareToTwitter()" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border: none; color: white;">
                            <i class="fab fa-twitter"></i>
                            تويتر
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Author-only Actions -->
            <?php if ($isLoggedIn && $post['userId'] == $currentUser['id']): ?>
                            <div class="post-author-actions" style="padding: 2rem 3rem; border-top: 2px dashed #eee; display: flex; gap: 1rem; align-items: center; justify-content: center; background: #f8f9fa;">
                <a href="edit-post.php?id=<?= $postId ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> تعديل المقال
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal">
                    <i class="fas fa-trash"></i> حذف المقال
                </button>
            </div>
            <?php endif; ?>
                        </article>
            
            <!-- Author Section -->
                        <section class="author-section">
                            <div class="author-card">
                    <?php if ($authorProfile): ?>
                        <div class="author-avatar">
                            <?= strtoupper(substr(($authorProfile['firstName'] ?? 'A') . ' ' . ($authorProfile['lastName'] ?? ''), 0, 1)) ?>
                        </div>
                                    <div class="author-info">
                                        <h3><?= htmlspecialchars(($authorProfile['firstName'] ?? 'Unknown') . ' ' . ($authorProfile['lastName'] ?? 'Author')) ?></h3>
                                        <p><?= htmlspecialchars($authorProfile['bio'] ?? 'لا توجد نبذة شخصية متاحة') ?></p>
                            <?php if (!empty($authorProfile['linkedin'])): ?>
                                <a href="<?= htmlspecialchars($authorProfile['linkedin']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                    <i class="fab fa-linkedin"></i> LinkedIn
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                                    <div class="author-info">
                                        <h3>مؤلف غير معروف</h3>
                                        <p>لا توجد معلومات متاحة عن المؤلف</p>
                        </div>
                    <?php endif; ?>
                </div>
                        </section>
            
            <!-- Comments Section -->
                        <section class="comments-section" id="commentsSection">
                <h3>التعليقات</h3>
                
                <?php if ($isLoggedIn): ?>
                    <div class="comment-form">
                                    <textarea id="commentInput" class="comment-input" placeholder="اكتب تعليقك هنا..." maxlength="1000"></textarea>
                                    <div style="text-align: left; margin-top: 0.5rem; color: var(--text-muted);">
                            <span id="commentCharCount">0</span>/1000
                        </div>
                        <button class="btn btn-primary" onclick="addComment()" style="margin-top: 1rem;">
                            <i class="fas fa-paper-plane"></i> إرسال التعليق
                        </button>
                    </div>
                <?php else: ?>
                                <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                                    <p>يجب <a href="login.php" style="color: var(--primary-color); font-weight: 600;">تسجيل الدخول</a> لترك تعليق.</p>
                                </div>
                <?php endif; ?>
                
                <div id="commentsList">
                    <?php
                    $comments = $db->getPostComments($postId);
                    ?>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                                <span class="comment-author"><?= htmlspecialchars($comment['user']['firstName'] . ' ' . $comment['user']['lastName']) ?></span>
                                    <span><?= date('Y/m/d H:i', strtotime($comment['createdAt'])) ?></span>
                                </div>
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                        <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>لا توجد تعليقات بعد. كن أول من يعلق!</p>
                                    </div>
                    <?php endif; ?>
                </div>
                        </section>
        </div>
        
                    <!-- Sidebar -->
        <div class="col-lg-4">
            <aside class="sidebar">
                            <!-- Similar Articles -->
                            <div class="sidebar-widget">
                    <h3 class="widget-title">مقالات مشابهة</h3>
                    <?php 
                    $similarPosts = $db->getPosts(['categoryId' => $categoryId]);
                    $similarPosts = array_filter($similarPosts, function($p) use ($postId) {
                        return $p['id'] != $postId;
                    });
                    $similarPosts = array_slice($similarPosts, 0, 3);
                    ?>
                    
                    <?php if (!empty($similarPosts)): ?>
                        <div class="similar-posts">
                            <?php foreach ($similarPosts as $similarPost): ?>
                                <div class="similar-post-item">
                                    <h4>
                                        <a href="post.php?id=<?= $similarPost['id'] ?>">
                                            <?= htmlspecialchars($similarPost['title']) ?>
                                        </a>
                                    </h4>
                                    <div class="similar-post-meta">
                                        <span class="author">
                                            <?php 
                                            $similarAuthor = $db->getUserById($similarPost['userId']);
                                            echo htmlspecialchars(($similarAuthor['firstName'] ?? 'Unknown') . ' ' . ($similarAuthor['lastName'] ?? 'Author'));
                                            ?>
                                        </span>
                                        <span class="date">
                                            <?= date('Y/m/d', strtotime($similarPost['createdAt'] ?? 'now')) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                                    <p style="color: var(--text-muted); text-align: center;">لا توجد مقالات مشابهة</p>
                    <?php endif; ?>
                </div>
                
                            <!-- Article Statistics -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">إحصائيات المقال</h3>
                    <div class="post-stats-widget">
                        <div class="stat-item">
                                        <div class="stat-icon views">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="stat-content">
                            <div class="stat-number"><?= $post['views'] ?? 0 ?></div>
                            <div class="stat-label">مشاهدة</div>
                                        </div>
                        </div>
                        <div class="stat-item">
                                        <div class="stat-icon rating">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number"><?= $post['totalRatings'] ?? 0 ?></div>
                            <div class="stat-label">تقييم</div>
                                        </div>
                        </div>
                        <div class="stat-item">
                                        <div class="stat-icon rating">
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number"><?= $post['averageRating'] ?? 0 ?></div>
                            <div class="stat-label">متوسط التقييم</div>
                                        </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
            </div>
        </main>

    <!-- Rating Modal -->
    <div id="ratingModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>تقييم المقال</h2>
            <div id="ratingStars" class="rating-stars">
                <i class="fas fa-star" data-rating="1"></i>
                <i class="fas fa-star" data-rating="2"></i>
                <i class="fas fa-star" data-rating="3"></i>
                <i class="fas fa-star" data-rating="4"></i>
                <i class="fas fa-star" data-rating="5"></i>
            </div>
            <button onclick="submitRating()" class="btn btn-primary">إرسال التقييم</button>
        </div>
    </div>

        <!-- Delete Post Modal -->
        <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deletePostModalLabel">
                            <i class="fas fa-trash"></i> تأكيد حذف المقال
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>هل أنت متأكد أنك تريد حذف هذا المقال؟</p>
                        <p class="text-muted">لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deletePost()">
                            <i class="fas fa-trash"></i> نعم، حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>

    <!-- تذييل الصفحة -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 من جديد. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- سكريبت JavaScript -->
    <script src="js/ui.js"></script>
    <script src="js/animations.js"></script>
    <script>
        let selectedRating = 0;
        const postId = '<?= $postId ?>';
        const userId = '<?= $isLoggedIn ? $currentUser['id'] : '' ?>';
        
        // Character count for comment
        document.getElementById('commentInput')?.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('commentCharCount').textContent = count;
        });
        
        // Show rating modal
        function showRatingModal() {
            const modal = document.getElementById('ratingModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }
        
        // Like post
        function likePost() {
            if (!userId) {
                    showToast('يجب تسجيل الدخول للإعجاب بالمقالات', 'warning');
                    setTimeout(() => {
                window.location.href = 'login.php';
                    }, 2000);
                return;
            }
                
                const likeBtn = document.querySelector('button[onclick="likePost()"]');
                const originalText = likeBtn.innerHTML;
                likeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري...';
                likeBtn.disabled = true;
            
            fetch('api/like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    postId: postId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                        const likesCount = document.querySelector('.stat-item .stat-number');
                    if (likesCount) {
                        likesCount.textContent = data.post.likes || 0;
                    }
                        showToast('تم الإعجاب بالمقال بنجاح', 'success');
                } else {
                        showToast('خطأ: ' + (data.error || 'فشل في الإعجاب بالمقال'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                    showToast('حدث خطأ أثناء الإعجاب بالمقال', 'error');
                })
                .finally(() => {
                    likeBtn.innerHTML = originalText;
                    likeBtn.disabled = false;
            });
        }
        
        // Submit rating
        function submitRating() {
            if (!userId) {
                alert('يجب تسجيل الدخول لتقييم المقالات.');
                window.location.href = 'login.php';
                return;
            }
            if (!selectedRating) {
                alert('يرجى اختيار تقييم');
                return;
            }
            
            fetch('api/rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    postId: postId,
                    rating: selectedRating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('خطأ: ' + data.error);
                    if (data.error && data.error.includes('تسجيل الدخول')) {
                        window.location.href = 'login.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('فشل في إرسال التقييم');
            });
            const modal = document.getElementById('ratingModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Add comment
        function addComment() {
            if (!userId) {
                    showToast('يجب تسجيل الدخول لترك تعليق', 'warning');
                    setTimeout(() => {
                window.location.href = 'login.php';
                    }, 2000);
                return;
            }
                
            const commentInput = document.getElementById('commentInput');
                const submitBtn = document.querySelector('button[onclick="addComment()"]');
                
            if (!commentInput) {
                    showToast('لم يتم العثور على حقل التعليق', 'error');
                return;
            }
                
            const comment = commentInput.value.trim();
            if (!comment) {
                    showToast('يرجى إدخال تعليق', 'warning');
                return;
            }
                
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
                submitBtn.disabled = true;
            
            fetch('api/comments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    postId: postId,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the input
                    commentInput.value = '';
                    document.getElementById('commentCharCount').textContent = '0';
                        
                        showToast('تم إضافة التعليق بنجاح', 'success');
                    
                    // Reload the page to show the new comment
                        setTimeout(() => {
                    location.reload();
                        }, 1000);
                } else {
                        showToast('خطأ: ' + (data.error || 'فشل في إضافة التعليق'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                    showToast('حدث خطأ أثناء إضافة التعليق', 'error');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
            });
        }
        
        // Scroll to comments
        function scrollToComments() {
            const commentsSection = document.getElementById('commentsSection');
            if (commentsSection) {
                commentsSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Logout function
        function logout() {
            const formData = new FormData();
            formData.append('action', 'logout');

            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = 'index.php';
            });
        }
            
            // Delete post function
            function deletePost() {
                if (!confirm('هل أنت متأكد أنك تريد حذف هذا المقال؟ لا يمكن التراجع عن هذا الإجراء.')) {
                    return;
                }
                
                const deleteBtn = document.querySelector('#deletePostModal .btn-danger');
                const originalText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
                deleteBtn.disabled = true;
                
                fetch('api/delete-post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        postId: postId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('تم حذف المقال بنجاح', 'success');
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1500);
                    } else {
                        showToast('خطأ: ' + (data.error || 'فشل في حذف المقال'), 'error');
                        deleteBtn.innerHTML = originalText;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('حدث خطأ أثناء حذف المقال', 'error');
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                });
            }
            
            // Show toast notification
            function showToast(message, type = 'info') {
                const toastContainer = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                toastContainer.appendChild(toast);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 5000);
                
                // Remove on click
                toast.addEventListener('click', () => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                });
            }
        
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('ratingModal');
            const closeBtn = document.querySelector('.close');
            const stars = document.querySelectorAll('#ratingStars i');

            if (closeBtn && modal) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                }
            }

            if (modal) {
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
            }

            // Star rating functionality
            if (stars.length > 0) {
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.dataset.rating;
                        selectedRating = rating;
                        
                        // Update star display
                        stars.forEach(s => {
                            if (s.dataset.rating <= rating) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                });
            }
        });

        // Social Media Sharing Functions
        function prepareShareText() {
            const articleContent = `<?= addslashes(strip_tags($post['content'])) ?>`;
            const postTitle = `<?= addslashes($post['title']) ?>`;
            const authorName = `<?= addslashes(($authorProfile['firstName'] ?? 'Unknown') . ' ' . ($authorProfile['lastName'] ?? 'Author')) ?>`;
            const articleUrl = window.location.href;
            const category = `<?= addslashes($category['name'] ?? 'General') ?>`;
            const views = `<?= $post['views'] ?? 0 ?>`;
            const rating = `<?= number_format($post['rating'] ?? 0, 1) ?>`;
            
            return `📝 ${postTitle}

📄 المحتوى:
${articleContent}

👤 الكاتب: ${authorName}
📂 التصنيف: ${category}
👁️ المشاهدات: ${views}
⭐ التقييم: ${rating}/5

🔗 رابط المقال: ${articleUrl}

#من_جديد #مقالات #${category}`;
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('✅ تم نسخ المقال إلى الحافظة!', 'success');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('✅ تم نسخ المقال إلى الحافظة!', 'success');
            });
        }
        
        function shareToFacebook() {
            const shareText = prepareShareText();
            copyToClipboard(shareText);
            
            // Show notification first
            showToast('📘 تم نسخ المقال! جاري فتح فيسبوك...', 'info');
            
            // Open Facebook share dialog after a short delay
            setTimeout(() => {
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent(shareText);
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
            }, 1000);
        }
        
        function shareToWhatsApp() {
            const shareText = prepareShareText();
            copyToClipboard(shareText);
            
            // Show notification first
            showToast('📱 تم نسخ المقال! جاري فتح واتساب...', 'info');
            
            // Open WhatsApp share after a short delay
            setTimeout(() => {
                const text = encodeURIComponent(shareText);
                window.open(`https://wa.me/?text=${text}`, '_blank');
            }, 1000);
        }
        
        function shareToInstagram() {
            const shareText = prepareShareText();
            copyToClipboard(shareText);
            
            // Check if user is on mobile
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                showToast('📸 تم نسخ المقال! جاري فتح انستغرام...', 'info');
                
                setTimeout(() => {
                    // Multiple Instagram deep link attempts
                    const instagramLinks = [
                        'instagram://app', // Open Instagram app
                        'instagram://library?AssetPickerSourceType=1', // Open to library
                        'instagram://camera', // Open camera
                        'instagram://feed', // Open feed
                        'instagram://', // Basic Instagram app link
                    ];
                    
                    let linkIndex = 0;
                    
                    function tryInstagramLink() {
                        if (linkIndex < instagramLinks.length) {
                            const link = instagramLinks[linkIndex];
                            console.log('Trying Instagram link:', link);
                            
                            // Try to open Instagram app
                            window.location.href = link;
                            
                            // Check if app opened (if not, try next link)
                            setTimeout(() => {
                                linkIndex++;
                                if (linkIndex < instagramLinks.length) {
                                    tryInstagramLink();
                                } else {
                                    // If all app links failed, open Instagram website
                                    showToast('📸 فتح انستغرام في المتصفح...', 'info');
                                    window.open('https://www.instagram.com/', '_blank');
                                }
                            }, 1500);
                        }
                    }
                    
                    tryInstagramLink();
                }, 1000);
            } else {
                // Desktop - try to open Instagram website
                showToast('📸 تم نسخ المقال! جاري فتح انستغرام...', 'info');
                setTimeout(() => {
                    window.open('https://www.instagram.com/', '_blank');
                }, 1000);
            }
        }
        
        function shareToTwitter() {
            const shareText = prepareShareText();
            copyToClipboard(shareText);
            
            // Show notification first
            showToast('🐦 تم نسخ المقال! جاري فتح تويتر...', 'info');
            
            // Open Twitter share dialog after a short delay
            setTimeout(() => {
                const text = encodeURIComponent(shareText);
                window.open(`https://twitter.com/intent/tweet?text=${text}`, '_blank');
            }, 1000);
        }
    </script>
    </div>
</body>
</html> 