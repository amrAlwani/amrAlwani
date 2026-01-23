<?php
/**
 * Wishlist Model - قائمة المفضلة
 */
require_once __DIR__ . '/../config/database.php';

class Wishlist {
    private PDO $db;
    private string $table = 'wishlists';

    public function __construct() {
        $this->db = db();
    }

    /**
     * الحصول على مفضلة المستخدم
     */
    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT w.*, p.name, p.slug, p.image, p.price, p.sale_price, p.stock_quantity,
                   c.name as category_name
            FROM {$this->table} w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = :user_id AND p.is_active = 1
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * إضافة منتج للمفضلة
     */
    public function add(int $userId, int $productId): bool {
        // التحقق من وجود المنتج
        $stmt = $this->db->prepare("SELECT id FROM products WHERE id = :id AND is_active = 1");
        $stmt->execute([':id' => $productId]);
        if (!$stmt->fetch()) {
            return false;
        }

        // التحقق من عدم وجوده مسبقاً
        if ($this->exists($userId, $productId)) {
            return true;
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, product_id) VALUES (:user_id, :product_id)");
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }

    /**
     * إزالة منتج من المفضلة
     */
    public function remove(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id");
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }

    /**
     * التحقق من وجود منتج في المفضلة
     */
    public function exists(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
        return (bool)$stmt->fetch();
    }

    /**
     * عدد عناصر المفضلة
     */
    public function getCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetch()['count'];
    }

    /**
     * تفريغ المفضلة
     */
    public function clear(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * نقل المنتج للسلة
     */
    public function moveToCart(int $userId, int $productId): bool {
        $cartModel = new Cart();
        
        try {
            $cartModel->addItem($userId, $productId, 1);
            $this->remove($userId, $productId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * إضافة/إزالة من المفضلة (Toggle)
     */
    public function toggle(int $userId, int $productId): array {
        if ($this->exists($userId, $productId)) {
            $this->remove($userId, $productId);
            return ['added' => false, 'removed' => true];
        } else {
            $success = $this->add($userId, $productId);
            return ['added' => $success, 'removed' => false];
        }
    }
}
