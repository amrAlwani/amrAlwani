<?php
/**
 * Review Model - نموذج المراجعات
 */
require_once __DIR__ . '/../config/database.php';

class Review {
    private PDO $db;
    private string $table = 'reviews';

    public function __construct() {
        $this->db = db();
    }

    /**
     * الحصول على مراجعات منتج
     */
    public function getByProduct(int $productId, int $page = 1, int $perPage = 10): array {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = :product_id AND is_approved = 1");
        $stmt->execute([':product_id' => $productId]);
        $total = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT r.*, u.name as user_name, u.avatar as user_avatar
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = :product_id AND r.is_approved = 1
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // إحصائيات التقييمات
        $stats = $this->getProductStats($productId);

        return [
            'reviews' => $reviews,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'stats' => $stats
        ];
    }

    /**
     * إحصائيات تقييمات المنتج
     */
    public function getProductStats(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM {$this->table}
            WHERE product_id = :product_id AND is_approved = 1
        ");
        $stmt->execute([':product_id' => $productId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int)$stats['total_reviews'],
            'average' => round((float)$stats['average_rating'], 1),
            'breakdown' => [
                5 => (int)$stats['five_star'],
                4 => (int)$stats['four_star'],
                3 => (int)$stats['three_star'],
                2 => (int)$stats['two_star'],
                1 => (int)$stats['one_star']
            ]
        ];
    }

    /**
     * إضافة مراجعة
     */
    public function create(int $userId, int $productId, array $data): array {
        // التحقق من عدم وجود مراجعة سابقة
        if ($this->userHasReviewed($userId, $productId)) {
            return ['success' => false, 'message' => 'لقد قمت بتقييم هذا المنتج مسبقاً'];
        }

        // التحقق من أن المستخدم اشترى المنتج
        $orderId = $this->getUserOrderWithProduct($userId, $productId);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, product_id, order_id, rating, title, comment)
            VALUES (:user_id, :product_id, :order_id, :rating, :title, :comment)
        ");

        $success = $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':order_id' => $orderId,
            ':rating' => min(5, max(1, (int)$data['rating'])),
            ':title' => $data['title'] ?? null,
            ':comment' => $data['comment'] ?? null
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'تم إضافة تقييمك بنجاح'];
        }

        return ['success' => false, 'message' => 'حدث خطأ'];
    }

    /**
     * تحديث مراجعة
     */
    public function update(int $id, int $userId, array $data): array {
        $review = $this->findById($id);
        if (!$review || $review['user_id'] != $userId) {
            return ['success' => false, 'message' => 'غير مصرح لك'];
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET
                rating = :rating,
                title = :title,
                comment = :comment,
                is_approved = 0
            WHERE id = :id
        ");

        $success = $stmt->execute([
            ':id' => $id,
            ':rating' => min(5, max(1, (int)($data['rating'] ?? $review['rating']))),
            ':title' => $data['title'] ?? $review['title'],
            ':comment' => $data['comment'] ?? $review['comment']
        ]);

        return $success 
            ? ['success' => true, 'message' => 'تم تحديث التقييم']
            : ['success' => false, 'message' => 'حدث خطأ'];
    }

    /**
     * حذف مراجعة
     */
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * البحث بالمعرف
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * التحقق من وجود مراجعة للمستخدم
     */
    public function userHasReviewed(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
        return (bool)$stmt->fetch();
    }

    /**
     * الحصول على طلب المستخدم الذي يحتوي على المنتج
     */
    private function getUserOrderWithProduct(int $userId, int $productId): ?int {
        $stmt = $this->db->prepare("
            SELECT o.id 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id 
              AND oi.product_id = :product_id 
              AND o.status = 'delivered'
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['id'] : null;
    }

    /**
     * الموافقة على مراجعة (للمدير)
     */
    public function approve(int $id): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_approved = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * مراجعات بانتظار الموافقة (للمدير)
     */
    public function getPending(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE is_approved = 0");
        $stmt->execute();
        $total = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT r.*, u.name as user_name, p.name as product_name
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE r.is_approved = 0
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'reviews' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }
}
