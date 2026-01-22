<?php
/**
 * صفحة التسجيل - SwiftCart
 * مع حماية CSRF والتحقق من أنواع البيانات
 */
session_start();
require_once 'includes/header.php';
require_once 'utils/CSRF.php';
require_once 'utils/Validator.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF Token
    if (!CSRF::verifyRequest()) {
        $error = 'انتهت صلاحية الجلسة. يرجى المحاولة مرة أخرى.';
    } else {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $accept_terms = isset($_POST['accept_terms']);
        
        // ============ التحقق الشامل باستخدام Validator (PHP Server-Side) ============
        $validator = new Validator([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'confirm_password' => $confirm_password
        ]);
        
        // التحقق من أنواع البيانات أولاً (حماية من Type Switching)
        $validator->isString('name', 'الاسم يجب أن يكون نصاً')
                  ->isString('email', 'البريد الإلكتروني يجب أن يكون نصاً')
                  ->isString('phone', 'الهاتف يجب أن يكون نصاً')
                  ->isString('password', 'كلمة المرور يجب أن تكون نصاً')
                  ->isString('confirm_password', 'تأكيد كلمة المرور يجب أن يكون نصاً');
        
        // التحقق من عدم وجود محتوى ملفات مُقنّع
        $validator->notFileContent('name', 'محتوى غير مسموح في الاسم')
                  ->notFileContent('email', 'محتوى غير مسموح في البريد')
                  ->notFileContent('password', 'محتوى غير مسموح في كلمة المرور');
        
        // التحقق من الحقول المطلوبة والصيغ
        $validator->required('name', 'الاسم مطلوب')
                  ->safeName('name', 'الاسم يحتوي على رموز غير مسموحة مثل () * , وغيرها')
                  ->min('name', 2, 'الاسم يجب أن يكون حرفين على الأقل')
                  ->max('name', 100, 'الاسم طويل جداً')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->max('email', 255, 'البريد الإلكتروني طويل جداً')
                  ->required('password', 'كلمة المرور مطلوبة')
                  ->min('password', 8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل')
                  ->max('password', 128, 'كلمة المرور طويلة جداً')
                  ->matches('confirm_password', 'password', 'كلمة المرور غير متطابقة');
        
        // التحقق من الهاتف (اختياري)
        if (!empty($phone)) {
            $validator->phone('phone', 'رقم الهاتف غير صالح');
        }
        
        // التحقق من قبول الشروط
        if (!$accept_terms) {
            $validator->addError('terms', 'يجب الموافقة على الشروط والأحكام');
        }
        
        if ($validator->fails()) {
            $error = $validator->getFirstError();
        } else {
            // تنظيف البيانات
            $name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            require_once 'config/database.php';
            
            try {
                $db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS
                );
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // التحقق من البريد
                $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $checkStmt->execute(['email' => $email]);
                
                if ($checkStmt->fetch()) {
                    $error = 'البريد الإلكتروني مسجل مسبقاً';
                } else {
                    // إنشاء الحساب
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (:name, :email, :phone, :password, 'user', NOW())");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone ?: '0000000000',
                        'password' => $hashedPassword
                    ]);
                    
                    $userId = $db->lastInsertId();
                    
                    // تجديد CSRF Token وSession
                    CSRF::regenerateToken();
                    session_regenerate_id(true);
                    
                    // تسجيل الدخول تلقائياً
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'user';
                    
                    // للتوافق مع BaseController
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'name' => $name,
                        'email' => $email,
                        'role' => 'user',
                        'avatar' => null
                    ];
                    
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'حدث خطأ في إنشاء الحساب';
            }
        }
    }
}

// الحصول على CSRF Token
$csrfToken = CSRF::getToken();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 to-primary-800 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900">إنشاء حساب</h2>
            <p class="text-gray-500 mt-2">أنشئ حسابك للبدء بالتسوق</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-4" data-validate="true">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل *</label>
                <input type="text" id="name" name="name" required
                       data-type="name" data-label="الاسم"
                       maxlength="100" minlength="2"
                       class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني *</label>
                <input type="email" id="email" name="email" required
                       data-type="email" data-label="البريد الإلكتروني"
                       maxlength="255"
                       class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                <input type="tel" id="phone" name="phone"
                       data-type="phone" data-label="رقم الهاتف"
                       pattern="[0-9+]{9,15}"
                       class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور *</label>
                <input type="password" id="password" name="password" required minlength="8" maxlength="128"
                       data-type="password" data-label="كلمة المرور"
                       class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right">
                <p class="text-xs text-gray-500 mt-1">8 أحرف على الأقل</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور *</label>
                <input type="password" id="confirm_password" name="confirm_password" required maxlength="128"
                       data-type="password" data-label="تأكيد كلمة المرور"
                       class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-right">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="accept_terms" name="accept_terms" required
                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="accept_terms" class="mr-2 block text-sm text-gray-700">
                    أوافق على <a href="#" class="text-primary-600 hover:underline">الشروط والأحكام</a>
                </label>
            </div>
            
            <button type="submit" 
                    class="w-full py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                إنشاء حساب
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            لديك حساب بالفعل؟
            <a href="login.php" class="font-medium text-primary-600 hover:text-primary-500">تسجيل الدخول</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
