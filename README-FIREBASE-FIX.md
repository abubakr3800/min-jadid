# 🔧 حل سريع لمشكلة Firebase Domain Authorization

## المشكلة
```
FirebaseError: Firebase: This domain is not authorized for OAuth operations for your Firebase project. Edit the list of authorized domains from the Firebase console. (auth/unauthorized-domain).
```

## الحل السريع (5 دقائق)

### 1. اذهب إلى Firebase Console
- افتح: https://console.firebase.google.com/
- اختر مشروعك: **min-jaded**

### 2. أضف النطاقات المصرح بها
- اذهب إلى: **Authentication** → **Settings** → **Authorized domains**
- أضف هذه النطاقات:
  ```
  localhost
  localhost:8000
  localhost:3000
  localhost:8080
  127.0.0.1
  127.0.0.1:8000
  127.0.0.1:3000
  127.0.0.1:8080
  ```
- احفظ التغييرات

### 3. انتظر 2-3 دقائق
- امسح ذاكرة التخزين المؤقت للمتصفح
- جرب مرة أخرى

## الحل المؤقت للاختبار

### استخدم صفحة الاختبار المحسنة
- اذهب إلى: `http://localhost:8080/test-auth-simple.html`
- اختر Google أو Facebook
- أدخل بياناتك
- اضغط "إكمال تسجيل الدخول"

### أو استخدم صفحة الاختبار البسيط
- اذهب إلى: `http://localhost:8080/simple-test.html`
- اضغط "اختبار تسجيل الدخول"

## المنافذ المتاحة

### الخادم الرئيسي
```bash
php -S localhost:8000
```

### الخادم البديل
```bash
php -S localhost:8080
```

### الخادم الثالث
```bash
php -S localhost:3000
```

## الروابط المهمة

- **صفحة تسجيل الدخول**: `http://localhost:8000/login.php`
- **صفحة الاختبار المحسنة**: `http://localhost:8080/test-auth-simple.html`
- **صفحة الاختبار البسيط**: `http://localhost:8080/simple-test.html`
- **الصفحة الرئيسية**: `http://localhost:8000/index.php`

## ملاحظات مهمة

✅ **localhost بدون منفذ** مسموح به تلقائياً  
❌ **localhost مع منفذ** يحتاج إضافة يدوية  
⏰ **التغييرات قد تستغرق بضع دقائق** لتطبيقها  
🔄 **امسح ذاكرة التخزين المؤقت** للمتصفح  

## في حالة استمرار المشكلة

1. تأكد من إضافة جميع النطاقات المذكورة أعلاه
2. انتظر 5 دقائق على الأقل
3. امسح ذاكرة التخزين المؤقت للمتصفح
4. استخدم صفحة الاختبار المؤقتة
5. تحقق من console المتصفح للأخطاء الإضافية

## اختبار النظام

بعد إضافة النطاقات، جرب:
1. `http://localhost:8000/login.php` - تسجيل الدخول العادي
2. `http://localhost:8080/test-auth-simple.html` - اختبار محسن
3. `http://localhost:8080/simple-test.html` - اختبار بسيط 