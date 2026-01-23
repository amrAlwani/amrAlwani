<?php
/**
 * API AuthController - مصادقة الـ API
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
     * تسجيل الدخول
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
        
        // إنشاء token
        $token = \Auth::generateToken($user['id']);
        
        // تحديث آخر تسجيل دخول
        $this->userModel->updateLastLogin($user['id']);
        
        unset($user['password']);
        
        \Response::success([
            'user' => $user,
            'token' => $token
        ], 'تم تسجيل الدخول بنجاح');
    }
    
    /**
     * التسجيل - معدل ليدعم تسجيل الدخول الاجتماعي
     */
    public function register(): void
    {
        $data = $this->getJsonInput();

        // التحقق إذا كان تسجيلاً اجتماعياً
        if (isset($data['firebase_uid']) && !empty($data['firebase_uid'])) {
            
            // --- منطق التسجيل/الدخول الاجتماعي ---

            $validator = new \Validator($data);
            $validator->required('firebase_uid', 'Firebase UID is required.')
                      ->required('email', 'Email is required.');

            if (!$validator->passes()) {
                \Response::validationError($validator->getErrors());
            }

            $user = $this->userModel->findBy('firebase_uid', $data['firebase_uid']);
            if (!$user) {
                $user = $this->userModel->findByEmail($data['email']);
            }

            // إذا كان المستخدم موجوداً، قم بتسجيل دخوله
            if ($user) {
                if (!$user['is_active']) {
                    \Response::error('الحساب معطل', [], 403);
                }

                $updateData = [
                    'name' => $data['name'] ?? $user['name'],
                    'avatar' => $data['avatar'] ?? $user['avatar'],
                    'firebase_uid' => $data['firebase_uid']
                ];

                if (empty($user['social_provider']) && isset($data['provider'])) {
                    $updateData['social_provider'] = $data['provider'];
                }
                $this->userModel->update($user['id'], $updateData);

                $token = \Auth::generateToken($user['id']);
                $this->userModel->updateLastLogin($user['id']);
                $updatedUser = $this->userModel->findById($user['id']);
                unset($updatedUser['password']);

                \Response::success([
                    'user' => $updatedUser,
                    'token' => $token
                ], 'تم تسجيل الدخول بنجاح');

            } 
            // إذا لم يكن المستخدم موجوداً، قم بإنشاء حساب جديد
            else {
                $userId = $this->userModel->create([
                    'name' => $data['name'] ?? 'User',
                    'email' => $data['email'],
                    'firebase_uid' => $data['firebase_uid'],
                    'avatar' => $data['avatar'] ?? null,
                    'social_provider' => $data['provider'] ?? null,
                    'role' => 'user',
                    'is_active' => 1
                ]);

                if (!$userId) {
                    \Response::error('حدث خطأ أثناء إنشاء الحساب', [], 500);
                }

                $newUser = $this->userModel->findById($userId);
                $token = \Auth::generateToken($userId);

                \Response::created([
                    'user' => $newUser,
                    'token' => $token
                ], 'تم إنشاء الحساب بنجاح');
            }

        } else {
            // --- منطق التسجيل العادي (بالبريد وكلمة المرور) ---

            $validator = new \Validator($data);
            $validator->required('name', 'الاسم مطلوب')
                      ->minLength('name', 3, 'الاسم قصير جداً')
                      ->required('email', 'البريد الإلكتروني مطلوب')
                      ->email('email', 'البريد الإلكتروني غير صالح')
                      ->required('phone', 'رقم الهاتف مطلوب')
                      ->required('password', 'كلمة المرور مطلوبة')
                      ->minLength('password', 8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل');

            if (!$validator->passes()) {
                \Response::validationError($validator->getErrors());
            }
            
            if ($this->userModel->findByEmail($data['email'])) {
                \Response::error('البريد الإلكتروني مستخدم بالفعل', [], 400);
            }
            
            $userId = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'user',
                'is_active' => 1
            ]);
            
            if (!$userId) {
                \Response::error('حدث خطأ أثناء إنشاء الحساب', [], 500);
            }
            
            $user = $this->userModel->findById($userId);
            $token = \Auth::generateToken($userId);
            
            \Response::created([
                'user' => $user,
                'token' => $token
            ], 'تم إنشاء الحساب بنجاح');
        }
    }
    
    /**
     * الملف الشخصي
     */
    public function profile(): void
    {
        $user = \Auth::requireAuth();
        \Response::success($user);
    }
    
    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }
        
        if (!empty($updateData)) {
            $this->userModel->update($user['id'], $updateData);
        }
        
        $updatedUser = $this->userModel->findById($user['id']);
        \Response::success($updatedUser, 'تم تحديث البيانات');
    }
    
    /**
     * تغيير كلمة المرور
     */
    public function changePassword(): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('current_password', 'كلمة المرور الحالية مطلوبة')
                  ->required('new_password', 'كلمة المرور الجديدة مطلوبة')
                  ->minLength('new_password', 8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $currentUser = $this->userModel->findByIdWithPassword($user['id']);
        
        if (!password_verify($data['current_password'], $currentUser['password'])) {
            \Response::error('كلمة المرور الحالية غير صحيحة', [], 400);
        }
        
        $this->userModel->update($user['id'], [
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT)
        ]);
        
        \Response::success(null, 'تم تغيير كلمة المرور بنجاح');
    }
}