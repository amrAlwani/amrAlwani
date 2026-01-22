<?php
/**
 * AuthController - متحكم المصادقة
 * يتعامل مع تسجيل الدخول والخروج والتسجيل
 * مع حماية CSRF والتحقق من أنواع البيانات
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/User.php';
require_once BASEPATH . '/utils/Validator.php';
require_once BASEPATH . '/utils/CSRF.php';

class AuthController extends BaseController {

    private \User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new \User();
    }

    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLogin(): void {
        $this->view('auth/login', [
            'title' => 'تسجيل الدخول',
            'csrf_token' => \CSRF::getToken()
        ], 'auth');
    }

    /**
     * معالجة تسجيل الدخول
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login.php');
        }

        // التحقق من CSRF
        if (!\CSRF::verifyRequest()) {
            $this->setFlash('error', 'انتهت صلاحية الجلسة، يرجى المحاولة مرة أخرى');
            $this->redirect('/login.php');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // ============ التحقق الشامل من المدخلات ============
        $validator = new \Validator(['email' => $email, 'password' => $password]);
        
        // التحقق من أنواع البيانات (حماية من Type Switching)
        $validator->isString('email', 'البريد الإلكتروني يجب أن يكون نصاً')
                  ->isString('password', 'كلمة المرور يجب أن تكون نصاً')
                  ->notFileContent('email', 'محتوى غير مسموح في البريد')
                  ->notFileContent('password', 'محتوى غير مسموح في كلمة المرور')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->required('password', 'كلمة المرور مطلوبة')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->max('email', 255, 'البريد الإلكتروني طويل جداً')
                  ->max('password', 128, 'كلمة المرور طويلة جداً');

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/login.php');
        }

        // تنظيف البريد
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        // البحث عن المستخدم
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->setFlash('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
            $this->redirect('/login.php');
        }

        if (!$user['is_active']) {
            $this->setFlash('error', 'الحساب غير مفعل');
            $this->redirect('/login.php');
        }

        // تسجيل الدخول - تجديد الجلسة والـ CSRF
        \CSRF::regenerateToken();
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();

        $this->setFlash('success', 'تم تسجيل الدخول بنجاح');
        
        // توجيه حسب الدور
        if ($user['role'] === 'admin') {
            $this->redirect('/admin/dashboard.php');
        } else {
            $this->redirect('/dashboard.php');
        }
    }

    /**
     * عرض صفحة التسجيل
     */
    public function showRegister(): void {
        $this->view('auth/register', [
            'title' => 'إنشاء حساب جديد',
            'csrf_token' => \CSRF::getToken()
        ], 'auth');
    }

    /**
     * معالجة التسجيل
     */
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register.php');
        }

        // التحقق من CSRF
        if (!\CSRF::verifyRequest()) {
            $this->setFlash('error', 'انتهت صلاحية الجلسة، يرجى المحاولة مرة أخرى');
            $this->redirect('/register.php');
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // ============ التحقق الشامل باستخدام Validator ============
        $validator = new \Validator([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'confirm_password' => $confirmPassword
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
                  ->min('password', 6, 'كلمة المرور يجب أن تكون 6 أحرف على الأقل')
                  ->max('password', 128, 'كلمة المرور طويلة جداً')
                  ->matches('confirm_password', 'password', 'كلمتا المرور غير متطابقتين');

        // التحقق من الهاتف إذا أُدخل
        if (!empty($phone)) {
            $validator->phone('phone', 'رقم الهاتف غير صالح');
        }

        if ($validator->fails()) {
            $this->setFlash('error', $validator->getFirstError());
            $this->redirect('/register.php');
        }

        // التحقق من عدم وجود الإيميل
        if ($this->userModel->findByEmail($email)) {
            $this->setFlash('error', 'البريد الإلكتروني مستخدم مسبقاً');
            $this->redirect('/register.php');
        }

        // إنشاء الحساب
        $user = $this->userModel->create([
            'name' => \Validator::sanitize($name),
            'email' => filter_var(trim($email), FILTER_SANITIZE_EMAIL),
            'phone' => preg_replace('/[^0-9+]/', '', $phone),
            'password' => $password
        ]);

        if (!$user) {
            $this->setFlash('error', 'فشل في إنشاء الحساب');
            $this->redirect('/register.php');
        }

        // تسجيل الدخول تلقائياً - تجديد الجلسة والـ CSRF
        \CSRF::regenerateToken();
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();

        $this->setFlash('success', 'تم إنشاء حسابك بنجاح');
        $this->redirect('/dashboard.php');
    }

    /**
     * تسجيل الخروج
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // تدمير الجلسة بشكل آمن
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        $this->redirect('/login.php');
    }
}
