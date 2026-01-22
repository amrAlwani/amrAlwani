<?php
/**
 * NotificationController - الإشعارات
 */

require_once BASEPATH . '/models/Notification.php';

class NotificationController extends Controller
{
    private Notification $notificationModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
    }
    
    /**
     * قائمة الإشعارات
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $notifications = $this->notificationModel->getByUser($user['id']);
        
        $this->view('notifications/index', [
            'title' => 'الإشعارات',
            'notifications' => $notifications
        ]);
    }
    
    /**
     * قراءة إشعار
     */
    public function markRead(string $id): void
    {
        $user = $this->requireAuth();
        $this->notificationModel->markAsRead((int)$id, $user['id']);
        
        // العودة للصفحة السابقة
        $referer = $_SERVER['HTTP_REFERER'] ?? url('notifications');
        header('Location: ' . $referer);
        exit;
    }
}
