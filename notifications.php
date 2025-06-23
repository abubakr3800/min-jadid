<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
$auth = new Auth();
$db = new Database();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}
$user = $auth->getCurrentUser();
$userId = $user['id'];
$notifications = $db->getUserNotifications($userId);

// Mark all as read if requested
if (isset($_POST['mark_all_read'])) {
    foreach ($notifications as $notif) {
        if (!$notif['read']) {
            $db->markNotificationRead($notif['id'], $userId);
        }
    }
    header('Location: notifications.php');
    exit();
}

// Handle delete notification
if (isset($_POST['delete_notif_id'])) {
    $notifId = $_POST['delete_notif_id'];
    $db->deleteNotification($notifId, $userId);
    header('Location: notifications.php');
    exit();
}

// Separate active (last hour) and old notifications
$active = [];
$old = [];
$now = time();
foreach ($notifications as $notif) {
    $created = strtotime($notif['createdAt']);
    if ($now - $created <= 3600) {
        $active[] = $notif;
    } else {
        $old[] = $notif;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كل الإشعارات - من جديد</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notif-page-container { max-width: 600px; margin: 2rem auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 2rem 1.5rem; }
        .notif-section-title { font-size: 1.3rem; font-weight: bold; color: #4f8cff; margin-bottom: 1rem; }
        .notif-list { list-style: none; margin: 0; padding: 0; }
        .notif-item { padding: 1rem; border-bottom: 1px solid #f3f3f3; display: flex; align-items: center; gap: 0.7rem; }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread { background: #f0f6ff; font-weight: bold; }
        .notif-item.active { background: #e6f7ff; border-right: 4px solid #4f8cff; }
        .notif-item .fa-bell { color: #4f8cff; font-size: 1.2rem; }
        .notif-message { flex: 1; }
        .notif-date { font-size: 0.85rem; color: #888; margin-right: 1rem; }
        .notif-link { color: #222; text-decoration: none; display: flex; align-items: center; width: 100%; }
        .notif-link:hover { background: #f5faff; }
        .notif-empty { text-align: center; color: #888; padding: 2rem 0; }
        .notif-actions { text-align: left; margin-bottom: 1.5rem; }
        .btn-mark-all { background: #4f8cff; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.2rem; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-mark-all:hover { background: #764ba2; }
        .btn-delete-notif {
            background: none;
            border: none;
            color: #b91c1c;
            font-size: 1rem;
            cursor: pointer;
            margin-left: 0.2rem;
            transition: color 0.2s;
            vertical-align: middle;
        }
        .btn-delete-notif:hover {
            color: #ff5252;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <header class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <img src="img/logo.svg" alt="شعار من جديد">
                </a>
                <ul class="navbar-nav">
                    <li><a href="index.php" class="nav-link">الرئيسية</a></li>
                    <li><a href="explore.php" class="nav-link">استكشاف</a></li>
                    <li><a href="about.php" class="nav-link">عن المنصة</a></li>
                    <li><a href="profile.php" class="nav-link">الملف الشخصي</a></li>
                    <li><a href="logout.php" class="btn btn-outline-primary">تسجيل الخروج</a></li>
                </ul>
            </div>
        </header>
        <main class="main-content">
            <div class="notif-page-container">
                <div class="notif-actions">
                    <form method="post" style="display:inline;">
                        <button type="submit" name="mark_all_read" class="btn-mark-all"><i class="fas fa-check-double"></i> تعليم الكل كمقروء</button>
                    </form>
                </div>
                <div class="notif-section-title"><i class="fas fa-bell"></i> الإشعارات النشطة (آخر ساعة)</div>
                <?php if (empty($active)): ?>
                    <div class="notif-empty">لا توجد إشعارات نشطة حالياً</div>
                <?php else: ?>
                    <ul class="notif-list">
                        <?php foreach ($active as $notif): ?>
                        <li class="notif-item active<?= !$notif['read'] ? ' unread' : '' ?>">
                            <form method="post" style="display:inline; margin-left:0.5rem;">
                                <input type="hidden" name="delete_notif_id" value="<?= htmlspecialchars($notif['id']) ?>">
                                <button type="submit" class="btn-delete-notif" title="حذف الإشعار" onclick="return confirm('هل تريد حذف هذا الإشعار؟');"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            <a class="notif-link" href="<?= htmlspecialchars($notif['link']) ?>">
                                <i class="fas fa-bell"></i>
                                <span class="notif-message"> <?= htmlspecialchars($notif['message']) ?> </span>
                                <span class="notif-date"> <?= date('Y/m/d H:i', strtotime($notif['createdAt'])) ?> </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="notif-section-title" style="margin-top:2.5rem;"><i class="fas fa-history"></i> الإشعارات القديمة</div>
                <?php if (empty($old)): ?>
                    <div class="notif-empty">لا توجد إشعارات قديمة</div>
                <?php else: ?>
                    <ul class="notif-list">
                        <?php foreach ($old as $notif): ?>
                        <li class="notif-item<?= !$notif['read'] ? ' unread' : '' ?>">
                            <form method="post" style="display:inline; margin-left:0.5rem;">
                                <input type="hidden" name="delete_notif_id" value="<?= htmlspecialchars($notif['id']) ?>">
                                <button type="submit" class="btn-delete-notif" title="حذف الإشعار" onclick="return confirm('هل تريد حذف هذا الإشعار؟');"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            <a class="notif-link" href="<?= htmlspecialchars($notif['link']) ?>">
                                <i class="fas fa-bell"></i>
                                <span class="notif-message"> <?= htmlspecialchars($notif['message']) ?> </span>
                                <span class="notif-date"> <?= date('Y/m/d H:i', strtotime($notif['createdAt'])) ?> </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 