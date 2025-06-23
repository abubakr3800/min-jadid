<?php
session_start();
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
if (!$db->isAdmin($currentUser['email'])) {
    echo '<h2>Access Denied</h2><p>You do not have permission to view this page.</p>';
    exit;
}

$users = $db->getAllUsersForAdmin();
$stats = $db->getAdminStats();
$posts = $db->getPosts();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الإدارة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .admin-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .admin-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #2563eb;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
            font-weight: 500;
        }
        
        .btn-admin {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .btn-view-stats {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-delete-user {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-delete-post {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }
        
        .admin-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .badge-admin {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .badge-admin.admin {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .badge-admin.user {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }
        
        .nav-tabs {
            border: none;
            margin-bottom: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-radius: 12px 12px 0 0;
            padding: 1rem 2rem;
            font-weight: 500;
            color: #64748b;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .tab-content {
            background: white;
            border-radius: 0 12px 12px 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="text-center">
                <h1 class="admin-title">لوحة تحكم الإدارة</h1>
                <p>مرحباً <?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?> - إدارة الموقع</p>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Site Statistics -->
        <div class="admin-section">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i>
                إحصائيات الموقع
            </h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalUsers'] ?></div>
                    <div class="stat-label">إجمالي المستخدمين</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalPosts'] ?></div>
                    <div class="stat-label">إجمالي المقالات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalComments'] ?></div>
                    <div class="stat-label">إجمالي التعليقات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalLikes'] ?></div>
                    <div class="stat-label">إجمالي الإعجابات</div>
                </div>
            </div>
        </div>

        <!-- Management Tabs -->
        <div class="admin-section">
            <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                        <i class="fas fa-users"></i> إدارة المستخدمين
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="articles-tab" data-bs-toggle="tab" data-bs-target="#articles" type="button" role="tab">
                        <i class="fas fa-newspaper"></i> إدارة المقالات
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="adminTabsContent">
                <!-- Users Management Tab -->
                <div class="tab-pane fade show active" id="users" role="tabpanel">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i>
                        جدول المستخدمين
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-hover admin-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>عدد المقالات</th>
                                    <th>الإعجابات المستلمة</th>
                                    <th>المشاهدات</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>صلاحيات</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $i => $user): ?>
                                <tr id="user-row-<?= $user['id'] ?>">
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= $user['postsCount'] ?></td>
                                    <td><?= $user['totalLikesReceived'] ?></td>
                                    <td><?= $user['totalViews'] ?></td>
                                    <td><?= $user['formattedCreatedAt'] ?></td>
                                    <td>
                                        <?php if (isset($user['isAdmin']) && $user['isAdmin']): ?>
                                            <span class="badge badge-admin admin">مدير</span>
                                        <?php else: ?>
                                            <span class="badge badge-admin user">مستخدم</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn-admin btn-view-stats view-stats-btn" data-user-id="<?= $user['id'] ?>">
                                                <i class="fas fa-chart-line"></i> إحصائيات
                                            </button>
                                            <?php if ($user['id'] != $currentUser['id']): ?>
                                            <button class="btn-admin btn-delete-user delete-user-btn" data-user-id="<?= $user['id'] ?>">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Articles Management Tab -->
                <div class="tab-pane fade" id="articles" role="tabpanel">
                    <h3 class="section-title">
                        <i class="fas fa-newspaper"></i>
                        إدارة المقالات
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-hover admin-table" id="articlesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>العنوان</th>
                                    <th>الكاتب</th>
                                    <th>التصنيف</th>
                                    <th>الإعجابات</th>
                                    <th>المشاهدات</th>
                                    <th>التقييم</th>
                                    <th>تاريخ النشر</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $i => $post): ?>
                                <tr id="post-row-<?= $post['id'] ?>">
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($post['title']) ?></td>
                                    <td><?= htmlspecialchars($post['user']['firstName'] . ' ' . $post['user']['lastName']) ?></td>
                                    <td><?= htmlspecialchars($post['category']['name'] ?? 'غير محدد') ?></td>
                                    <td><?= $post['likes'] ?? 0 ?></td>
                                    <td><?= $post['views'] ?? 0 ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?= number_format($post['averageRating'] ?? 0, 1) ?> ⭐
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($post['createdAt'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="post.php?id=<?= $post['id'] ?>" class="btn-admin btn-view-stats" target="_blank">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            <button class="btn-admin btn-delete-post delete-post-btn" data-post-id="<?= $post['id'] ?>">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="userStatsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">إحصائيات المستخدم</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body" id="userStatsContent">
            <div class="text-center text-muted">جاري التحميل...</div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تأكيد حذف المستخدم</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                هل أنت متأكد أنك تريد حذف هذا المستخدم وجميع بياناته؟ لا يمكن التراجع!
            </div>
            <input type="hidden" id="deleteUserId" value="">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">
                <i class="fas fa-trash"></i> حذف نهائي
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="deletePostModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تأكيد حذف المقال</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                هل أنت متأكد أنك تريد حذف هذا المقال؟ سيتم حذف جميع التعليقات والإعجابات المرتبطة به.
            </div>
            <input type="hidden" id="deletePostId" value="">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="button" class="btn btn-warning" id="confirmDeletePostBtn">
                <i class="fas fa-trash"></i> حذف المقال
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#usersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
            },
            pageLength: 10,
            order: [[0, 'asc']],
            responsive: true
        });
        
        $('#articlesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
            },
            pageLength: 10,
            order: [[0, 'asc']],
            responsive: true
        });

        // View user stats
        $('.view-stats-btn').on('click', function() {
            const userId = $(this).data('user-id');
            const modal = new bootstrap.Modal(document.getElementById('userStatsModal'));
            $('#userStatsContent').html('<div class="text-center text-muted">جاري التحميل...</div>');
            modal.show();
            
            fetch('api/users.php?action=stats&userId=' + userId)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        let html = `<div class="row">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number">${stats.postsCount}</div>
                                    <div class="stat-label">عدد المقالات</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number">${stats.totalLikes}</div>
                                    <div class="stat-label">إجمالي الإعجابات</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number">${stats.totalViews}</div>
                                    <div class="stat-label">إجمالي المشاهدات</div>
                                </div>
                            </div>
                        </div>`;
                        
                        if (stats.posts && stats.posts.length > 0) {
                            html += `<div class="mt-4">
                                <h6>مقالات المستخدم:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>العنوان</th><th>الإعجابات</th><th>المشاهدات</th></tr>
                                        </thead>
                                        <tbody>`;
                            stats.posts.forEach(post => {
                                html += `<tr><td>${post.title}</td><td>${post.likes ?? 0}</td><td>${post.views ?? 0}</td></tr>`;
                            });
                            html += `</tbody></table></div></div>`;
                        }
                        $('#userStatsContent').html(html);
                    } else {
                        $('#userStatsContent').html(`<div class="alert alert-danger">${data.error}</div>`);
                    }
                });
        });

        // Delete user
        $('.delete-user-btn').on('click', function() {
            const userId = $(this).data('user-id');
            $('#deleteUserId').val(userId);
            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        });
        
        $('#confirmDeleteUserBtn').on('click', function() {
            const userId = $('#deleteUserId').val();
            fetch('api/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $(`#user-row-${userId}`).fadeOut();
                    bootstrap.Modal.getInstance(document.getElementById('deleteUserModal')).hide();
                } else {
                    alert(data.error || 'حدث خطأ أثناء حذف المستخدم');
                }
            });
        });

        // Delete post
        $('.delete-post-btn').on('click', function() {
            const postId = $(this).data('post-id');
            $('#deletePostId').val(postId);
            const modal = new bootstrap.Modal(document.getElementById('deletePostModal'));
            modal.show();
        });
        
        $('#confirmDeletePostBtn').on('click', function() {
            const postId = $('#deletePostId').val();
            fetch('api/posts.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', postId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $(`#post-row-${postId}`).fadeOut();
                    bootstrap.Modal.getInstance(document.getElementById('deletePostModal')).hide();
                } else {
                    alert(data.error || 'حدث خطأ أثناء حذف المقال');
                }
            });
        });
    });
    </script>
</body>
</html> 