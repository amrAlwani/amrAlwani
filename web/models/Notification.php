<?php
/**
 * Notification Model - نموذج الإشعارات
 */
require_once __DIR__ . '/../config/database.php';

class Notification {
    private PDO $db;
    private string $table = 'notifications';

    public function __construct() {
        $this->db = db();
    }

    /**
     * الحصول على إشعارات المستخدم
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $total = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($notifications as &$notification) {
            $notification['data'] = $notification['data'] ? json_decode($notification['data'], true) : null;
        }

        return [
            'notifications' => $notifications,
            'total' => $total,
            'unread_count' => $this->getUnreadCount($userId)
        ];
    }

    /**
     * عدد الإشعارات غير المقروءة
     */
    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetch()['count'];
    }

    /**
     * إنشاء إشعار
     */
    public function create(int $userId, string $title, ?string $body = null, string $type = 'general', ?array $data = null): int {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, title, body, type, data)
            VALUES (:user_id, :title, :body, :type, :data)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':body' => $body,
            ':type' => $type,
            ':data' => $data ? json_encode($data) : null
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * تعليم كمقروء
     */
    public function markAsRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * تعليم الكل كمقروء
     */
    public function markAllAsRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * حذف إشعار
     */
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * حذف الإشعارات القديمة
     */
    public function deleteOld(int $days = 30): int {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
        $stmt->execute([':days' => $days]);
        return $stmt->rowCount();
    }

    /**
     * إرسال إشعار لمجموعة مستخدمين
     */
    public function sendToMany(array $userIds, string $title, ?string $body = null, string $type = 'general', ?array $data = null): int {
        $count = 0;
        foreach ($userIds as $userId) {
            $this->create($userId, $title, $body, $type, $data);
            $count++;
        }
        return $count;
    }

    /**
     * إرسال إشعار للجميع
     */
    public function sendToAll(string $title, ?string $body = null, string $type = 'general', ?array $data = null): int {
        $stmt = $this->db->query("SELECT id FROM users WHERE is_active = 1");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $this->sendToMany($users, $title, $body, $type, $data);
    }
}
