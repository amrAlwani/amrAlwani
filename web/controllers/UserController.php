<?php
/**
 * UserController - المستخدم
 */

require_once BASEPATH . '/models/User.php';
require_once BASEPATH . '/models/Order.php';
require_once BASEPATH . '/models/Notification.php';

class UserController extends Controller
{
    private User $userModel;
    private Order $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->orderModel = new Order();
    }
    
    /**
     * لوحة تحكم المستخدم
     */
    public function dashboard(): void
    {
        $user = $this->requireAuth();
        
        // إحصائيات المستخدم
        $stats = [
            'orders_count' => $this->orderModel->count(['user_id' => $user['id']]),
            'pending_orders' => $this->orderModel->count(['user_id' => $user['id'], 'status' => 'pending']),
        ];
        
        // آخر الطلبات
        $recentOrders = $this->orderModel->getByUser($user['id'], 1, 5)['orders'];
        
        // الإشعارات غير المقروءة
        $notificationModel = new Notification();
        $unreadNotifications = $notificationModel->getUnreadCount($user['id']);
        
        $this->view('user/dashboard', [
            'title' => 'لوحة التحكم',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'unreadNotifications' => $unreadNotifications
        ]);
    }
    
    /**
     * الملف الشخصي
     */
    public function profile(): void
    {
        $user = $this->requireAuth();
        
        $this->view('user/profile', [
            'title' => 'الملف الشخصي',
            'profile' => $this->userModel->findById($user['id'])
        ]);
    }
    
    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        // التحقق من CSRF
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج');
            $this->redirect('profile');
        }
        
        $validator = new Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->required('phone', 'رقم الهاتف مطلوب');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('profile');
        }
        
        // تحديث البيانات
        $updateData = [
            'name' => $data['name'],
            'phone' => $data['phone']
        ];
        
        // تغيير كلمة المرور إذا تم إدخالها
        if (!empty($data['new_password'])) {
            $validator = new Validator($data);
            $validator->required('current_password', 'كلمة المرور الحالية مطلوبة')
                      ->minLength('new_password', 8, 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل')
                      ->confirmed('new_password', 'new_password_confirmation', 'كلمات المرور غير متطابقة');
            
            if (!$validator->passes()) {
                $this->flash('error', implode('<br>', $validator->getErrors()));
                $this->redirect('profile');
            }
            
            // التحقق من كلمة المرور الحالية
            $currentUser = $this->userModel->findByIdWithPassword($user['id']);
            if (!password_verify($data['current_password'], $currentUser['password'])) {
                $this->flash('error', 'كلمة المرور الحالية غير صحيحة');
                $this->redirect('profile');
            }
            
            $updateData['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }
        
        $this->userModel->update($user['id'], $updateData);
        
        // تحديث الجلسة
        $updatedUser = $this->userModel->findById($user['id']);
        $_SESSION['user'] = $updatedUser;
        
        $this->flash('success', 'تم تحديث البيانات بنجاح');
        $this->redirect('profile');
    }
}
