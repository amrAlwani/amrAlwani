<?php
/**
 * API AuthController - مصادقة الـ API والدخول الاجتماعي
 */

namespace Api;

require_once BASEPATH . '/models/User.php';

class AuthController extends \Controller
{
    private \User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new \User();
    }
    
    /**
     * تسجيل الدخول التقليدي
     */
    public function login(): void
    {
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->required('password', 'كلمة المرور مطلوبة');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            \Response::error('البريد الإلكتروني أو كلمة المرور غير صحيحة', [], 401);
        }
        
        if (!$user['is_active']) {
            \Response::error('الحساب معطل', [], 403);
        }
        
        $token = \Auth::generateToken($user['id']);
        
        // استخدام دالة update العامة لتحديث وقت الدخول
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        unset($user['password']);
        
        \Response::success([
            'user' => $user,
            'token' => $token
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * تسجيل الدخول الاجتماعي (المسار المباشر)
     */
    public function socialLogin(): void
    {
        $data = $this->getJsonInput();

        if (empty($data['email']) || empty($data['firebase_uid'])) {
            \Response::error('البريد الإلكتروني و UID مطلوبان', [], 400);
        }

        // البحث عن المستخدم (استخدام findOne كدالة عامة إذا كانت متوفرة أو findByEmail)
        $user = $this->userModel->findByEmail($data['email']);

        if ($user) {
            // تحديث بيانات المستخدم الحالي
            $updateData = [
                'firebase_uid' => $data['firebase_uid'],
                'social_provider' => $data['provider'] ?? 'google',
                'last_login' => date('Y-m-d H:i:s')
            ];
            
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['avatar'])) $updateData['avatar'] = $data['avatar'];

            $this->userModel->update($user['id'], $updateData);
            $userId = $user['id'];
            $message = 'تم تسجيل الدخول بنجاح';
        } else {
            // إنشاء حساب جديد
            $userId = $this->userModel->create([
                'name' => $data['name'] ?? 'User',
                'email' => $data['email'],
                'firebase_uid' => $data['firebase_uid'],
                'avatar' => $data['avatar'] ?? null,
                'social_provider' => $data['provider'] ?? 'google',
                'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role' => 'user',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$userId) {
                \Response::error('فشل إنشاء الحساب الاجتماعي', [], 500);
            }
            $message = 'تم إنشاء حساب جديد بنجاح';
        }

        $finalUser = $this->userModel->findById($userId);
        $token = \Auth::generateToken($userId);

        \Response::success([
            'user' => $finalUser,
            'token' => $token
        ], $message);
    }
    
    /**
     * التسجيل العادي
     */
    public function register(): void
    {
        $data = $this->getJsonInput();

        // إذا كانت البيانات تحتوي على UID فقم بتحويل الطلب لـ socialLogin
        if (!empty($data['firebase_uid'])) {
            $this->socialLogin();
            return;
        }

        $validator = new \Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->required('email', 'البريد الإلكتروني مطلوب')
                  ->email('email', 'البريد الإلكتروني غير صالح')
                  ->required('password', 'كلمة المرور مطلوبة');

        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            \Response::error('البريد الإلكتروني مسجل بالفعل', [], 400);
        }
        
        $userId = $this->userModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'user',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $user = $this->userModel->findById($userId);
        $token = \Auth::generateToken($userId);
        
        \Response::created([
            'user' => $user,
            'token' => $token
        ], 'تم إنشاء الحساب بنجاح');
    }

    public function profile(): void
    {
        $user = \Auth::requireAuth();
        \Response::success($user);
    }
}
