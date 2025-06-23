# 🔧 حل مشكلة النطاق المصرح به في Firebase

## المشكلة
```
FirebaseError: Firebase: This domain is not authorized for OAuth operations for your Firebase project. Edit the list of authorized domains from the Firebase console. (auth/unauthorized-domain).
```

## النطاق الحالي
**min-jaded.ct.ws** - هذا نطاق إلكتروني حقيقي ويحتاج إلى إضافة في Firebase Console

## الحل السريع (5 دقائق)

### الخطوة 1: الذهاب إلى Firebase Console
1. اذهب إلى [Firebase Console](https://console.firebase.google.com/)
2. اختر مشروعك: **min-jaded**

### الخطوة 2: إضافة النطاق المصرح به
1. اذهب إلى **Authentication** في القائمة الجانبية
2. انقر على **Settings** (الإعدادات)
3. انقر على **Authorized domains** (النطاقات المصرح بها)
4. أضف النطاق: `min-jaded.ct.ws`
5. اضغط **Add domain**
6. احفظ التغييرات

### الخطوة 3: انتظار تطبيق التغييرات
- انتظر 2-3 دقائق حتى يتم تطبيق التغييرات
- امسح ذاكرة التخزين المؤقت للمتصفح
- جرب مرة أخرى

## 🧪 اختبار الحل

### استخدم صفحة الاختبار المخصصة
اذهب إلى: `https://min-jaded.ct.ws/domain-fix.html`

هذه الصفحة ستساعدك في:
- اختبار اتصال Firebase
- اختبار تسجيل الدخول عبر Google
- عرض رسائل خطأ مفصلة إذا كانت هناك مشاكل

### أو استخدم صفحة تسجيل الدخول العادية
اذهب إلى: `https://min-jaded.ct.ws/login.php`

## 📋 قائمة النطاقات المطلوبة

### النطاقات الأساسية (مطلوبة)
- `min-jaded.ct.ws` ✅ (النطاق الرئيسي)

### النطاقات الإضافية (اختيارية للاختبار)
- `localhost`
- `localhost:8000`
- `localhost:3000`
- `127.0.0.1`
- `127.0.0.1:8000`

## 🔍 تشخيص المشاكل

### إذا استمرت المشكلة:

1. **تأكد من إضافة النطاق الصحيح**
   - تأكد من كتابة `min-jaded.ct.ws` بدون `https://` أو `http://`

2. **انتظر وقتاً كافياً**
   - قد يستغرق Firebase حتى 5 دقائق لتطبيق التغييرات

3. **امسح ذاكرة التخزين المؤقت**
   - اضغط `Ctrl + F5` أو `Cmd + Shift + R`
   - أو امسح ذاكرة التخزين المؤقت يدوياً

4. **تحقق من إعدادات Firebase**
   - تأكد من تفعيل Google Authentication
   - تأكد من إعداد OAuth consent screen

## 🛠️ إعدادات إضافية

### تفعيل Google Authentication
1. في Firebase Console، اذهب إلى **Authentication** → **Sign-in method**
2. انقر على **Google**
3. تأكد من تفعيله
4. أضف **Support email** إذا لم يكن موجوداً

### إعداد OAuth Consent Screen
1. اذهب إلى [Google Cloud Console](https://console.cloud.google.com/)
2. اختر مشروعك: **min-jaded**
3. اذهب إلى **APIs & Services** → **OAuth consent screen**
4. تأكد من إضافة النطاق: `min-jaded.ct.ws`

## 📞 الدعم

إذا استمرت المشكلة:

1. استخدم صفحة الاختبار: `https://min-jaded.ct.ws/domain-fix.html`
2. تحقق من رسائل الخطأ في Console المتصفح (F12)
3. تأكد من أن جميع الملفات مرفوعة بشكل صحيح
4. تحقق من إعدادات الخادم

## ✅ علامات النجاح

بعد إضافة النطاق بنجاح:
- ✅ تسجيل الدخول عبر Google يعمل
- ✅ تسجيل الدخول عبر Facebook يعمل
- ✅ لا توجد رسائل خطأ في Console
- ✅ صفحة الاختبار تظهر "Firebase متصل بنجاح"

## 🎯 النتيجة النهائية

بعد اتباع هذه الخطوات، سيعمل موقعك `min-jaded.ct.ws` بشكل مثالي مع:
- تسجيل الدخول الاجتماعي (Google & Facebook)
- Firebase Authentication
- جميع ميزات الموقع

---
**ملاحظة:** هذا الحل مخصص للنطاق `min-jaded.ct.ws` وقد يحتاج تعديلات إذا تم تغيير النطاق في المستقبل. 