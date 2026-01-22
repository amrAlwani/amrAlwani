<?php
/**
 * AuthController - المصادقة
 * محسّن للأمان مع حماية كاملة
 */

require_once BASEPATH . '/models/User.php';
require_once BASEPATH . '/utils/Security.php';

class AuthController extends Controller
{
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLogin(): void
    {
        // إذا كان مسجل دخول، حوله للداشبورد
        if (isset($_SESSION['user'])) {
            $this->redirect('dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'تسجيل الدخول'
        ]);
    }
    
    /**
     * معالجة تسجيل الدخول
     */
    public function login(): void
    {
        $data = $this->getPostData();
        
        // التحقق من CSRF
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج، حاول مرة أخرى');
            $this->redirect('login');
        }
        
        // التحقق من Rate Limiting
        $ip = Security::getClientIP();
        if (!Security::checkRateLimit('login_' . $ip, 10, 300)) {
            $this->flash('error', 'محاولات كثيرة، حاول بعد 5 دقائق');
            Security::logSecurityEvent('rate_limit_exceeded', ['type' => 'login', 'ip' => $ip]);
            $this->redirect('login');
        }
        
        // التحقق من البيانات
        $validator = new Validator($data);
        $validator->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->maxLength('email', 255, 'البريد الإلكتروني طويل جداً')
                  ->required('password', 'كلمة المرور مطلوبة')
                  ->maxLength('password', 100, 'كلمة المرور طويلة جداً');
        
        if ($validator->fails()) {
            $_SESSION['old'] = ['email' => $data['email'] ?? ''];
            $this->flash('error', $validator->getFirstError());
            $this->redirect('login');
        }
        
        $email = $validator->get('email');
        
        // التحقق من قفل الحساب
        if (Security::isAccountLocked($email)) {
            $this->flash('error', 'الحساب مقفل مؤقتاً بسبب محاولات فاشلة متعددة');
            Security::logSecurityEvent('login_blocked', ['email' => $email]);
            $this->redirect('login');
        }
        
        // البحث عن المستخدم
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            $_SESSION['old'] = ['email' => $email];
            Security::recordFailedLogin($email);
            Security::logSecurityEvent('login_failed', ['email' => $email]);
            $this->flash('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
            $this->redirect('login');
        }
        
        // التحقق من تفعيل الحساب
        if (!$user['is_active']) {
            Security::logSecurityEvent('login_inactive', ['email' => $email]);
            $this->flash('error', 'الحساب معطل، تواصل مع الإدارة');
            $this->redirect('login');
        }
        
        // إعادة تعيين محاولات الدخول الفاشلة
        Security::resetFailedLogins($email);
        
        // تجديد معرف الجلسة (منع Session Fixation)
        session_regenerate_id(true);
        
        // تجديد CSRF token
        CSRF::regenerate();
        
        // حفظ بيانات المستخدم في الجلسة
        unset($user['password']);
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
        $_SESSION['ip'] = $ip;
        
        // تحديث آخر تسجيل دخول
        $this->userModel->update($user['id'], []);
        
        Security::logSecurityEvent('login_success', ['email' => $email, 'user_id' => $user['id']]);
        
        $this->flash('success', 'مرحباً بك ' . htmlspecialchars($user['name']));
        
        if ($user['role'] === 'admin') {
            $this->redirect('admin');
        } else {
            $this->redirect('dashboard');
        }
    }
    
    /**
     * عرض صفحة التسجيل
     */
    public function showRegister(): void
    {
        // إذا كان مسجل دخول، حوله للداشبورد
        if (isset($_SESSION['user'])) {
            $this->redirect('dashboard');
        }
        
        $this->view('auth/register', [
            'title' => 'إنشاء حساب جديد'
        ]);
    }
    
    /**
     * معالجة التسجيل
     */
    public function register(): void
    {
        $data = $this->getPostData();
        
        // التحقق من CSRF
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج، حاول مرة أخرى');
            $this->redirect('register');
        }
        
        // التحقق من Rate Limiting
        $ip = Security::getClientIP();
        if (!Security::checkRateLimit('register_' . $ip, 5, 3600)) {
            $this->flash('error', 'محاولات كثيرة، حاول لاحقاً');
            Security::logSecurityEvent('rate_limit_exceeded', ['type' => 'register', 'ip' => $ip]);
            $this->redirect('register');
        }
        
        // التحقق من البيانات
        $validator = new Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->minLength('name', 2, 'الاسم قصير جداً')
                  ->maxLength('name', 100, 'الاسم طويل جداً')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->maxLength('email', 255, 'البريد الإلكتروني طويل جداً')
                  ->required('phone', 'رقم الهاتف مطلوب')
                  ->phone('phone', 'رقم الهاتف غير صالح')
                  ->required('password', 'كلمة المرور مطلوبة')
                  ->minLength('password', 8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل')
                  ->maxLength('password', 100, 'كلمة المرور طويلة جداً')
                  ->notBannedPassword('password', 'كلمة المرور ضعيفة جداً')
                  ->confirmed('password', 'password_confirmation', 'كلمات المرور غير متطابقة');
        
        if ($validator->fails()) {
            $_SESSION['old'] = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? ''
            ];
            $this->flash('error', $validator->getFirstError());
            $this->redirect('register');
        }
        
        // التحقق من وجود البريد
        if ($this->userModel->findByEmail($validator->get('email'))) {
            $_SESSION['old'] = $validator->getData();
            $this->flash('error', 'البريد الإلكتروني مستخدم بالفعل');
            $this->redirect('register');
        }
        
        // إنشاء المستخدم
        $userId = $this->userModel->create([
            'name' => $validator->get('name'),
            'email' => $validator->get('email'),
            'phone' => $validator->get('phone'),
            'password' => $data['password'], // User model يقوم بالـ hash
            'role' => 'user',
            'is_active' => 1
        ]);
        
        if (!$userId) {
            $this->flash('error', 'حدث خطأ أثناء إنشاء الحساب');
            $this->redirect('register');
        }
        
        Security::logSecurityEvent('register_success', ['email' => $validator->get('email'), 'user_id' => $userId]);
        
        // تسجيل الدخول تلقائياً
        session_regenerate_id(true);
        CSRF::regenerate();
        
        $user = $this->userModel->findById($userId);
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
        $_SESSION['ip'] = $ip;
        
        $this->flash('success', 'تم إنشاء حسابك بنجاح!');
        $this->redirect('dashboard');
    }
    
    /**
     * تسجيل الخروج
     */
    public function logout(): void
    {
        if (isset($_SESSION['user'])) {
            Security::logSecurityEvent('logout', ['user_id' => $_SESSION['user']['id']]);
        }
        
        // مسح الجلسة
        $_SESSION = [];
        
        // حذف الكوكي
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        redirect('login');
    }
    
    /**
     * عرض صفحة استعادة كلمة المرور
     */
    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', [
            'title' => 'استعادة كلمة المرور'
        ]);
    }
    
    /**
     * معالجة استعادة كلمة المرور
     */
    public function forgotPassword(): void
    {
        $data = $this->getPostData();
        
        // التحقق من CSRF
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج، حاول مرة أخرى');
            $this->redirect('forgot-password');
        }
        
        // التحقق من Rate Limiting
        $ip = Security::getClientIP();
        if (!Security::checkRateLimit('forgot_' . $ip, 3, 3600)) {
            $this->flash('error', 'محاولات كثيرة، حاول لاحقاً');
            $this->redirect('forgot-password');
        }
        
        $validator = new Validator($data);
        $validator->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح');
        
        if ($validator->fails()) {
            $this->flash('error', $validator->getFirstError());
            $this->redirect('forgot-password');
        }
        
        // التحقق من وجود المستخدم (لكن لا نكشف ذلك)
        $user = $this->userModel->findByEmail($validator->get('email'));
        
        if ($user) {
            // TODO: إرسال بريد استعادة كلمة المرور
            Security::logSecurityEvent('password_reset_request', ['email' => $validator->get('email')]);
        }
        
        // دائماً نظهر نفس الرسالة لأسباب أمنية
        $this->flash('success', 'إذا كان البريد الإلكتروني مسجلاً، ستصلك رسالة بتعليمات استعادة كلمة المرور');
        $this->redirect('login');
    }
}
