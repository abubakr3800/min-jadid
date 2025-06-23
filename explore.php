<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();

// Get filter parameters
$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';

// Get all categories
$categories = $db->getCategories();

// Get posts with filters
$filters = [];
if ($categoryId) {
    $filters['categoryId'] = $categoryId;
}

$posts = $db->getPosts($filters);

// Filter by search if provided
if ($search) {
    $posts = array_filter($posts, function($post) use ($search) {
        $tags = $post['tags'];
        if (is_array($tags)) {
            $tags = implode(' ', $tags);
        }
        return (stripos($post['title'], $search) !== false) ||
               (stripos($post['content'], $search) !== false) ||
               (stripos($tags, $search) !== false);
    });
    $posts = array_values($posts);
}

$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - استكشاف المقالات</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="استكشاف المقالات والمنشورات في منصة من جديد">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
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
                    <li><a href="explore.php" class="nav-link active">استكشاف</a></li>
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

        <!-- القسم الرئيسي -->
        <main class="main-content">
            <div class="container">
                <div class="row">
                    <!-- الشريط الجانبي للفلاتر -->
                    <div class="col-lg-3">
                        <aside class="sidebar">
                            <!-- البحث -->
                            <div class="sidebar-widget mb-lg">
                                <h3 class="widget-title">البحث</h3>
                                <form method="GET" class="search-form">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="ابحث في المقالات..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- التصنيفات -->
                            <div class="sidebar-widget mb-lg">
                                <h3 class="widget-title">التصنيفات</h3>
                                <div class="categories-list">
                                    <a href="explore.php" class="category-item <?= !$categoryId ? 'active' : '' ?>">
                                        <span>جميع التصنيفات</span>
                                        <span class="count"><?= count($db->getPosts()) ?></span>
                                    </a>
                                    <?php foreach ($categories as $category): ?>
                                        <?php 
                                        $categoryPosts = $db->getPosts(['categoryId' => $category['id']]);
                                        $postCount = count($categoryPosts);
                                        ?>
                                        <a href="explore.php?category=<?= $category['id'] ?>" 
                                           class="category-item <?= $categoryId == $category['id'] ? 'active' : '' ?>">
                                            <span style="color: <?= $category['color'] ?>">
                                                <i class="<?= $category['icon'] ?>"></i>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </span>
                                            <span class="count"><?= $postCount ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- إحصائيات سريعة -->
                            <div class="sidebar-widget">
                                <h3 class="widget-title">إحصائيات سريعة</h3>
                                <div class="stats-list">
                                    <div class="stat-item">
                                        <div class="stat-number"><?= count($posts) ?></div>
                                        <div class="stat-label">مقال معروض</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?= count($categories) ?></div>
                                        <div class="stat-label">تصنيف متاح</div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <!-- المحتوى الرئيسي -->
                    <div class="col-lg-9">
                        <!-- عنوان الصفحة -->
                        <div class="page-header mb-lg">
                            <h1 class="page-title">استكشاف المقالات</h1>
                            <?php if ($categoryId): ?>
                                <?php 
                                $currentCategory = $db->getCategoryById($categoryId);
                                if ($currentCategory): 
                                ?>
                                    <p class="page-subtitle">
                                        تصفح مقالات تصنيف: 
                                        <span class="text-primary"><?= htmlspecialchars($currentCategory['name']) ?></span>
                                    </p>
                                <?php endif; ?>
                            <?php elseif ($search): ?>
                                <p class="page-subtitle">
                                    نتائج البحث عن: 
                                    <span class="text-primary">"<?= htmlspecialchars($search) ?>"</span>
                                </p>
                            <?php else: ?>
                                <p class="page-subtitle">استكشف جميع المقالات والاقتباسات الملهمة</p>
                            <?php endif; ?>
                        </div>

                        <!-- نتائج البحث -->
                        <?php if (empty($posts)): ?>
                            <div class="empty-state text-center py-lg">
                                <div class="empty-icon mb-md">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3>لا توجد نتائج</h3>
                                <p>
                                    <?php if ($search): ?>
                                        لم نجد مقالات تطابق بحثك "<?= htmlspecialchars($search) ?>"
                                    <?php elseif ($categoryId): ?>
                                        لا توجد مقالات في هذا التصنيف
                                    <?php else: ?>
                                        لا توجد مقالات منشورة حالياً
                                    <?php endif; ?>
                                </p>
                                <a href="explore.php" class="btn btn-primary mt-md">
                                    <i class="fas fa-arrow-left"></i>
                                    العودة للاستكشاف
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="cards-grid">
                                <?php foreach ($posts as $post): ?>
                                    <div class="card post-card fade-in-up">
                                        <div class="post-card-header">
                                            <div class="post-author">
                                                <a href="profile.php?id=<?= $post['user']['id'] ?>" class="author-name">
                                                    <?= htmlspecialchars($post['user']['firstName'] . ' ' . $post['user']['lastName']) ?>
                                                </a>
                                                <span class="post-date"><?= date('Y/m/d', strtotime($post['createdAt'])) ?></span>
                                            </div>
                                            <div class="post-category">
                                                <?php 
                                                $category = $db->getCategoryById($post['categoryId']);
                                                if ($category): 
                                                ?>
                                                    <span class="category-badge" style="background-color: <?= $category['color'] ?>">
                                                        <i class="<?= $category['icon'] ?>"></i>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="post-content">
                                            <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                                            <p class="post-excerpt"><?= (substr($post['content'], 0, 170)) ?>...</p>
                                        </div>
                                        
                                        <div class="post-meta">
                                            <div class="post-rating">
                                                <div class="stars" data-post-id="<?= $post['id'] ?>">
                                                    <?php 
                                                    $userRating = $isLoggedIn ? $db->getUserRating($currentUser['id'], $post['id']) : 0;
                                                    for ($i = 1; $i <= 5; $i++): 
                                                    ?>
                                                        <i class="fas fa-star <?= $i <= $post['averageRating'] ? 'filled' : '' ?> <?= $i <= $userRating ? 'user-rated' : '' ?>" 
                                                           data-rating="<?= $i ?>" 
                                                           <?= $isLoggedIn ? 'onclick="ratePost(' . $post['id'] . ', ' . $i . ')"' : 'data-bs-toggle="modal" data-bs-target="#loginModal" title="تسجيل الدخول مطلوب للتقييم"' ?>>
                                                        </i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="rating-count">(<?= $post['totalRatings'] ?> تقييم)</span>
                                                <?php if (!$isLoggedIn): ?>
                                                    <small class="login-hint" style="display: block; margin-top: 0.25rem; color: #6c757d; font-size: 0.875rem;">
                                                        <i class="fas fa-info-circle"></i>
                                                        تسجيل الدخول مطلوب للتقييم والإعجاب
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="post-actions-meta">
                                                <div class="post-views">
                                                    <i class="fas fa-eye"></i>
                                                    <span><?= $post['views'] ?></span>
                                                </div>
                                                <?php if ($isLoggedIn): ?>
                                                    <div class="post-like">
                                                        <button class="like-btn <?= $db->hasUserLikedPost($currentUser['id'], $post['id']) ? 'liked' : '' ?>" 
                                                                onclick="toggleLike(<?= $post['id'] ?>)">
                                                            <i class="fas fa-heart"></i>
                                                            <span class="like-count"><?= $post['likes'] ?? 0 ?></span>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="post-like-disabled" title="تسجيل الدخول مطلوب للإعجاب" data-bs-toggle="modal" data-bs-target="#loginModal">
                                                        <i class="fas fa-heart" style="opacity: 0.4;"></i>
                                                        <span class="like-count"><?= $post['likes'] ?? 0 ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="post-actions">
                                            <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                قراءة المزيد
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول مطلوب
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="modal-icon mb-3">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h6>يجب تسجيل الدخول للتفاعل مع المحتوى</h6>
                    <p class="text-muted">سجل دخولك للتمكن من التقييم والإعجاب بالمقالات</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </a>
                    <a href="signup.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i>
                        إنشاء حساب
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- سكريبت JavaScript -->
    <script src="js/ui.js"></script>
    <script src="js/animations.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Check if user is logged in
        function isLoggedIn() {
            return <?= $isLoggedIn ? 'true' : 'false' ?>;
        }
        
        // Rating functionality
        function ratePost(postId, rating) {
            if (!isLoggedIn()) {
                try {
                    // Show login modal instead of notification
                    const modalElement = document.getElementById('loginModal');
                    
                    if (modalElement) {
                        const loginModal = new bootstrap.Modal(modalElement);
                        loginModal.show();
                    } else {
                        showNotification('يجب تسجيل الدخول لتقييم المقال', 'error');
                    }
                } catch (error) {
                    showNotification('يجب تسجيل الدخول لتقييم المقال', 'error');
                }
                return;
            }
            fetch('api/rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    postId: postId,
                    rating: rating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the stars display
                    updateStarsDisplay(postId, rating, data.post);
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء التقييم', 'error');
            });
        }
        
        function updateStarsDisplay(postId, userRating, postData) {
            const starsContainer = document.querySelector(`[data-post-id="${postId}"]`);
            if (!starsContainer) return;
            
            const stars = starsContainer.querySelectorAll('.fas.fa-star');
            const ratingCount = starsContainer.parentElement.querySelector('.rating-count');
            
            // Update average rating display
            stars.forEach((star, index) => {
                const starRating = index + 1;
                star.classList.remove('filled', 'user-rated');
                
                if (starRating <= postData.averageRating) {
                    star.classList.add('filled');
                }
                if (starRating <= userRating) {
                    star.classList.add('user-rated');
                }
            });
            
            // Update rating count
            ratingCount.textContent = `(${postData.totalRatings} تقييم)`;
        }
        
        // Like functionality
        function toggleLike(postId) {
            if (!isLoggedIn()) {
                try {
                    // Show login modal instead of notification
                    const modalElement = document.getElementById('loginModal');
                    
                    if (modalElement) {
                        const loginModal = new bootstrap.Modal(modalElement);
                        loginModal.show();
                    } else {
                        showNotification('يجب تسجيل الدخول للإعجاب بالمقال', 'error');
                    }
                } catch (error) {
                    showNotification('يجب تسجيل الدخول للإعجاب بالمقال', 'error');
                }
                return;
            }
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
                    // Update like button and count
                    updateLikeDisplay(postId, data.liked, data.post);
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ أثناء الإعجاب', 'error');
            });
        }
        
        function updateLikeDisplay(postId, liked, postData) {
            const likeBtn = document.querySelector(`button[onclick="toggleLike(${postId})"]`);
            if (!likeBtn) return;
            
            const likeCount = likeBtn.querySelector('.like-count');
            
            if (liked) {
                likeBtn.classList.add('liked');
            } else {
                likeBtn.classList.remove('liked');
            }
            
            likeCount.textContent = postData.likes || 0;
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }
        
        // Add hover effects for stars
        document.addEventListener('DOMContentLoaded', function() {
            const starsContainers = document.querySelectorAll('.stars');
            
            starsContainers.forEach(container => {
                const stars = container.querySelectorAll('.fas.fa-star');
                
                stars.forEach((star, index) => {
                    star.addEventListener('mouseenter', function() {
                        const rating = index + 1;
                        highlightStars(container, rating);
                    });
                    
                    star.addEventListener('mouseleave', function() {
                        resetStarsHighlight(container);
                    });
                });
            });
        });
        
        function highlightStars(container, rating) {
            const stars = container.querySelectorAll('.fas.fa-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#ffc107';
                }
            });
        }
        
        function resetStarsHighlight(container) {
            const stars = container.querySelectorAll('.fas.fa-star');
            stars.forEach(star => {
                star.style.color = '';
            });
        }
    </script>
    
    <style>
        /* Style for non-interactive rating stars */
        .fas.fa-star:not([onclick]) {
            opacity: 0.6;
            cursor: help;
        }
        
        .fas.fa-star:not([onclick]):hover {
            opacity: 0.8;
        }
        
        /* Style for clickable rating stars (all users) */
        .fas.fa-star[onclick] {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .fas.fa-star[onclick]:hover {
            transform: scale(1.1);
            color: #ffc107;
        }
        
        /* Style for disabled like button */
        .post-like-disabled {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s ease;
        }
        
        .post-like-disabled:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .notification-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .notification-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            margin-left: 1rem;
            padding: 0.25rem;
        }
        
        /* Post actions meta styles */
        .post-actions-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .post-like {
            display: flex;
            align-items: center;
        }
        
        .like-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .like-btn:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .like-btn.liked {
            color: #dc3545;
        }
        
        .like-btn.liked i {
            animation: heartBeat 0.3s ease;
        }
        
        /* Star rating styles */
        .stars .fas.fa-star {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .stars .fas.fa-star.filled {
            color: #ffc107;
        }
        
        .stars .fas.fa-star.user-rated {
            color: #ff6b35;
        }
        
        /* Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        /* Login Modal Styles */
        .modal-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .modal-header {
            border-bottom: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem 1rem;
        }
        
        .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 2rem;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
        }
        
        .modal-body h6 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .modal-body p {
            color: #718096;
            font-size: 0.95rem;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            background: white;
        }
        
        .modal-footer .btn {
            margin: 0 0.5rem;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .modal-footer .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .modal-footer .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .modal-footer .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }
        
        .modal-footer .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            transform: translateY(-2px);
        }
        
        .modal-footer .btn-secondary {
            background: #e2e8f0;
            border: none;
            color: #4a5568;
        }
        
        .modal-footer .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        body {
            background: linear-gradient(135deg, #6366f1 0%, #60a5fa 100%);
            min-height: 100vh;
        }
        
        .login-container, .signup-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(99,102,241,0.10);
            padding: 2.5rem 2rem;
            max-width: 400px;
            margin: 2rem auto;
        }
        
        .login-logo img, .signup-logo img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            box-shadow: 0 2px 12px rgba(99,102,241,0.10);
            margin-bottom: 1.5rem;
            background: #f3f4f6;
            padding: 8px;
        }
        
        .login-title, .signup-title {
            color: #3730a3;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .btn-primary, .btn-signup {
            background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
            color: #fff;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(99,102,241,0.10);
            transition: 0.2s;
        }
        .btn-primary:hover, .btn-signup:hover {
            background: linear-gradient(90deg, #3730a3 0%, #6366f1 100%);
            transform: translateY(-2px) scale(1.03);
        }
        
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e0e7ff;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px #6366f133;
        }
        .input-icon, .input-group .input-icon {
            color: #6366f1;
            font-size: 1.1rem;
        }
    </style>
</body>
</html> 