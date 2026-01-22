<?php
/**
 * Coupon Model - نموذج الكوبونات
 */
require_once __DIR__ . '/../config/database.php';

class Coupon {
    private PDO $db;
    private string $table = 'coupons';

    public function __construct() {
        $this->db = db();
    }

    /**
     * البحث بالكود
     */
    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code");
        $stmt->execute([':code' => strtoupper($code)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
     * التحقق من صلاحية الكوبون
     */
    public function validate(string $code, float $orderAmount): array {
        $coupon = $this->findByCode($code);

        if (!$coupon) {
            return ['valid' => false, 'message' => 'الكوبون غير موجود'];
        }

        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'الكوبون غير مفعل'];
        }

        if ($coupon['start_date'] && strtotime($coupon['start_date']) > time()) {
            return ['valid' => false, 'message' => 'الكوبون لم يبدأ بعد'];
        }

        if ($coupon['end_date'] && strtotime($coupon['end_date']) < time()) {
            return ['valid' => false, 'message' => 'انتهت صلاحية الكوبون'];
        }

        if ($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'تم استخدام الكوبون بالكامل'];
        }

        if ($coupon['min_order_amount'] && $orderAmount < $coupon['min_order_amount']) {
            return [
                'valid' => false, 
                'message' => 'الحد الأدنى للطلب ' . number_format($coupon['min_order_amount'], 2) . ' ' . CURRENCY_SYMBOL
            ];
        }

        // حساب الخصم
        $discount = $coupon['type'] === 'percentage'
            ? ($orderAmount * $coupon['value'] / 100)
            : $coupon['value'];

        // الحد الأقصى للخصم
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'تم تطبيق الكوبون بنجاح'
        ];
    }

    /**
     * الحصول على جميع الكوبونات
     */
    public function getAll(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = (int)$stmt->fetch()['total'];

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'coupons' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    /**
     * إنشاء كوبون
     */
    public function create(array $data): ?array {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (code, type, value, min_order_amount, max_discount, max_uses, start_date, end_date, is_active)
            VALUES 
            (:code, :type, :value, :min_order, :max_discount, :max_uses, :start_date, :end_date, :is_active)
        ");

        $success = $stmt->execute([
            ':code' => strtoupper($data['code']),
            ':type' => $data['type'] ?? 'fixed',
            ':value' => $data['value'],
            ':min_order' => $data['min_order_amount'] ?? null,
            ':max_discount' => $data['max_discount'] ?? null,
            ':max_uses' => $data['max_uses'] ?? null,
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':is_active' => $data['is_active'] ?? 1
        ]);

        return $success ? $this->findById($this->db->lastInsertId()) : null;
    }

    /**
     * تحديث كوبون
     */
    public function update(int $id, array $data): ?array {
        $coupon = $this->findById($id);
        if (!$coupon) return null;

        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET
                code = :code,
                type = :type,
                value = :value,
                min_order_amount = :min_order,
                max_discount = :max_discount,
                max_uses = :max_uses,
                start_date = :start_date,
                end_date = :end_date,
                is_active = :is_active
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id,
            ':code' => strtoupper($data['code'] ?? $coupon['code']),
            ':type' => $data['type'] ?? $coupon['type'],
            ':value' => $data['value'] ?? $coupon['value'],
            ':min_order' => $data['min_order_amount'] ?? $coupon['min_order_amount'],
            ':max_discount' => $data['max_discount'] ?? $coupon['max_discount'],
            ':max_uses' => $data['max_uses'] ?? $coupon['max_uses'],
            ':start_date' => $data['start_date'] ?? $coupon['start_date'],
            ':end_date' => $data['end_date'] ?? $coupon['end_date'],
            ':is_active' => $data['is_active'] ?? $coupon['is_active']
        ]);

        return $this->findById($id);
    }

    /**
     * حذف كوبون
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * زيادة عداد الاستخدام
     */
    public function incrementUsage(string $code): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET used_count = used_count + 1 WHERE code = :code");
        return $stmt->execute([':code' => strtoupper($code)]);
    }

    /**
     * جميع الكوبونات (للـ Admin)
     */
    public function all(array $conditions = [], string $orderBy = 'created_at DESC'): array {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
