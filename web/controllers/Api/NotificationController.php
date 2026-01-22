<?php
/**
 * API NotificationController
 */

namespace Api;

require_once BASEPATH . '/models/Notification.php';

class NotificationController extends \Controller
{
    private \Notification $notificationModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new \Notification();
    }
    
    /**
     * قائمة الإشعارات
     */
    public function index(): void
    {
        $user = \Auth::requireAuth();
        $notifications = $this->notificationModel->getByUser($user['id']);
        
        \Response::success([
            'notifications' => $notifications,
            'unread_count' => $this->notificationModel->getUnreadCount($user['id'])
        ]);
    }
    
    /**
     * قراءة إشعار
     */
    public function markRead(string $id): void
    {
        $user = \Auth::requireAuth();
        $this->notificationModel->markAsRead((int)$id, $user['id']);
        
        \Response::success(null, 'تم');
    }
    
    /**
     * قراءة الكل
     */
    public function markAllRead(): void
    {
        $user = \Auth::requireAuth();
        $this->notificationModel->markAllAsRead($user['id']);
        
        \Response::success(null, 'تم قراءة جميع الإشعارات');
    }
}
