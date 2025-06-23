# استكشاف الأخطاء - Troubleshooting Guide

## المشاكل المبلغ عنها:

### 1. قص الصور في edit-profile.php لا يعمل
### 2. صفحة المقال الجديد تظهر طبقة شفافة تمنع التفاعل عند النقر على "إضافة صورة"

## الحلول المطبقة:

### ✅ تم إصلاح edit-profile.php:
- إضافة متغيرات قص الصور المفقودة
- إضافة تنظيف النافذة المنبثقة
- إضافة رسائل تصحيح للمساعدة في تحديد المشاكل

### ✅ تم إصلاح new-post.php:
- إضافة تنظيف النافذة المنبثقة
- إزالة طبقة الخلفية المتبقية
- إضافة رسائل تصحيح

## كيفية اختبار الإصلاحات:

### 1. اختبار قص الصور الشخصية:
1. افتح `edit-profile.php`
2. اضغط F12 لفتح وحدة التحكم
3. ابحث عن الرسائل التالية:
   ```
   Edit Profile DOM loaded
   Bootstrap available: true
   Cropper available: true
   Avatar elements found: {avatarInput: true, avatarPreview: true, ...}
   ```
4. اضغط "تغيير الصورة"
5. اختر صورة
6. يجب أن تظهر نافذة قص الصورة

### 2. اختبار قص صور الغلاف:
1. افتح `new-post.php`
2. اضغط F12 لفتح وحدة التحكم
3. ابحث عن الرسائل التالية:
   ```
   New Post DOM loaded
   Bootstrap available: true
   Cropper available: true
   Cover elements found: {coverInput: true, coverPreview: true, ...}
   ```
4. اضغط على منطقة رفع الصورة
5. اختر صورة
6. يجب أن تظهر نافذة قص الصورة بدون طبقة شفافة

## إذا استمرت المشاكل:

### للمشكلة الأولى (edit-profile.php):
1. تحقق من وحدة التحكم للأخطاء
2. تأكد من تحميل جميع المكتبات
3. جرب ملف الاختبار `test-cropper.html`

### للمشكلة الثانية (new-post.php):
1. تحقق من وحدة التحكم للأخطاء
2. ابحث عن أخطاء JavaScript
3. تأكد من عدم وجود نوافذ منبثقة معلقة

## أوامر التصحيح:

### في وحدة التحكم، اكتب:
```javascript
// فحص المكتبات
console.log('Bootstrap:', typeof bootstrap);
console.log('Cropper:', typeof Cropper);

// فحص العناصر
console.log('Modal:', document.getElementById('coverCropperModal'));
console.log('Modal:', document.getElementById('avatarCropperModal'));

// إزالة الطبقة الشفافة يدوياً
const backdrop = document.querySelector('.modal-backdrop');
if (backdrop) backdrop.remove();
document.body.classList.remove('modal-open');
```

## إذا لم تعمل الإصلاحات:

1. **جرب ملف الاختبار**: افتح `test-cropper.html` في المتصفح
2. **تحقق من المتصفح**: تأكد من استخدام متصفح حديث
3. **امسح التخزين المؤقت**: اضغط Ctrl+F5 لإعادة تحميل الصفحة
4. **تحقق من JavaScript**: تأكد من تفعيل JavaScript

## معلومات إضافية:

### المتطلبات:
- Bootstrap 5.1.3
- Cropper.js 1.5.13
- متصفح حديث يدعم ES6+

### الملفات المحدثة:
- `edit-profile.php` - إصلاح قص الصور الشخصية
- `new-post.php` - إصلاح طبقة الخلفية
- `test-cropper.html` - ملف اختبار منفصل

### الدعم:
إذا استمرت المشاكل، يرجى مشاركة:
1. رسائل وحدة التحكم
2. نوع المتصفح والإصدار
3. أي أخطاء تظهر 