<?php
/**
 * ProfileController - الملف الشخصي
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/User.php';

class ProfileController extends BaseController {

    private \User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new \User();
    }

    /**
     * عرض الملف الشخصي
     */
    public function index(): void {
        $user = $this->requireAuth();

        // جلب العناوين
        $addresses = $this->userModel->getAddresses($user['id']);
        
        // جلب الإشعارات
        $notifications = $this->userModel->getNotifications($user['id'], 5);
        $unreadCount = $this->userModel->getUnreadNotificationCount($user['id']);

        $this->view('profile/index', [
            'title' => 'الملف الشخصي',
            'user' => $user,
            'addresses' => $addresses,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'csrf_token' => $this->getCsrfToken(),
        ], 'main');
    }

    /**
     * صفحة تعديل الملف الشخصي
     */
    public function edit(): void {
        $user = $this->requireAuth();

        $this->view('profile/edit', [
            'title' => 'تعديل الملف الشخصي',
            'user' => $user,
            'csrf_token' => $this->getCsrfToken(),
        ], 'main');
    }

    /**
     * تحديث الملف الشخصي
     */
    public function update(): void {
        $user = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/profile.php?action=edit');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        // معالجة الصورة الرمزية
        if (!empty($_FILES['avatar']['name'])) {
            require_once BASEPATH . '/utils/FileUpload.php';
            $uploader = new \FileUpload('avatars');
            $avatarPath = $uploader->upload($_FILES['avatar']);
            if ($avatarPath) {
                $data['avatar'] = $avatarPath;
            }
        }

        $updatedUser = $this->userModel->update($user['id'], $data);

        if ($updatedUser) {
            $_SESSION['user'] = $updatedUser;
            $this->setFlash('success', 'تم تحديث الملف الشخصي');
        } else {
            $this->setFlash('error', 'فشل في التحديث');
        }

        $this->redirect('/profile.php');
    }

    /**
     * صفحة تغيير كلمة المرور
     */
    public function changePassword(): void {
        $user = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$this->verifyCsrfToken($csrfToken)) {
                $this->setFlash('error', 'طلب غير صالح');
                $this->redirect('/profile.php?action=change-password');
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // التحقق
            if (strlen($newPassword) < 6) {
                $this->setFlash('error', 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                $this->redirect('/profile.php?action=change-password');
            }

            if ($newPassword !== $confirmPassword) {
                $this->setFlash('error', 'كلمتا المرور غير متطابقتين');
                $this->redirect('/profile.php?action=change-password');
            }

            // التحقق من كلمة المرور الحالية
            $fullUser = $this->userModel->findByEmail($user['email']);
            if (!$fullUser || !password_verify($currentPassword, $fullUser['password'])) {
                $this->setFlash('error', 'كلمة المرور الحالية غير صحيحة');
                $this->redirect('/profile.php?action=change-password');
            }

            // تحديث كلمة المرور
            if ($this->userModel->updatePassword($user['id'], $newPassword)) {
                $this->setFlash('success', 'تم تغيير كلمة المرور');
                $this->redirect('/profile.php');
            } else {
                $this->setFlash('error', 'فشل في تغيير كلمة المرور');
                $this->redirect('/profile.php?action=change-password');
            }
        }

        $this->view('profile/change-password', [
            'title' => 'تغيير كلمة المرور',
            'user' => $user,
            'csrf_token' => $this->getCsrfToken(),
        ], 'main');
    }

    /**
     * إدارة العناوين
     */
    public function addresses(): void {
        $user = $this->requireAuth();
        $addresses = $this->userModel->getAddresses($user['id']);

        $this->view('profile/addresses', [
            'title' => 'عناويني',
            'user' => $user,
            'addresses' => $addresses,
            'csrf_token' => $this->getCsrfToken(),
        ], 'main');
    }

    /**
     * إضافة عنوان
     */
    public function addAddress(): void {
        $user = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile.php?action=addresses');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/profile.php?action=addresses');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'street' => trim($_POST['street'] ?? ''),
            'building' => trim($_POST['building'] ?? ''),
            'is_default' => isset($_POST['is_default']),
        ];

        if ($this->userModel->addAddress($user['id'], $data)) {
            $this->setFlash('success', 'تم إضافة العنوان');
        } else {
            $this->setFlash('error', 'فشل في إضافة العنوان');
        }

        $this->redirect('/profile.php?action=addresses');
    }

    /**
     * حذف عنوان
     */
    public function deleteAddress(int $id): void {
        $user = $this->requireAuth();

        if ($this->userModel->deleteAddress($user['id'], $id)) {
            $this->setFlash('success', 'تم حذف العنوان');
        } else {
            $this->setFlash('error', 'فشل في حذف العنوان');
        }

        $this->redirect('/profile.php?action=addresses');
    }
}
