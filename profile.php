<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();

// Get user ID from URL parameter
$userId = $_GET['id'] ?? null;

if (!$userId) {
    // If no user ID provided, show current user's profile
    if ($auth->isLoggedIn()) {
        $userId = $auth->getCurrentUserId();
    } else {
        header('Location: login.php');
        exit();
    }
}

// Get user data
$user = $db->getUserById($userId);
if (!$user) {
    header('Location: index.php');
    exit();
}

// Get user's posts
$userPosts = $db->getPosts(['userId' => $userId]);

// Get services
$servicesFile = 'data/services.json';
$allServices = file_exists($servicesFile) ? json_decode(file_get_contents($servicesFile), true) : [];
$userServices = array_filter($allServices, function($srv) use ($userId) { return $srv['userId'] == $userId; });

// Check if current user is viewing their own profile
$isOwnProfile = $auth->isLoggedIn() && $auth->getCurrentUserId() == $userId;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من جديد - الملف الشخصي</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/profile-mobile.css">
    <link rel="stylesheet" href="css/modal-fixes.css">
    <link rel="stylesheet" href="css/profile-modal-fixes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    <meta name="description" content="الملف الشخصي في منصة من جديد">
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
                    <?php if ($auth->isLoggedIn()): ?>
                        <li><a href="new-post.php" class="nav-link">مقال جديد</a></li>
                        <li><a href="profile.php" class="nav-link active">الملف الشخصي</a></li>
                        <!-- Notification Bell -->
                        <?php if ($isOwnProfile): ?>
                        <?php 
                            $notifications = $db->getUserNotifications($userId);
                            $unreadCount = 0;
                            foreach ($notifications as $notif) { if (!$notif['read']) $unreadCount++; }
                        ?>
                        <li class="nav-link notification-bell-wrapper" style="position:relative;">
                            <a href="#" id="notifBell" onclick="event.preventDefault(); document.getElementById('notifDropdown').classList.toggle('show');">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                <span class="notif-badge"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                            <div id="notifDropdown" class="notif-dropdown">
                                <div class="notif-dropdown-header">الإشعارات</div>
                                <?php if (empty($notifications)): ?>
                                    <div class="notif-empty">لا توجد إشعارات جديدة</div>
                                <?php else: ?>
                                    <ul class="notif-list">
                                        <?php foreach (array_slice($notifications, 0, 8) as $notif): ?>
                                        <li class="notif-item<?= !$notif['read'] ? ' unread' : '' ?>">
                                            <a href="<?= htmlspecialchars($notif['link']) ?>">
                                                <i class="fas fa-bell"></i>
                                                <?= htmlspecialchars($notif['message']) ?>
                                                <span class="notif-date"><?= date('Y/m/d', strtotime($notif['createdAt'])) ?></span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <div class="notif-dropdown-footer" style="text-align:center; padding:0.5rem 1rem;">
                                    <a href="notifications.php" class="btn btn-link" style="color:#4f8cff; font-weight:bold;">عرض كل الإشعارات</a>
                                </div>
                            </div>
                        </li>
                        <style>
                        .notification-bell-wrapper { position: relative; }
                        .notification-bell-wrapper .fa-bell { font-size: 1.3rem; }
                        .notif-badge {
                            position: absolute;
                            top: 0px;
                            right: 0px;
                            background: #ff5252;
                            color: #fff;
                            border-radius: 50%;
                            font-size: 0.7rem;
                            padding: 2px 6px;
                            font-weight: bold;
                        }
                        .notif-dropdown {
                            display: none;
                            position: absolute;
                            right: 0;
                            top: 2.2rem;
                            background: #fff;
                            min-width: 270px;
                            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
                            border-radius: 12px;
                            z-index: 1000;
                            padding: 0.5rem 0;
                        }
                        .notif-dropdown.show { display: block; }
                        .notif-dropdown-header {
                            font-weight: bold;
                            padding: 0.5rem 1rem;
                            border-bottom: 1px solid #eee;
                            color: #4f8cff;
                        }
                        .notif-empty {
                            padding: 1rem;
                            text-align: center;
                            color: #888;
                        }
                        .notif-list { list-style: none; margin: 0; padding: 0; }
                        .notif-item { padding: 0.7rem 1rem; border-bottom: 1px solid #f3f3f3; }
                        .notif-item.unread { background: #f0f6ff; font-weight: bold; }
                        .notif-item:last-child { border-bottom: none; }
                        .notif-item a { color: #222; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
                        .notif-item .fa-bell { color: #4f8cff; }
                        .notif-date { font-size: 0.8rem; color: #888; margin-right: auto; }
                        </style>
                        <script>
                        document.addEventListener('click', function(e) {
                            var bell = document.getElementById('notifBell');
                            var dropdown = document.getElementById('notifDropdown');
                            if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                                dropdown.classList.remove('show');
                            }
                        });
                        </script>
                        <?php endif; ?>
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
                <!-- معلومات المستخدم -->
                <section class="profile-header mb-lg">
                    <div class="card">
                        <div class="card-body p-lg">
                            <div class="row items-center">
                                <div class="col-md-3 text-center">
                                    <div class="profile-avatar">
                                        <?php if (!empty($user['avatar']) && file_exists($user['avatar'])): ?>
                                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="الصورة الشخصية" class="avatar-img">
                                        <?php else: ?>
                                            <div class="avatar-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="profile-info">
                                        <h1 class="profile-name"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h1>
                                        
                                        <?php if (!empty($user['bio'])): ?>
                                            <p class="profile-bio"><?= htmlspecialchars($user['bio']) ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="profile-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-envelope"></i>
                                                <span><?= htmlspecialchars($user['email']) ?></span>
                                            </div>
                                            
                                            <?php if (!empty($user['linkedin'])): ?>
                                                <div class="meta-item">
                                                    <i class="fab fa-linkedin"></i>
                                                    <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" class="text-primary">
                                                        LinkedIn
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="meta-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>انضم في <?= date('Y/m/d', strtotime($user['createdAt'])) ?></span>
                                            </div>
                                            
                                            <div class="meta-item">
                                                <i class="fas fa-file-alt"></i>
                                                <span><?= count($userPosts) ?> مقال منشور</span>
                                            </div>
                                        </div>
                                        
                                        <?php if ($isOwnProfile): ?>
                                            <div class="profile-actions mt-md">
                                                <a href="edit-profile.php" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                    تعديل الملف الشخصي
                                                </a>
                                                <a href="add-service.php" class="btn btn-success">
                                                    <i class="fas fa-plus"></i>
                                                    إضافة خدمة
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                
                <!-- خدمات المستخدم -->
                <section class="user-services mt-xl">
                    <div class="section-header">
                        <h2 class="section-title">الخدمات المقدمة</h2>
                        <p class="section-subtitle">الخدمات التي يقدمها <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></p>
                    </div>
                    <?php if (empty($userServices)): ?>
                        <div class="empty-state text-center py-lg">
                            <div class="empty-icon mb-md">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h3>لا توجد خدمات مضافة</h3>
                            <?php if ($isOwnProfile): ?>
                                <a href="add-service.php" class="btn btn-success mt-md">
                                    <i class="fas fa-plus"></i> أضف خدمة جديدة
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="cards-grid">
                            <?php foreach ($userServices as $srv): ?>
                                <div class="card post-card fade-in-up" style="position:relative;">
                                    <?php if (!empty($srv['image'])): ?>
                                        <img src="<?= htmlspecialchars($srv['image']) ?>" alt="صورة الخدمة" style="width:100%;max-height:180px;object-fit:cover;border-radius:12px 12px 0 0;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h3 class="post-title" style="font-size:1.3rem;"> <?= htmlspecialchars($srv['title']) ?> </h3>
                                        <p class="card-text"> <?= nl2br(htmlspecialchars($srv['desc'])) ?> </p>
                                        <div class="post-meta" style="margin-bottom:1rem;">
                                            <span class="badge bg-success" style="font-size:1rem;"> <?= number_format($srv['price']) ?> جنيه مصري </span>
                                            <span class="text-muted" style="font-size:0.9rem;float:left;"> <?= date('Y/m/d', strtotime($srv['createdAt'])) ?> </span>
                                        </div>
                                        <?php if ($isOwnProfile): ?>
                                        <div style="display:flex;gap:0.5rem;">
                                            <a href="edit-service.php?id=<?= $srv['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
                                            <form method="post" action="" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟');" style="display:inline;">
                                                <input type="hidden" name="delete_service_id" value="<?= $srv['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> حذف</button>
                                            </form>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- مقالات المستخدم -->
                <section class="user-posts">
                    <div class="section-header">
                        <h2 class="section-title">المقالات المنشورة</h2>
                        <p class="section-subtitle">مقالات <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></p>
                    </div>
                    
                    <?php if (empty($userPosts)): ?>
                        <div class="empty-state text-center py-lg">
                            <div class="empty-icon mb-md">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3>لا توجد مقالات منشورة</h3>
                            <p>لم ينشر <?= htmlspecialchars($user['firstName']) ?> أي مقالات بعد.</p>
                            <?php if ($isOwnProfile): ?>
                                <a href="new-post.php" class="btn btn-primary mt-md">
                                    <i class="fas fa-plus"></i>
                                    نشر مقال جديد
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="cards-grid">
                            <?php foreach ($userPosts as $post): ?>
                                <div class="card post-card fade-in-up">
                                    <div class="post-card-header">
                                        <div class="post-author">
                                            <span class="author-name"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></span>
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
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $post['averageRating'] ? 'filled' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-count">(<?= $post['totalRatings'] ?> تقييم)</span>
                                        </div>
                                        <div class="post-views">
                                            <i class="fas fa-eye"></i>
                                            <span><?= $post['views'] ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="post-actions">
                                        <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            قراءة المزيد
                                        </a>
                                        <?php if ($isOwnProfile): ?>
                                            <a href="edit-post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                                تعديل
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-post-btn" data-post-id="<?= $post['id'] ?>" data-post-title="<?= htmlspecialchars($post['title']) ?>">
                                                <i class="fas fa-trash"></i>
                                                حذف
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

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

    <!-- سكريبت JavaScript -->
    <script src="js/ui.js"></script>
    <script src="js/animations.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script>
    let cropper, croppedBlob;
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize delete functionality
      initDeleteFunctionality();
      
      // Initialize modal improvements for mobile
      initModalImprovements();
      
      var editProfileForm = document.getElementById('editProfileForm');
      var avatarInput = document.getElementById('editAvatar');
      var avatarPreview = document.getElementById('avatarPreview');
      var avatarDropZone = document.getElementById('avatarDropZone');
      var avatarError = document.getElementById('avatarError');
      var avatarCropperModal = document.getElementById('avatarCropperModal');
      var avatarCropperImg = document.getElementById('avatarCropperImg');
      var cropAvatarBtn = document.getElementById('cropAvatarBtn');
      let cropperModalInstance;
      
      // Initialize delete functionality
      function initDeleteFunctionality() {
        const deleteButtons = document.querySelectorAll('.delete-post-btn');
        const confirmDeleteBtn = document.getElementById('confirmDeletePost');
        const deletePostModal = document.getElementById('deletePostModal');
        const deletePostTitle = document.getElementById('deletePostTitle');
        
        // Add click event to delete buttons
        deleteButtons.forEach(button => {
          button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const postTitle = this.getAttribute('data-post-title');
            
            // Set the post title in the modal
            deletePostTitle.textContent = postTitle;
            
            // Store the post ID in the modal
            deletePostModal.setAttribute('data-post-id', postId);
            
            // Show the modal
            const modal = new bootstrap.Modal(deletePostModal);
            modal.show();
          });
        });
        
        // Confirm delete button
        if (confirmDeleteBtn) {
          confirmDeleteBtn.addEventListener('click', async function() {
            const postId = deletePostModal.getAttribute('data-post-id');
            
            if (postId) {
              await deletePost(postId);
            }
          });
        }
      }
      
      // Delete post function
      async function deletePost(postId) {
        const confirmBtn = document.getElementById('confirmDeletePost');
        const originalText = confirmBtn.innerHTML;
        
        // Show loading state
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الحذف...';
        confirmBtn.disabled = true;
        
        try {
          const response = await fetch('api/posts.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              action: 'delete',
              postId: postId
            })
          });
          
          const result = await response.json();
          
          if (result.success) {
            showNotification('تم حذف المقال بنجاح', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deletePostModal'));
            modal.hide();
            
            // Remove article card from page
            const articleCard = document.querySelector(`[data-post-id="${postId}"]`).closest('.post-card');
            if (articleCard) {
              articleCard.remove();
            }
            
            // Update article count
            const articleCountElement = document.querySelector('.meta-item:last-child span');
            if (articleCountElement) {
              const currentText = articleCountElement.textContent;
              const currentCount = parseInt(currentText.match(/\d+/)[0]);
              articleCountElement.textContent = `${currentCount - 1} مقال منشور`;
            }
            
            // Check if no articles left
            const remainingArticles = document.querySelectorAll('.post-card');
            if (remainingArticles.length === 0) {
              const cardsGrid = document.querySelector('.cards-grid');
              if (cardsGrid) {
                cardsGrid.innerHTML = `
                  <div class="empty-state text-center py-lg">
                    <div class="empty-icon mb-md">
                      <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>لا توجد مقالات منشورة</h3>
                    <p>لم تنشر أي مقالات بعد.</p>
                    <a href="new-post.php" class="btn btn-primary mt-md">
                      <i class="fas fa-plus"></i>
                      نشر مقال جديد
                    </a>
                  </div>
                `;
              }
            }
            
          } else {
            throw new Error(result.error || 'فشل حذف المقال');
          }
          
        } catch (error) {
          console.error('Error deleting post:', error);
          showNotification(error.message || 'فشل حذف المقال', 'error');
        } finally {
          // Restore button state
          confirmBtn.innerHTML = originalText;
          confirmBtn.disabled = false;
        }
      }
      
      // Show notification function
      function showNotification(message, type = 'info') {
        const toast = document.getElementById('notificationToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = toast.querySelector('.toast-header i');
        
        // Set message
        toastMessage.textContent = message;
        
        // Set title and icon based on type
        switch (type) {
          case 'success':
            toastTitle.textContent = 'نجح';
            toastIcon.className = 'fas fa-check-circle me-2 text-success';
            break;
          case 'error':
            toastTitle.textContent = 'خطأ';
            toastIcon.className = 'fas fa-exclamation-circle me-2 text-danger';
            break;
          default:
            toastTitle.textContent = 'إشعار';
            toastIcon.className = 'fas fa-info-circle me-2 text-info';
        }
        
        // Ensure toast is visible and properly positioned
        toast.style.display = 'block';
        toast.style.position = 'relative';
        toast.style.zIndex = '10000';
        
        // Show toast using Bootstrap
        const bsToast = new bootstrap.Toast(toast, {
          autohide: true,
          delay: 5000
        });
        bsToast.show();
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          bsToast.hide();
        }, 5000);
      }
      
      function openCropper(file) {
        if (!avatarCropperImg || !avatarCropperModal) return;
        avatarError && (avatarError.textContent = '');
        var reader = new FileReader();
        reader.onload = function(e) {
          avatarCropperImg.src = e.target.result;
          cropperModalInstance = new bootstrap.Modal(avatarCropperModal);
          cropperModalInstance.show();
        };
        reader.readAsDataURL(file);
      }
      
      function validateImage(file) {
        if (!avatarError) return false;
        avatarError.textContent = '';
        
        // Check file type
        if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
          avatarError.textContent = 'الرجاء اختيار صورة بصيغة jpg, png, gif, أو webp';
          return false;
        }
        
        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          avatarError.textContent = 'الحد الأقصى لحجم الصورة هو 5 ميجابايت';
          return false;
        }
        
        return true;
      }
      
      if (avatarDropZone && avatarInput && avatarPreview) {
        avatarDropZone.addEventListener('dragover', function(e) {
          e.preventDefault();
          avatarDropZone.classList.add('dragover');
        });
        avatarDropZone.addEventListener('dragleave', function(e) {
          e.preventDefault();
          avatarDropZone.classList.remove('dragover');
        });
        avatarDropZone.addEventListener('drop', function(e) {
          e.preventDefault();
          avatarDropZone.classList.remove('dragover');
          var file = e.dataTransfer.files[0];
          if (file && validateImage(file)) {
            openCropper(file);
          }
        });
        avatarInput.addEventListener('change', function() {
          if (this.files && this.files[0] && validateImage(this.files[0])) {
            openCropper(this.files[0]);
          }
        });
      }
      
      if (avatarCropperModal) {
        avatarCropperModal.addEventListener('shown.bs.modal', function () {
          if (cropper) cropper.destroy();
          if (!avatarCropperImg) return;
          cropper = new Cropper(avatarCropperImg, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            guides: false,
            center: true,
            background: false,
            highlight: false,
            cropBoxResizable: true,
            cropBoxMovable: true,
            minContainerWidth: 220,
            minContainerHeight: 220,
            ready() {
              // Make the crop box circular
              const cropBox = avatarCropperModal.querySelector('.cropper-crop-box');
              if (cropBox) cropBox.style.borderRadius = '50%';
            }
          });
        });
      }
      cropAvatarBtn && cropAvatarBtn.addEventListener('click', function() {
        if (cropper) {
          cropper.getCroppedCanvas({ width: 240, height: 240, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
            croppedBlob = blob;
            var url = URL.createObjectURL(blob);
            avatarPreview && (avatarPreview.src = url);
            // Set a fake File in the input for form submission
            var dt = new DataTransfer();
            var file = new File([blob], 'avatar.png', { type: 'image/png' });
            dt.items.add(file);
            avatarInput && (avatarInput.files = dt.files);
            cropperModalInstance && cropperModalInstance.hide();
          }, 'image/png');
        }
      });
      if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
          e.preventDefault();
          var msg = document.getElementById('editProfileMsg');
          msg && (msg.textContent = '');
          msg && (msg.className = '');
          var btn = editProfileForm.querySelector('button[type="submit"]');
          btn && (btn.disabled = true);
          btn && (btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جارٍ الحفظ...');
          
          var formData = new FormData(editProfileForm);
          
          // Debug: Log form data
          console.log('Submitting form data:');
          for (let [key, value] of formData.entries()) {
            if (key === 'avatar' && value instanceof File) {
              console.log(key, ':', value.name, '(', value.size, 'bytes)');
            } else {
              console.log(key, ':', value);
            }
          }
          
          fetch('api/update-profile.php', {
            method: 'POST',
            body: formData
          })
          .then(res => {
            console.log('Response status:', res.status);
            return res.json();
          })
          .then(data => {
            console.log('Response data:', data);
            btn && (btn.disabled = false);
            btn && (btn.innerHTML = '<i class="fas fa-save"></i> حفظ التعديلات');
            
            if (data.success) {
              msg && (msg.textContent = data.message);
              msg && (msg.className = 'alert alert-success');
              
              // Update avatar preview if new avatar was uploaded
              if (data.avatar && avatarPreview) {
                avatarPreview.src = data.avatar + '?t=' + new Date().getTime();
              }
              
              setTimeout(function() {
                var modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
                modal && modal.hide();
                location.reload();
              }, 1200);
            } else {
              msg && (msg.textContent = data.error || 'حدث خطأ');
              msg && (msg.className = 'alert alert-danger');
            }
          })
          .catch(error => {
            console.error('Error submitting form:', error);
            btn && (btn.disabled = false);
            btn && (btn.innerHTML = '<i class="fas fa-save"></i> حفظ التعديلات');
            msg && (msg.textContent = 'حدث خطأ أثناء الاتصال بالخادم');
            msg && (msg.className = 'alert alert-danger');
          });
        });
      }
      
      // Initialize modal improvements for mobile
      function initModalImprovements() {
        const editProfileModal = document.getElementById('editProfileModal');
        
        if (editProfileModal) {
          // Ensure modal is properly sized on mobile
          editProfileModal.addEventListener('shown.bs.modal', function() {
            const modalDialog = this.querySelector('.modal-dialog');
            const modalContent = this.querySelector('.modal-content');
            const modalBody = this.querySelector('.modal-body');
            
            // Set proper height for mobile
            if (window.innerWidth <= 768) {
              modalDialog.style.maxHeight = '95vh';
              modalContent.style.maxHeight = '95vh';
              modalBody.style.maxHeight = 'calc(95vh - 140px)';
              modalBody.style.overflowY = 'auto';
              modalBody.style.webkitOverflowScrolling = 'touch';
            }
            
            // Focus on first input
            const firstInput = this.querySelector('input, textarea');
            if (firstInput) {
              setTimeout(() => firstInput.focus(), 300);
            }
            
            // Add modal-open class to body
            document.body.classList.add('modal-open');
          });
          
          // Handle modal close
          editProfileModal.addEventListener('hidden.bs.modal', function() {
            // Reset form validation states
            const inputs = this.querySelectorAll('.form-control');
            inputs.forEach(input => {
              input.classList.remove('is-invalid');
            });
            
            // Clear error messages
            const errorMsg = document.getElementById('editProfileMsg');
            if (errorMsg) {
              errorMsg.textContent = '';
              errorMsg.className = '';
            }
            
            // Reset avatar error
            if (avatarError) {
              avatarError.textContent = '';
            }
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
          });
          
          // Prevent body scroll when modal is open
          editProfileModal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
          });
          
          editProfileModal.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
          });
          
          // Handle touch events for better mobile experience
          const modalBody = editProfileModal.querySelector('.modal-body');
          if (modalBody) {
            let startY = 0;
            let startScrollTop = 0;
            
            modalBody.addEventListener('touchstart', function(e) {
              startY = e.touches[0].clientY;
              startScrollTop = this.scrollTop;
            });
            
            modalBody.addEventListener('touchmove', function(e) {
              const currentY = e.touches[0].clientY;
              const diff = startY - currentY;
              
              // Allow scrolling within the modal body
              this.scrollTop = startScrollTop + diff;
            });
          }
        }
      }
    });
    </script>

    <!-- Edit Profile Modal -->
    <?php if ($isOwnProfile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProfileModalLabel">
              <i class="fas fa-edit"></i> تعديل الملف الشخصي
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <form id="editProfileForm">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editFirstName" class="form-label">الاسم الأول</label>
                    <input type="text" class="form-control" id="editFirstName" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editLastName" class="form-label">اسم العائلة</label>
                    <input type="text" class="form-control" id="editLastName" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" required>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="editBio" class="form-label">نبذة شخصية</label>
                <textarea class="form-control" id="editBio" name="bio" rows="3" placeholder="اكتب نبذة مختصرة عن نفسك..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
              </div>
              <div class="mb-3">
                <label for="editLinkedin" class="form-label">رابط LinkedIn</label>
                <input type="url" class="form-control" id="editLinkedin" name="linkedin" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/username">
              </div>
              <div class="mb-3">
                <label class="form-label d-block mb-3">الصورة الشخصية</label>
                <div class="avatar-upload-container">
                  <div id="avatarDropZone" class="avatar-drop-zone">
                    <img id="avatarPreview" src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'img/avatar-placeholder.png' ?>" alt="الصورة الشخصية">
                    <div class="avatar-overlay">
                      <i class="fas fa-camera"></i>
                      <span>انقر أو اسحب لتغيير الصورة</span>
                    </div>
                    <input type="file" id="editAvatar" name="avatar" accept="image/*">
                  </div>
                  <div class="avatar-upload-info">
                    <small class="text-muted">
                      <i class="fas fa-info-circle"></i>
                      يمكنك رفع صورة بحجم أقصى 5 ميجابايت بصيغة JPG أو PNG
                    </small>
                  </div>
                </div>
                <div id="avatarError" class="text-danger mt-2"></div>
              </div>
              <div id="editProfileMsg" class="mb-2"></div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> إلغاء
            </button>
            <button type="submit" form="editProfileForm" class="btn btn-primary">
              <i class="fas fa-save"></i> حفظ التعديلات
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Avatar Cropper Modal -->
    <div class="modal fade" id="avatarCropperModal" tabindex="-1" aria-labelledby="avatarCropperModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="avatarCropperModalLabel">
              <i class="fas fa-crop"></i> تعديل الصورة الشخصية
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body text-center">
            <div class="cropper-container">
              <img id="avatarCropperImg" src="" alt="صورة للتعديل">
            </div>
            <div class="cropper-instructions mt-3">
              <p class="text-muted">
                <i class="fas fa-mouse-pointer"></i>
                اسحب وحرك لتحديد المنطقة المطلوبة
              </p>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-primary" id="cropAvatarBtn">
              <i class="fas fa-check"></i> استخدام الصورة
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> إلغاء
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Post Confirmation Modal -->
    <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deletePostModalLabel">
              <i class="fas fa-exclamation-triangle"></i> تأكيد حذف المقال
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body text-center">
            <p>هل أنت متأكد من رغبتك في حذف المقال التالي؟</p>
            <p class="fw-bold" id="deletePostTitle"></p>
            <p class="text-danger">لا يمكن التراجع عن هذا الإجراء.</p>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-danger" id="confirmDeletePost">
              <i class="fas fa-trash"></i> نعم، حذف المقال
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
      <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <i class="fas fa-info-circle me-2"></i>
          <strong class="me-auto" id="toastTitle">إشعار</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="إغلاق"></button>
        </div>
        <div class="toast-body" id="toastMessage">
        </div>
      </div>
    </div>
    <style>
    @media (max-width: 600px) {
      .profile-info {
        text-align: center;
      }
      .profile-avatar {
        margin: 0 auto 1rem;
      }
      .profile-actions {
        justify-content: center;
      }
    }
    
    /* Profile Page Improvements */
    .profile-header {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }
    
    .profile-avatar {
      position: relative;
      width: 120px;
      height: 120px;
      margin: 0 auto;
    }
    
    .avatar-img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--white);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .avatar-placeholder {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 3rem;
      border: 4px solid var(--white);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-name {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }
    
    .profile-bio {
      color: var(--text-medium);
      line-height: 1.6;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }
    
    .profile-meta {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text-medium);
      font-size: 0.9rem;
    }
    
    .meta-item i {
      color: var(--primary-color);
    }
    
    .profile-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    /* Post Cards Improvements */
    .post-card {
      background: var(--white);
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
      position: relative;
    }
    
    .post-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .post-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    
    .post-author {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    
    .author-name {
      font-weight: 600;
      color: var(--text-dark);
    }
    
    .post-date {
      font-size: 0.85rem;
      color: var(--text-medium);
    }
    
    .category-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      color: white;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .post-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      line-height: 1.4;
    }
    
    .post-excerpt {
      color: var(--text-medium);
      line-height: 1.6;
      margin-bottom: 1rem;
    }
    
    .post-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding: 0.75rem 0;
      border-top: 1px solid var(--border-color);
    }
    
    .post-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .stars {
      display: flex;
      gap: 0.1rem;
    }
    
    .stars i {
      color: #ffd700;
      font-size: 0.9rem;
    }
    
    .stars i.filled {
      color: #ffd700;
    }
    
    .rating-count {
      font-size: 0.85rem;
      color: var(--text-medium);
    }
    
    .post-views {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      color: var(--text-medium);
      font-size: 0.85rem;
    }
    
    .post-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    
    .post-actions .btn {
      font-size: 0.85rem;
      padding: 0.375rem 0.75rem;
    }
    
    /* Delete Button Styling */
    .delete-post-btn {
      transition: all 0.3s ease;
      background-color: transparent;
      border-color: #dc2626;
      color: #dc2626;
    }
    
    .delete-post-btn:hover {
      background-color: #dc2626;
      border-color: #dc2626;
      color: white;
      transform: scale(1.05);
    }
    
    .delete-post-btn:focus {
      background-color: #dc2626;
      border-color: #dc2626;
      color: white;
      box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
    }
    
    /* Ensure delete buttons are visible */
    .post-actions .btn-outline-danger {
      border-width: 1px;
      font-weight: 500;
    }
    
    /* Empty State Styling */
    .empty-state {
      padding: 3rem 2rem;
      text-align: center;
      color: var(--text-medium);
    }
    
    .empty-icon {
      font-size: 3rem;
      color: var(--text-muted);
      margin-bottom: 1rem;
    }
    
    .empty-state h3 {
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }
    
    /* Modal Improvements */
    .modal-content {
      border-radius: 15px;
      border: none;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    }
    
    .modal-header {
      border-bottom: 1px solid var(--border-color);
      padding: 1.5rem 2rem;
    }
    
    .modal-body {
      padding: 2rem;
    }
    
    .modal-footer {
      border-top: 1px solid var(--border-color);
      padding: 1.5rem 2rem;
    }
    
    /* Toast Notifications */
    .toast-container {
      position: fixed !important;
      top: 20px !important;
      right: 20px !important;
      z-index: 9999 !important;
      pointer-events: none;
    }
    
    .toast {
      border-radius: 10px;
      border: none;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      pointer-events: auto;
      min-width: 300px;
      background-color: white;
    }
    
    .toast-header {
      border-bottom: 1px solid var(--border-color);
      padding: 0.75rem 1rem;
      background-color: white;
    }
    
    .toast-body {
      padding: 1rem;
      background-color: white;
    }
    
    /* Ensure toast is always on top */
    .toast-container .toast {
      position: relative;
      z-index: 10000;
    }
    
    /* Hide toast by default */
    .toast {
      display: none;
    }
    
    /* Show toast when it has the 'show' class */
    .toast.show {
      display: block;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .profile-meta {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      .post-card-header {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
      }
      
      .post-meta {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
      }
      
      .post-actions {
        justify-content: flex-start;
      }
      
      .cards-grid {
        grid-template-columns: 1fr;
      }
    }
    
    /* Loading States */
    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
    }
    
    /* Animation Classes */
    .fade-in-up {
      animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Modal Mobile Fixes */
    @media (max-width: 768px) {
      #editProfileModal .modal-dialog {
        max-width: 98% !important;
        margin: 0.5rem auto !important;
        max-height: 95vh !important;
      }
      
      #editProfileModal .modal-content {
        max-height: 95vh !important;
        border-radius: 10px;
      }
      
      #editProfileModal .modal-body {
        max-height: calc(95vh - 140px) !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        padding: 1rem !important;
      }
      
      #editProfileModal .modal-header {
        padding: 1rem !important;
        border-bottom: 1px solid #dee2e6;
      }
      
      #editProfileModal .modal-footer {
        padding: 1rem !important;
        border-top: 1px solid #dee2e6;
        background: #fff;
        position: sticky;
        bottom: 0;
        z-index: 10;
      }
      
      #editProfileModal .btn {
        width: 100% !important;
        margin-bottom: 0.5rem !important;
        padding: 0.75rem 1rem !important;
        font-size: 16px !important;
      }
      
      #editProfileModal .btn:last-child {
        margin-bottom: 0 !important;
      }
      
      #editProfileModal .form-control {
        padding: 0.75rem !important;
        font-size: 16px !important;
        border-radius: 8px !important;
      }
      
      #editProfileModal .row .col-md-6 {
        width: 100% !important;
        margin-bottom: 1rem !important;
      }
      
      #editProfileModal .row .col-md-6:last-child {
        margin-bottom: 0 !important;
      }
      
      #editProfileModal .avatar-drop-zone {
        width: 100px !important;
        height: 100px !important;
      }
    }
    
    @media (max-width: 480px) {
      #editProfileModal .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
        height: 100vh !important;
      }
      
      #editProfileModal .modal-content {
        height: 100vh !important;
        border-radius: 0 !important;
      }
      
      #editProfileModal .modal-body {
        max-height: calc(100vh - 140px) !important;
        padding: 1rem !important;
      }
      
      #editProfileModal .modal-header {
        padding: 1rem !important;
      }
      
      #editProfileModal .modal-footer {
        padding: 1rem !important;
      }
      
      #editProfileModal .btn {
        padding: 1rem !important;
        font-size: 16px !important;
      }
      
      #editProfileModal .form-control {
        padding: 1rem !important;
        font-size: 16px !important;
      }
      
      #editProfileModal .avatar-drop-zone {
        width: 80px !important;
        height: 80px !important;
      }
    }
    
    /* Ensure modal scrollbar is visible */
    #editProfileModal .modal-body::-webkit-scrollbar {
      width: 6px;
    }
    
    #editProfileModal .modal-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }
    
    #editProfileModal .modal-body::-webkit-scrollbar-thumb {
      background: #007bff;
      border-radius: 3px;
    }
    
    #editProfileModal .modal-body::-webkit-scrollbar-thumb:hover {
      background: #0056b3;
    }
    
    /* Prevent body scroll when modal is open */
    body.modal-open {
      overflow: hidden !important;
    }
    </style>
</body>
</html>

<?php
// حذف الخدمة إذا تم إرسال الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service_id']) && $isOwnProfile) {
    $deleteId = (int)$_POST['delete_service_id'];
    $allServices = file_exists($servicesFile) ? json_decode(file_get_contents($servicesFile), true) : [];
    $allServices = array_filter($allServices, function($srv) use ($deleteId, $currentUser) {
        // فقط صاحب الخدمة يمكنه حذفها
        return !($srv['id'] == $deleteId && $srv['userId'] == $currentUser['id']);
    });
    file_put_contents($servicesFile, json_encode(array_values($allServices), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    // إعادة تحميل الصفحة بعد الحذف
    echo '<meta http-equiv="refresh" content="0">';
    exit;
}
?> 