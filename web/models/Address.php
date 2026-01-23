<?php
/**
 * Address Model - نموذج العناوين
 */
require_once __DIR__ . '/../config/database.php';

class Address {
    private PDO $db;
    private string $table = 'addresses';

    public function __construct() {
        $this->db = db();
    }

    /**
     * الحصول على عناوين المستخدم
     */
    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = :user_id 
            ORDER BY is_default DESC, created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * الحصول على العنوان الافتراضي
     */
    public function getDefault(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_default = 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * إضافة عنوان جديد
     * يقبل إما (int $userId, array $data) أو (array $data) مع user_id داخل الـ data
     */
    public function create($userIdOrData, ?array $data = null): ?array {
        // دعم الاستخدامين: create($userId, $data) أو create($data)
        if (is_array($userIdOrData)) {
            $data = $userIdOrData;
            $userId = $data['user_id'] ?? null;
            if (!$userId) {
                return null;
            }
        } else {
            $userId = $userIdOrData;
        }

        // إذا كان العنوان الأول أو محدد كافتراضي
        if ($data['is_default'] ?? false) {
            $this->clearDefault($userId);
        }

        // إذا لا توجد عناوين، اجعله افتراضي
        $count = $this->getCount($userId);
        if ($count === 0) {
            $data['is_default'] = true;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (user_id, name, phone, city, district, street, building, floor, apartment, notes, is_default)
            VALUES 
            (:user_id, :name, :phone, :city, :district, :street, :building, :floor, :apartment, :notes, :is_default)
        ");

        $success = $stmt->execute([
            ':user_id' => $userId,
            ':name' => $data['name'] ?? '',
            ':phone' => $data['phone'] ?? '',
            ':city' => $data['city'] ?? '',
            ':district' => $data['district'] ?? null,
            ':street' => $data['street'] ?? '',
            ':building' => $data['building'] ?? null,
            ':floor' => $data['floor'] ?? null,
            ':apartment' => $data['apartment'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':is_default' => ($data['is_default'] ?? false) ? 1 : 0
        ]);

        if ($success) {
            return $this->findById($this->db->lastInsertId());
        }

        return null;
    }

    /**
     * تحديث عنوان
     */
    public function update(int $id, int $userId, array $data): ?array {
        $address = $this->findById($id);
        if (!$address || $address['user_id'] != $userId) {
            return null;
        }

        if ($data['is_default'] ?? false) {
            $this->clearDefault($userId);
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET
                name = :name,
                phone = :phone,
                city = :city,
                district = :district,
                street = :street,
                building = :building,
                floor = :floor,
                apartment = :apartment,
                notes = :notes,
                is_default = :is_default
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'] ?? $address['name'],
            ':phone' => $data['phone'] ?? $address['phone'],
            ':city' => $data['city'] ?? $address['city'],
            ':district' => $data['district'] ?? $address['district'],
            ':street' => $data['street'] ?? $address['street'],
            ':building' => $data['building'] ?? $address['building'],
            ':floor' => $data['floor'] ?? $address['floor'],
            ':apartment' => $data['apartment'] ?? $address['apartment'],
            ':notes' => $data['notes'] ?? $address['notes'],
            ':is_default' => isset($data['is_default']) ? ($data['is_default'] ? 1 : 0) : $address['is_default']
        ]);

        return $this->findById($id);
    }

    /**
     * حذف عنوان
     */
    public function delete(int $id, int $userId): bool {
        $address = $this->findById($id);
        if (!$address || $address['user_id'] != $userId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);

        // إذا كان العنوان المحذوف هو الافتراضي، اجعل الأول افتراضي
        if ($result && $address['is_default']) {
            $first = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
            $first->execute([':user_id' => $userId]);
            $firstAddr = $first->fetch();
            if ($firstAddr) {
                $this->setDefault($firstAddr['id'], $userId);
            }
        }

        return $result;
    }

    /**
     * تعيين عنوان كافتراضي
     */
    public function setDefault(int $id, int $userId): bool {
        $address = $this->findById($id);
        if (!$address || $address['user_id'] != $userId) {
            return false;
        }

        $this->clearDefault($userId);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_default = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * إلغاء جميع العناوين الافتراضية
     */
    private function clearDefault(int $userId): void {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }

    /**
     * عدد العناوين
     */
    public function getCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetch()['count'];
    }
}
