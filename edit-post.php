<?php
session_start();
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$db = new Database();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// Get post ID
$postId = $_GET['id'] ?? null;
if (!$postId) {
    header('Location: index.php');
    exit();
}

// Get post
$post = $db->getPostById($postId);
if (!$post) {
    header('Location: index.php');
    exit();
}

// Only author can edit
if (!$isLoggedIn || $post['userId'] != $currentUser['id']) {
    header('Location: post.php?id=' . $postId);
    exit();
}

// Get categories
$categories = $db->getCategories();

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = $_POST['categoryId'] ?? '';
    $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    // Debug: Log the received data
    error_log("Edit Post Debug - Title: " . $title);
    error_log("Edit Post Debug - Content length: " . strlen($content));
    error_log("Edit Post Debug - Category ID: " . $categoryId);

    if ($title && $content && $categoryId) {
        // Handle cover image upload
        $coverImagePath = $post['coverImage'] ?? '';
        if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['coverImage']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $dir = 'img/covers/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $filename = 'cover_' . $postId . '_' . time() . '.' . $ext;
                $target = $dir . $filename;
                if (move_uploaded_file($_FILES['coverImage']['tmp_name'], $target)) {
                    $coverImagePath = $dir . $filename;
                }
            }
        }
        
        $updateData = [
            'id' => $postId,
            'title' => $title,
            'content' => $content,
            'categoryId' => $categoryId,
            'tags' => $tags,
            'coverImage' => $coverImagePath
        ];
        
        // Debug: Log the update data
        error_log("Edit Post Debug - Update data: " . json_encode($updateData));
        
        $result = $db->updatePost($updateData);
        if ($result) {
            $success = 'تم تحديث المقال بنجاح';
            header('Location: post.php?id=' . $postId);
            exit();
        } else {
            $error = 'حدث خطأ أثناء تحديث المقال';
            error_log("Edit Post Error - Failed to update post: " . $postId);
        }
    } else {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
        if (!$title) $error .= ' - العنوان مطلوب';
        if (!$content) $error .= ' - المحتوى مطلوب';
        if (!$categoryId) $error .= ' - التصنيف مطلوب';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المقال - من جديد</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/omzsk362r3utt1ymlppi704ycstkfj52ky9f5z80bv7sdcub/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    tinymce.init({
      selector: '#content',
      directionality: 'rtl',
      plugins: 'lists link image code table advlist autolink charmap preview anchor searchreplace visualblocks fullscreen insertdatetime media table paste help wordcount codesample',
      toolbar: 'undo redo | blocks | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media codesample | table blockquote | removeformat | preview fullscreen',
      height: 400,
      menubar: false,
      branding: false,
      images_upload_url: 'api/upload-image.php',
      automatic_uploads: true,
      images_upload_handler: function (blobInfo, success, failure) {
        var xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.open('POST', 'api/upload-image.php');
        var formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        xhr.onload = function() {
          var json;
          if (xhr.status != 200) {
            failure('HTTP Error: ' + xhr.status);
            return;
          }
          json = JSON.parse(xhr.responseText);
          if (!json || typeof json.location != 'string') {
            failure('Invalid JSON: ' + xhr.responseText);
            return;
          }
          success(json.location);
        };
        xhr.onerror = function() {
          failure('Image upload failed due to a XHR Transport error.');
        };
        xhr.send(formData);
      },
      content_style: 'body { font-family: Tajawal, Arial, sans-serif; font-size: 1.1rem; direction: rtl; line-height: 1.6; }',
      block_formats: 'فقرة=p; عنوان 1=h1; عنوان 2=h2; عنوان 3=h3; اقتباس=blockquote; كود=pre',
      language: 'ar',
      placeholder: 'اكتب محتوى المقال هنا...',
      setup: function(editor) {
        // Ensure content is saved when form is submitted
        editor.on('change', function() {
          editor.save();
        });
      }
    });
    
    let coverCropper, croppedCoverBlob;
    document.addEventListener('DOMContentLoaded', function() {
      var coverInput = document.getElementById('coverImage');
      var coverPreview = document.getElementById('coverPreview');
      var coverDropZone = document.getElementById('coverDropZone');
      var coverError = document.getElementById('coverError');
      var coverCropperModal = document.getElementById('coverCropperModal');
      var coverCropperImg = document.getElementById('coverCropperImg');
      var cropCoverBtn = document.getElementById('cropCoverBtn');
      let coverCropperModalInstance;
      
      function openCoverCropper(file) {
        if (!coverCropperImg || !coverCropperModal) return;
        coverError && (coverError.textContent = '');
        var reader = new FileReader();
        reader.onload = function(e) {
          coverCropperImg.src = e.target.result;
          coverCropperModalInstance = new bootstrap.Modal(coverCropperModal);
          coverCropperModalInstance.show();
        };
        reader.readAsDataURL(file);
      }
      
      function validateCoverImage(file) {
        if (!coverError) return false;
        coverError.textContent = '';
        if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
          coverError.textContent = 'الرجاء اختيار صورة بصيغة jpg, png, gif, أو webp';
          return false;
        }
        if (file.size > 2 * 1024 * 1024) {
          coverError.textContent = 'الحد الأقصى لحجم الصورة هو 2 ميجابايت';
          return false;
        }
        return true;
      }
      
      if (coverDropZone && coverInput && coverPreview) {
        coverDropZone.addEventListener('dragover', function(e) {
          e.preventDefault();
          coverDropZone.style.borderColor = '#667eea';
          coverDropZone.style.background = '#eef2ff';
        });
        
        coverDropZone.addEventListener('dragleave', function(e) {
          e.preventDefault();
          coverDropZone.style.borderColor = '#aaa';
          coverDropZone.style.background = '#fafbfc';
        });
        
        coverDropZone.addEventListener('drop', function(e) {
          e.preventDefault();
          coverDropZone.style.borderColor = '#aaa';
          coverDropZone.style.background = '#fafbfc';
          var file = e.dataTransfer.files[0];
          if (file && validateCoverImage(file)) {
            openCoverCropper(file);
          }
        });
        
        coverInput.addEventListener('change', function() {
          if (this.files && this.files[0] && validateCoverImage(this.files[0])) {
            openCoverCropper(this.files[0]);
          }
        });
      }
      
      if (coverCropperModal) {
        coverCropperModal.addEventListener('shown.bs.modal', function () {
          if (coverCropper) coverCropper.destroy();
          if (!coverCropperImg) return;
          coverCropper = new Cropper(coverCropperImg, {
            aspectRatio: 16/9,
            viewMode: 1,
            dragMode: 'move',
            guides: true,
            center: true,
            background: false,
            highlight: false,
            cropBoxResizable: true,
            cropBoxMovable: true,
            minContainerWidth: 400,
            minContainerHeight: 225
          });
        });
      }
      
      cropCoverBtn && cropCoverBtn.addEventListener('click', function() {
        if (coverCropper) {
          coverCropper.getCroppedCanvas({ width: 800, height: 450, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
            croppedCoverBlob = blob;
            var url = URL.createObjectURL(blob);
            coverPreview && (coverPreview.src = url);
            // Set a fake File in the input for form submission
            var dt = new DataTransfer();
            var file = new File([blob], 'cover.png', { type: 'image/png' });
            dt.items.add(file);
            coverInput && (coverInput.files = dt.files);
            coverCropperModalInstance && coverCropperModalInstance.hide();
          }, 'image/png');
        }
      });
      
      // Ensure TinyMCE content is saved to textarea before submit
      var editForm = document.querySelector('form[method="post"]');
      if (editForm) {
        editForm.addEventListener('submit', function(e) {
          // Save TinyMCE content before form submission
          if (window.tinymce && tinymce.get('content')) {
            tinymce.get('content').save();
          }
          
          // Validate form
          var title = document.getElementById('title').value.trim();
          var content = document.getElementById('content').value.trim();
          var category = document.getElementById('categoryId').value;
          
          if (!title) {
            e.preventDefault();
            alert('يرجى إدخال عنوان المقال');
            return false;
          }
          
          if (!content) {
            e.preventDefault();
            alert('يرجى إدخال محتوى المقال');
            return false;
          }
          
          if (!category) {
            e.preventDefault();
            alert('يرجى اختيار تصنيف للمقال');
            return false;
          }
        });
      }
      
      // Check if TinyMCE loaded successfully
      setTimeout(function() {
        if (!window.tinymce || !tinymce.get('content')) {
          console.log('TinyMCE not loaded, using fallback textarea');
          var contentTextarea = document.getElementById('content');
          if (contentTextarea) {
            contentTextarea.style.display = 'block';
            contentTextarea.style.minHeight = '300px';
          }
        }
      }, 2000);
    });
    </script>
    <style>
        .edit-post-container { 
            max-width: 800px; 
            margin: 2rem auto; 
            background: #fff; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            padding: 2.5rem; 
        }
        .edit-post-container h2 { 
            margin-bottom: 2rem; 
            color: #2d3748;
            text-align: center;
        }
        .form-group { 
            margin-bottom: 1.5rem; 
        }
        .form-label { 
            display: block; 
            margin-bottom: 0.75rem; 
            font-weight: 600; 
            color: #4a5568;
        }
        .form-control, textarea { 
            width: 100%; 
            padding: 0.875rem; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px; 
            font-size: 1rem; 
            transition: border-color 0.2s ease;
        }
        .form-control:focus, textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        textarea { 
            min-height: 300px; 
            resize: vertical; 
            font-family: 'Tajawal', Arial, sans-serif;
        }
        .form-actions { 
            display: flex; 
            gap: 1rem; 
            margin-top: 2rem; 
            justify-content: center;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #718096;
            border: none;
        }
        .btn-secondary:hover {
            background: #4a5568;
        }
        .alert { 
            padding: 1rem 1.25rem; 
            border-radius: 8px; 
            margin-bottom: 1.5rem; 
            border: none;
        }
        .alert-success { 
            background: #c6f6d5; 
            color: #22543d; 
        }
        .alert-danger { 
            background: #fed7d7; 
            color: #742a2a; 
        }
        .form-text {
            color: #718096;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .edit-post-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            .form-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
        .modal-body { 
            max-height: 70vh; 
            overflow-y: auto; 
        }
    </style>
</head>
<body>
    <div class="edit-post-container">
        <h2><i class="fas fa-edit"></i> تعديل المقال</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="title">العنوان</label>
                <input class="form-control" type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            <div class="form-group text-center">
                <label class="form-label" for="coverImage">صورة الغلاف</label><br>
                <div id="coverDropZone" style="width: 130px; height: 90px; border: 2px dashed #aaa; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem; background: #fafbfc; position: relative; cursor: pointer;">
                  <img id="coverPreview" src="<?= !empty($post['coverImage']) ? htmlspecialchars($post['coverImage']) : 'img/cover-placeholder.png' ?>" alt="صورة الغلاف" style="width: 120px; height: 80px; border-radius: 8px; object-fit: cover;">
                  <input class="form-control" type="file" id="coverImage" name="coverImage" accept="image/*" style="opacity:0;position:absolute;top:0;left:0;width:100%;height:100%;cursor:pointer;">
                </div>
                <div id="coverError" class="text-danger mt-2" style="font-size:0.95em;"></div>
            </div>
            <div class="form-group">
                <label class="form-label" for="content">المحتوى</label>
                <textarea class="form-control" id="content" name="content" required style="min-height: 300px;"><?= htmlspecialchars($post['content']) ?></textarea>
                <div class="form-text">إذا لم يظهر محرر النصوص المتقدم، يمكنك الكتابة مباشرة في هذا الحقل</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="categoryId">التصنيف</label>
                <select class="form-control" id="categoryId" name="categoryId" required>
                    <option value="">اختر تصنيفاً</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['categoryId'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="tags">الوسوم (مفصولة بفاصلة)</label>
                <input class="form-control" type="text" id="tags" name="tags" value="<?= htmlspecialchars(is_array($post['tags']) ? implode(',', $post['tags']) : $post['tags']) ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ التعديلات</button>
                <a href="post.php?id=<?= $postId ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>

    <!-- Cover Cropper Modal -->
    <div class="modal fade" id="coverCropperModal" tabindex="-1" aria-labelledby="coverCropperModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="coverCropperModalLabel">تعديل صورة الغلاف</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body text-center">
            <div style="width: 400px; height: 225px; margin: 0 auto;">
              <img id="coverCropperImg" src="" style="max-width:100%; max-height:100%; display:block; margin:0 auto;">
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-primary" id="cropCoverBtn">استخدام الصورة</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          </div>
        </div>
      </div>
    </div>
</body>
</html> 