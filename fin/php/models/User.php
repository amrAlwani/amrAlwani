<?php
/**
 * User Model - نموذج المستخدم
 * 
 * تم التصحيح:
 * - تصحيح مشكلة الـ phone (NOT NULL في Schema)
 * - تحسين معالجة الأخطاء
 * - إصلاح role الافتراضي (user بدلاً من customer)
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private string $table = 'users';
    private PDO $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * البحث عن مستخدم بالـ ID
     */
    public function findById($id): ?array {
        if (empty($id)) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['password']); // لا ترجع كلمة المرور
            }

            return $user ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * البحث عن مستخدم بالبريد الإلكتروني (مع كلمة المرور للتحقق)
     */
    public function findByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * إنشاء مستخدم جديد
     * تم التصحيح: التعامل مع phone كحقل مطلوب
     */
    public function create(array $data): ?array {
        try {
            // التحقق من الحقول المطلوبة
            if (empty($data['email'])) {
                return null;
            }

            $fields = [];
            $placeholders = [];
            $values = [];

            // الاسم
            if (isset($data['name'])) {
                $fields[] = 'name';
                $placeholders[] = '?';
                $values[] = $data['name'];
            } else {
                $fields[] = 'name';
                $placeholders[] = '?';
                $values[] = 'مستخدم جديد';
            }

            // البريد الإلكتروني
            $fields[] = 'email';
            $placeholders[] = '?';
            $values[] = $data['email'];

            // الهاتف - تصحيح: حقل مطلوب في Schema
            $fields[] = 'phone';
            $placeholders[] = '?';
            $values[] = $data['phone'] ?? '0000000000'; // قيمة افتراضية إذا لم تُرسل

            // كلمة المرور
            if (isset($data['password'])) {
                $fields[] = 'password';
                $placeholders[] = '?';
                $values[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }

            // الصورة الرمزية
            if (isset($data['avatar'])) {
                $fields[] = 'avatar';
                $placeholders[] = '?';
                $values[] = $data['avatar'];
            }

            // الدور - تصحيح: القيم المسموحة هي 'user' أو 'admin'
            $fields[] = 'role';
            $placeholders[] = '?';
            $allowedRoles = ['user', 'admin'];
            $role = isset($data['role']) && in_array($data['role'], $allowedRoles) ? $data['role'] : 'user';
            $values[] = $role;

            // الحالة النشطة
            $fields[] = 'is_active';
            $placeholders[] = '?';
            $values[] = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->table,
                implode(', ', $fields),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $lastId = $this->db->lastInsertId();
            return $this->findById($lastId);

        } catch (PDOException $e) {
            // التحقق من خطأ التكرار
            if ($e->getCode() == 23000) {
                return null; // البريد الإلكتروني موجود مسبقاً
            }
            return null;
        }
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function update($id, array $data): ?array {
        try {
            $fields = [];
            $values = [];
            $allowedFields = ['name', 'phone', 'avatar'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return $this->findById($id);
            }

            $values[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return $this->findById($id);
        } catch (PDOException $e) {
            return $this->findById($id);
        }
    }

    /**
     * تحديث كلمة المرور
     */
    public function updatePassword($id, string $newPassword): bool {
        try {
            $sql = "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * التحقق من كلمة المرور
     */
    public function verifyPassword(?array $user, string $password): bool {
        if (!$user || !isset($user['password'])) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    /**
     * الحصول على عناوين المستخدم
     */
    public function getAddresses(int $userId): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * إضافة عنوان جديد
     */
    public function addAddress(int $userId, array $data): ?int {
        try {
            // إذا كان العنوان الافتراضي، أزل الافتراضي من العناوين الأخرى
            if (!empty($data['is_default'])) {
                $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            }

            $sql = "INSERT INTO addresses (user_id, name, phone, city, district, street, building, floor, apartment, notes, is_default)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $data['city'] ?? '',
                $data['district'] ?? null,
                $data['street'] ?? '',
                $data['building'] ?? null,
                $data['floor'] ?? null,
                $data['apartment'] ?? null,
                $data['notes'] ?? null,
                !empty($data['is_default']) ? 1 : 0
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * تحديث عنوان
     */
    public function updateAddress(int $userId, int $addressId, array $data): bool {
        try {
            // التحقق من ملكية العنوان
            $stmt = $this->db->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $userId]);
            if (!$stmt->fetch()) {
                return false;
            }

            if (!empty($data['is_default'])) {
                $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            }

            $fields = [];
            $values = [];
            $allowedFields = ['name', 'phone', 'city', 'district', 'street', 'building', 'floor', 'apartment', 'notes', 'is_default'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return true;
            }

            $values[] = $addressId;
            $sql = "UPDATE addresses SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * حذف عنوان
     */
    public function deleteAddress(int $userId, int $addressId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * الحصول على العنوان الافتراضي
     */
    public function getDefaultAddress(int $userId): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على الإشعارات
     */
    public function getNotifications(int $userId, int $limit = 20): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * تحديد الإشعار كمقروء
     */
    public function markNotificationRead(int $userId, int $notificationId): bool {
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$notificationId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * عد الإشعارات غير المقروءة
     */
    public function getUnreadNotificationCount(int $userId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
