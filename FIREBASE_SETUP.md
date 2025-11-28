# إعدادات Firebase

تم إعداد Firebase في المشروع بنجاح.

## الملفات المُعدة

1. **ملف الإعدادات**: `config/firebase.php`
   - تم إنشاء ملف الإعدادات مع المسار الصحيح لملف الاعتماديات

2. **ملف الاعتماديات**: `storage/app/firebase/hospitalproject-a95de-firebase-adminsdk-fbsvc-e1cec9e469.json`
   - ملف Service Account من Firebase Console

## الإعدادات الحالية

- **Project ID**: `hospitalproject-a95de`
- **Credentials Path**: `storage/app/firebase/hospitalproject-a95de-firebase-adminsdk-fbsvc-e1cec9e469.json`
- **Default Project**: `app`

## متغيرات البيئة (اختيارية)

يمكنك إضافة هذه المتغيرات في ملف `.env` إذا أردت تخصيص الإعدادات:

```env
# Firebase Project Name (اختياري)
FIREBASE_PROJECT=app

# مسار ملف الاعتماديات (اختياري - تم تعيينه افتراضياً)
FIREBASE_CREDENTIALS=storage/app/firebase/hospitalproject-a95de-firebase-adminsdk-fbsvc-e1cec9e469.json

# Firebase Realtime Database URL (إذا كنت تستخدمه)
FIREBASE_DATABASE_URL=https://hospitalproject-a95de-default-rtdb.firebaseio.com

# Firebase Storage Bucket (إذا كنت تستخدمه)
FIREBASE_STORAGE_DEFAULT_BUCKET=hospitalproject-a95de.appspot.com

# Firebase Auth Tenant ID (إذا كنت تستخدم Multi-tenancy)
FIREBASE_AUTH_TENANT_ID=

# Cache Store (افتراضي: file)
FIREBASE_CACHE_STORE=file
```

## كيفية الاستخدام

### 1. استخدام Firebase Messaging (FCM)

```php
use Kreait\Laravel\Firebase\Facades\Firebase;

// الحصول على خدمة Messaging
$messaging = Firebase::messaging();

// إرسال إشعار
$message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $deviceToken)
    ->withNotification(\Kreait\Firebase\Messaging\Notification::create('عنوان الإشعار', 'نص الإشعار'))
    ->withData(['key' => 'value']);

$messaging->send($message);
```

### 2. استخدام Firebase Auth

```php
use Kreait\Laravel\Firebase\Facades\Firebase;

$auth = Firebase::auth();

// إنشاء مستخدم
$user = $auth->createUser([
    'email' => 'user@example.com',
    'password' => 'password123',
]);
```

### 3. استخدام Firestore

```php
use Kreait\Laravel\Firebase\Facades\Firebase;

$firestore = Firebase::firestore();
$database = $firestore->database();

// إضافة مستند
$collection = $database->collection('users');
$collection->add([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

### 4. استخدام Firebase Storage

```php
use Kreait\Laravel\Firebase\Facades\Firebase;

$storage = Firebase::storage();
$bucket = $storage->getBucket();

// رفع ملف
$bucket->upload(file_get_contents('path/to/file.jpg'), [
    'name' => 'images/file.jpg',
]);
```

## ملاحظات مهمة

1. **الأمان**: تأكد من أن ملف الاعتماديات (`hospitalproject-a95de-firebase-adminsdk-fbsvc-e1cec9e469.json`) موجود في `.gitignore` ولا يتم رفعه إلى Git.

2. **الصلاحيات**: تأكد من أن ملف الاعتماديات لديه الصلاحيات المناسبة في Firebase Console.

3. **الاختبار**: يمكنك اختبار الإعدادات باستخدام:
   ```php
   $firebase = app('firebase');
   $messaging = $firebase->messaging();
   ```

## الخطوات التالية (اختيارية)

1. إضافة حقل `fcm_token` في جدول `users` لحفظ أرقام أجهزة المستخدمين
2. إنشاء Service لإرسال الإشعارات عبر FCM
3. دمج Firebase Notifications مع Laravel Notifications

## الدعم

- [Firebase PHP SDK Documentation](https://firebase-php.readthedocs.io/)
- [Kreait Laravel Firebase Package](https://github.com/kreait/laravel-firebase)

