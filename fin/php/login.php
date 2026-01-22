<?php
/**
 * صفحة تسجيل الدخول - SwiftCart
 * واجهة PHP كاملة للمصادقة مع حماية CSRF
 */

// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/utils/CSRF.php';

// إذا كان المستخدم مسجل دخوله، وجهه للوحة التحكم
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? $_SESSION['user']['role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/utils/Validator.php';
    
    // التحقق من CSRF Token
    if (!CSRF::verifyRequest()) {
        $error = 'انتهت صلاحية الجلسة. يرجى المحاولة مرة أخرى.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // ============ التحقق من أنواع البيانات (PHP Server-Side) ============
        $validator = new Validator(['email' => $email, 'password' => $password]);
        
        // التحقق من أن البيانات نصية وليست ملفات مُقنّعة
        $validator->isString('email', 'البريد الإلكتروني يجب أن يكون نصاً')
                  ->isString('password', 'كلمة المرور يجب أن تكون نصاً')
                  ->notFileContent('email', 'محتوى غير مسموح في البريد الإلكتروني')
                  ->notFileContent('password', 'محتوى غير مسموح في كلمة المرور')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->required('password', 'كلمة المرور مطلوبة')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->max('email', 255, 'البريد الإلكتروني طويل جداً')
                  ->max('password', 128, 'كلمة المرور طويلة جداً');
        
        if ($validator->fails()) {
            $error = $validator->getFirstError();
        } else {
            // تنظيف البريد
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            
            require_once __DIR__ . '/config/database.php';
            
            try {
                $db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS
                );
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // تجديد CSRF Token بعد تسجيل الدخول
                    CSRF::regenerateToken();
                    session_regenerate_id(true);
                    
                    // التحقق من تفعيل الحساب
                    if (!($user['is_active'] ?? true)) {
                        $error = 'الحساب غير مفعل، يرجى التواصل مع الدعم';
                    } else {
                        // تسجيل الدخول ناجح - حفظ بيانات المستخدم
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'] ?? 'user';
                        
                        // للتوافق مع BaseController
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role' => $user['role'] ?? 'user',
                            'avatar' => $user['avatar'] ?? null
                        ];
                        
                        // تحديث آخر تسجيل دخول
                        $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                        $updateStmt->execute(['id' => $user['id']]);
                        
                        // تسجيل في سجل الأمان (اختياري - تجاهل الخطأ)
                        try {
                            $logStmt = $db->prepare("INSERT INTO security_logs (user_id, action_type, ip_address, user_agent) VALUES (:user_id, 'login_success', :ip, :ua)");
                            $logStmt->execute([
                                'user_id' => $user['id'],
                                'ip' => $_SERVER['REMOTE_ADDR'],
                                'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
                            ]);
                        } catch (PDOException $logError) {
                            // تجاهل خطأ السجل - لا يمنع تسجيل الدخول
                        }
                        
                        // توجيه حسب الدور: admin -> لوحة الأدمن | user -> لوحة المستخدم
                        if (($user['role'] ?? 'user') === 'admin') {
                            header('Location: ' . BASE_URL . '/admin/dashboard.php');
                        } else {
                            header('Location: ' . BASE_URL . '/dashboard.php');
                        }
                        exit;
                    }
                } else {
                    $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                    
                    // تسجيل محاولة فاشلة (اختياري)
                    try {
                        $logStmt = $db->prepare("INSERT INTO login_attempts (email, ip_address, attempted_at) VALUES (:email, :ip, NOW())");
                        $logStmt->execute([
                            'email' => $email,
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    } catch (PDOException $logError) {
                        // تجاهل خطأ السجل
                    }
                }
            } catch (PDOException $e) {
                $error = 'حدث خطأ في الاتصال بقاعدة البيانات';
            }
        }
    }
}

// الحصول على CSRF Token
$csrfToken = CSRF::getToken();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 to-primary-800 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-primary-100 rounded-2xl mx-auto flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">مرحباً بك</h2>
            <p class="text-gray-500 mt-2">سجّل دخولك للمتابعة</p>
        </div>
        
        <!-- Error/Success Messages -->
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="" class="space-y-6" data-validate="true">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>
                    <input id="email" name="email" type="email" required 
                           class="block w-full pr-10 pl-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right"
                           placeholder="example@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input id="password" name="password" type="password" required 
                           class="block w-full pr-10 pl-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right"
                           placeholder="••••••••">
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="remember" class="mr-2 block text-sm text-gray-700">تذكرني</label>
                </div>
                <a href="<?= BASE_URL ?>/forgot-password.php" class="text-sm text-primary-600 hover:text-primary-500">نسيت كلمة المرور؟</a>
            </div>
            
            <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                تسجيل الدخول
            </button>
        </form>
        
        <!-- Divider -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">أو</span>
                </div>
            </div>
        </div>
        
        <!-- Social Login -->
        <div class="mt-6 space-y-3">
            <button type="button" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 ml-2" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                المتابعة مع Google
            </button>
        </div>
        
        <!-- Register Link -->
        <p class="mt-8 text-center text-sm text-gray-600">
            ليس لديك حساب؟
            <a href="<?= BASE_URL ?>/register.php" class="font-medium text-primary-600 hover:text-primary-500">إنشاء حساب</a>
        </p>
        
        <!-- Guest Link -->
        <p class="mt-2 text-center">
            <a href="<?= BASE_URL ?>/index.php" class="text-sm text-gray-500 hover:text-gray-700">تصفح كضيف</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
