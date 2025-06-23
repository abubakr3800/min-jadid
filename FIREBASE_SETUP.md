# Firebase Setup Guide

This guide will help you set up Firebase for the Knowledge Hub website to enable real-time data and user interactions.

## Prerequisites

1. A Google account
2. PHP 7.4 or higher
3. Composer installed
4. XAMPP or similar local server

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a project" or "Add project"
3. Enter a project name (e.g., "knowledge-hub")
4. Choose whether to enable Google Analytics (recommended)
5. Click "Create project"

## Step 2: Enable Authentication

1. In your Firebase project, go to "Authentication" in the left sidebar
2. Click "Get started"
3. Go to the "Sign-in method" tab
4. Enable "Email/Password" authentication
5. Click "Save"

## Step 3: Set up Realtime Database

1. In your Firebase project, go to "Realtime Database" in the left sidebar
2. Click "Create database"
3. Choose a location (select the closest to your users)
4. Start in "test mode" for development (you can secure it later)
5. Click "Done"

## Step 4: Get Firebase Configuration

1. In your Firebase project, click the gear icon next to "Project Overview"
2. Select "Project settings"
3. Scroll down to "Your apps" section
4. Click the web icon (</>)
5. Register your app with a nickname (e.g., "knowledge-hub-web")
6. Copy the Firebase configuration object

## Step 5: Download Service Account Key

1. In Project settings, go to the "Service accounts" tab
2. Click "Generate new private key"
3. Save the JSON file as `firebase-credentials.json` in the `config/` folder
4. **IMPORTANT**: Never commit this file to version control!

## Step 6: Install Dependencies

Run the following command in your project root:

```bash
composer install
```

## Step 7: Update Configuration

1. Open `config/firebase-config.php`
2. Replace `'https://your-project-id.firebaseio.com'` with your actual Firebase database URL
3. Make sure the path to `firebase-credentials.json` is correct

## Step 8: Database Rules

In your Firebase Realtime Database, go to the "Rules" tab and set these rules for development:

```json
{
  "rules": {
    "users": {
      "$uid": {
        ".read": "$uid === auth.uid",
        ".write": "$uid === auth.uid"
      }
    },
    "posts": {
      ".read": true,
      ".write": "auth != null"
    },
    "post_ratings": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    },
    "post_likes": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    }
  }
}
```

## Step 9: Test the Setup

1. Start your local server (XAMPP)
2. Navigate to your website
3. Try to register a new user
4. Create a post
5. Test rating and liking functionality

## Security Considerations

### For Production:

1. **Secure Database Rules**: Update the database rules to be more restrictive
2. **Environment Variables**: Store Firebase credentials in environment variables
3. **HTTPS**: Ensure your website uses HTTPS
4. **Rate Limiting**: Implement rate limiting for API endpoints
5. **Input Validation**: Validate all user inputs

### Example Production Database Rules:

```json
{
  "rules": {
    "users": {
      "$uid": {
        ".read": "$uid === auth.uid || root.child('users').child($uid).child('public').val() === true",
        ".write": "$uid === auth.uid"
      }
    },
    "posts": {
      ".read": true,
      ".write": "auth != null && root.child('users').child(auth.uid).exists()",
      "$postId": {
        ".validate": "newData.hasChildren(['title', 'content', 'category', 'userId']) && newData.child('userId').val() === auth.uid"
      }
    }
  }
}
```

## Troubleshooting

### Common Issues:

1. **"API is not defined" error**: Make sure all script files are loaded in the correct order
2. **CORS errors**: Ensure your Firebase project allows your domain
3. **Authentication errors**: Check that Email/Password authentication is enabled
4. **Database permission errors**: Verify your database rules allow the operations

### Debug Mode:

To enable debug mode, add this to your PHP files:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## File Structure

After setup, your project should have this structure:

```
min/
├── config/
│   ├── firebase-config.php
│   └── firebase-credentials.json (not in git)
├── api/
│   ├── auth.php
│   └── posts.php
├── vendor/ (created by composer)
├── composer.json
├── composer.lock
└── ... (other files)
```

## Next Steps

1. Test all functionality thoroughly
2. Set up proper error handling
3. Implement user profile management
4. Add post editing and deletion
5. Implement search functionality
6. Add analytics tracking

## Support

If you encounter issues:

1. Check the Firebase console for error logs
2. Verify your configuration files
3. Test with a simple Firebase connection first
4. Check the browser console for JavaScript errors
5. Ensure all dependencies are installed correctly

# 🔧 إصلاح مشكلة Firebase Domain Authorization

## المشكلة
```
FirebaseError: Firebase: This domain is not authorized for OAuth operations for your Firebase project. Edit the list of authorized domains from the Firebase console. (auth/unauthorized-domain).
```

## الحل

### الخطوة 1: الذهاب إلى Firebase Console
1. اذهب إلى [Firebase Console](https://console.firebase.google.com/)
2. اختر مشروعك: **min-jaded**

### الخطوة 2: إعداد النطاقات المصرح بها
1. اذهب إلى **Authentication** في القائمة الجانبية
2. انقر على **Settings** (الإعدادات)
3. انقر على **Authorized domains** (النطاقات المصرح بها)

### الخطوة 3: إضافة النطاقات
أضف هذه النطاقات:
- `localhost`
- `localhost:8000`
- `localhost:3000`
- `127.0.0.1`
- `127.0.0.1:8000`
- `127.0.0.1:3000`

### الخطوة 4: حفظ التغييرات
1. انقر على **Save** (حفظ)
2. انتظر بضع دقائق حتى يتم تطبيق التغييرات

### الخطوة 5: اختبار الحل
1. عد إلى موقعك: `http://localhost:8000/login.php`
2. جرب تسجيل الدخول عبر Google
3. يجب أن يعمل الآن بدون أخطاء

## بدائل سريعة

### الخيار 1: استخدام منفذ مختلف
```bash
php -S localhost:3000
```

### الخيار 2: استخدام IP بدلاً من localhost
```bash
php -S 127.0.0.1:8000
```

### الخيار 3: إضافة نطاق مخصص
إذا كان لديك نطاق مخصص، أضفه إلى Firebase Console.

## معلومات إضافية

### النطاقات الافتراضية المصرح بها
Firebase يسمح تلقائياً بـ:
- `localhost` (بدون منفذ)
- `127.0.0.1` (بدون منفذ)

### النطاقات التي تحتاج إضافة يدوية
- `localhost:8000`
- `localhost:3000`
- `127.0.0.1:8000`
- `127.0.0.1:3000`
- أي نطاق مخصص

## اختبار الاتصال

بعد إضافة النطاقات، يمكنك اختبار الاتصال عبر:
- `http://localhost:8000/debug.php` (صفحة تصحيح)
- `http://localhost:8000/test-auth.html` (صفحة اختبار)

## ملاحظات مهمة

1. **التأخير**: قد يستغرق تطبيق التغييرات في Firebase بضع دقائق
2. **المتصفح**: تأكد من مسح ذاكرة التخزين المؤقت للمتصفح
3. **الأمان**: لا تضيف نطاقات غير آمنة في الإنتاج
4. **التطوير**: هذه الإعدادات مخصصة للتطوير المحلي فقط

## في حالة استمرار المشكلة

1. تحقق من إعدادات Firebase مرة أخرى
2. تأكد من أن المشروع صحيح: **min-jaded**
3. جرب استخدام منفذ مختلف
4. تحقق من console المتصفح للأخطاء الإضافية 