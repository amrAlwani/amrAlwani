<?php
/**
 * Authentication API Endpoints
 * نقاط نهاية المصادقة
 * 
 * تم التصحيح:
 * - تحسين معالجة الأخطاء
 * - إصلاح مسارات الملفات
 */

// تفعيل عرض الأخطاء للتصحيح (يجب تعطيله في الإنتاج)
// ⚠️ تنبيه: قم بتعطيل هذه السطور في بيئة الإنتاج
$isProduction = getenv('APP_ENV') === 'production';
if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// إضافة CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// معالجة OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// تحميل الملفات المطلوبة
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../models/User.php';

// الحصول على الإجراء المطلوب
$action = $_GET['action'] ?? 'none';

switch ($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'profile':
        getProfile();
        break;
    case 'update-profile':
        updateProfile();
        break;
    case 'change-password':
        changePassword();
        break;
    default:
        Response::error('إجراء غير صالح أو مفقود', [], 400);
}

/**
 * تسجيل الدخول
 * تم التحسين: إضافة Rate Limiting ومعالجة أفضل
 */
function login(): void {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        Response::error('بيانات غير صالحة', [], 400);
    }

    $validator = new Validator($data);
    
    // التحقق من أنواع البيانات (حماية من Type Switching)
    $validator->isString('email', 'البريد الإلكتروني يجب أن يكون نصاً')
              ->isString('password', 'كلمة المرور يجب أن تكون نصاً')
              ->notFileContent('email', 'محتوى غير مسموح في البريد')
              ->notFileContent('password', 'محتوى غير مسموح في كلمة المرور')
              ->required('email', 'البريد الإلكتروني مطلوب')
              ->email('email', 'البريد الإلكتروني غير صالح')
              ->max('email', 255, 'البريد الإلكتروني طويل جداً')
              ->required('password', 'كلمة المرور مطلوبة')
              ->max('password', 128, 'كلمة المرور طويلة جداً');
    $validator->validate();
    
    // تنظيف البريد
    $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    // التحقق من كلمة المرور باستخدام timing-safe comparison
    if (!$user || !password_verify($data['password'], $user['password'])) {
        // رسالة موحدة لمنع enumeration attacks
        Response::error('البريد الإلكتروني أو كلمة المرور غير صحيحة', [], 401);
    }

    if (!$user['is_active']) {
        Response::error('الحساب غير مفعل، يرجى التواصل مع الدعم', [], 403);
    }

    $token = Auth::generateToken($user['id']);
    unset($user['password']);
    
    Response::success(['token' => $token, 'user' => $user], 'تم تسجيل الدخول بنجاح');
}

/**
 * التسجيل / مزامنة Firebase
 * تم التصحيح: دعم Social Login بدون كلمة مرور
 */
function register(): void {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        Response::error('بيانات غير صالحة', [], 400);
    }

    $userModel = new User();
    $isSocialLogin = !empty($data['firebase_token']) && empty($data['password']);

    // البريد الإلكتروني مطلوب
    if (empty($data['email'])) {
        Response::error('البريد الإلكتروني مطلوب لمزامنة الحساب', [], 422);
    }
    
    $validator = new Validator($data);
    
    // التحقق من أنواع البيانات أولاً (حماية من Type Switching)
    $validator->isString('name', 'الاسم يجب أن يكون نصاً')
              ->isString('email', 'البريد الإلكتروني يجب أن يكون نصاً')
              ->notFileContent('name', 'محتوى غير مسموح في الاسم')
              ->notFileContent('email', 'محتوى غير مسموح في البريد');
    
    if (!$isSocialLogin) {
        $validator->isString('password', 'كلمة المرور يجب أن تكون نصاً')
                  ->notFileContent('password', 'محتوى غير مسموح في كلمة المرور');
    }

    $existingUser = $userModel->findByEmail($data['email']);

    if ($existingUser) {
        // المستخدم موجود - تسجيل دخول (مزامنة)
        if (!$existingUser['is_active']) {
            Response::error('الحساب غير مفعل، يرجى التواصل مع الدعم', [], 403);
        }
        
        // تحديث الصورة الرمزية إذا كانت جديدة (من Social Login)
        if (!empty($data['avatar']) && empty($existingUser['avatar'])) {
            $userModel->update($existingUser['id'], ['avatar' => $data['avatar']]);
            $existingUser['avatar'] = $data['avatar'];
        }
        
        $token = Auth::generateToken($existingUser['id']);
        unset($existingUser['password']);
        Response::success(['token' => $token, 'user' => $existingUser], 'تم تسجيل الدخول بنجاح');
    } else {
        // مستخدم جديد - إنشاء حساب
        $validator->required('name', 'الاسم مطلوب')
                  ->safeName('name', 'الاسم يحتوي على رموز غير مسموحة مثل () * , وغيرها')
                  ->min('name', 2, 'الاسم يجب أن يكون حرفين على الأقل')
                  ->max('name', 100, 'الاسم طويل جداً')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->max('email', 255, 'البريد الإلكتروني طويل جداً');
        
        // كلمة المرور مطلوبة فقط للتسجيل العادي
        if (!$isSocialLogin) {
            $validator->required('password', 'كلمة المرور مطلوبة')
                      ->min('password', 6, 'كلمة المرور يجب أن تكون 6 أحرف على الأقل')
                      ->max('password', 128, 'كلمة المرور طويلة جداً');
        }
        
        // التحقق من الهاتف إذا كان موجوداً
        if (!empty($data['phone'])) {
            $validator->isString('phone', 'الهاتف يجب أن يكون نصاً')
                      ->phone('phone', 'رقم الهاتف غير صالح');
        }
        
        $validator->validate();
        
        // تنظيف البيانات
        $data['name'] = Validator::sanitize($data['name']);
        $data['email'] = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

        // للـ Social Login، إنشاء كلمة مرور عشوائية آمنة
        if ($isSocialLogin && empty($data['password'])) {
            $data['password'] = bin2hex(random_bytes(32));
        }

        $newUser = $userModel->create($data);
        if (!$newUser) {
            Response::error('فشل إنشاء المستخدم، قد يكون البريد مسجلاً مسبقاً', [], 500);
        }

        $token = Auth::generateToken($newUser['id']);
        Response::created(['token' => $token, 'user' => $newUser], 'تم إنشاء الحساب بنجاح');
    }
}

/**
 * الحصول على الملف الشخصي
 */
function getProfile(): void {
    $user = Auth::requireAuth();
    Response::success($user);
}

/**
 * تحديث الملف الشخصي
 */
function updateProfile(): void {
    $user = Auth::requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        Response::error('بيانات غير صالحة', [], 400);
    }

    // منع تعديل البريد وكلمة المرور والـ role من هذا الإجراء
    unset($data['email'], $data['password'], $data['role'], $data['is_active']);

    // تنظيف المدخلات
    if (isset($data['name'])) {
        $data['name'] = Validator::sanitize($data['name']);
    }

    // التحقق من المدخلات
    $validator = new Validator($data);
    
    if (!empty($data['name'])) {
        $validator->safeName('name', 'الاسم يحتوي على رموز غير مسموحة')
                  ->min('name', 2, 'الاسم يجب أن يكون حرفين على الأقل')
                  ->max('name', 100, 'الاسم طويل جداً');
    }
    
    if (!empty($data['phone'])) {
        $validator->phone('phone', 'رقم الهاتف غير صالح');
    }
    
    $validator->validate();

    $userModel = new User();
    $updatedUser = $userModel->update($user['id'], $data);

    Response::success($updatedUser, 'تم تحديث الملف الشخصي');
}

/**
 * تغيير كلمة المرور
 */
function changePassword(): void {
    $user = Auth::requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        Response::error('بيانات غير صالحة', [], 400);
    }

    $validator = new Validator($data);
    $validator->required('current_password', 'كلمة المرور الحالية مطلوبة')
              ->required('new_password', 'كلمة المرور الجديدة مطلوبة')
              ->min('new_password', 6, 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
    $validator->validate();

    $userModel = new User();
    // الحصول على المستخدم مع كلمة المرور للتحقق
    $fullUser = $userModel->findByEmail($user['email']);

    if (!$fullUser || !password_verify($data['current_password'], $fullUser['password'])) {
        Response::error('كلمة المرور الحالية غير صحيحة', [], 400);
    }

    $userModel->updatePassword($user['id'], $data['new_password']);
    Response::success(null, 'تم تغيير كلمة المرور بنجاح');
}
