<?php
/**
 * User Model - نموذج المستخدم المصحح
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
     * البحث عن مستخدم بالهاتف
     */
    public function findByPhone(string $phone): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE phone = ?");
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['password']);
            }

            return $user ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * إنشاء مستخدم جديد
     * تم التصحيح: التعامل مع phone كحقل مطلوب
     */
    public function create(array $data): ?int {
        try {
            // التحقق من الحقول المطلوبة
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return null;
            }

            // تشفير كلمة المرور
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            // تصحيح: phone مطلوب في Schema - إضافة قيمة افتراضية
            $phone = $data['phone'] ?? '0000000000';

            // تصحيح: role يجب أن يكون 'user' وليس 'customer'
            $role = $data['role'] ?? 'user';
            if (!in_array($role, ['user', 'vendor', 'admin'])) {
                $role = 'user';
            }

            $sql = "INSERT INTO {$this->table} 
                    (name, email, phone, password, role, avatar, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $phone,
                $hashedPassword,
                $role,
                $data['avatar'] ?? null
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * تحديث مستخدم
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $values = [];

            // الحقول المسموح بتحديثها
            $allowedFields = ['name', 'phone', 'avatar'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $fields[] = "updated_at = NOW()";
            $values[] = $id;

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword(int $id, string $newPassword): bool {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET password = ?, last_password_change = NOW(), updated_at = NOW() WHERE id = ?"
            );
            return $stmt->execute([$hashedPassword, $id]);

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * التحقق من كلمة المرور
     */
    public function verifyPassword(string $email, string $password): ?array {
        $user = $this->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            // تسجيل محاولة فاشلة
            $this->recordFailedLogin($user['id']);
            return null;
        }

        // إعادة تعيين محاولات الدخول الفاشلة
        $this->resetFailedLogins($user['id']);

        // تحديث وقت آخر دخول
        $this->updateLastLogin($user['id']);

        unset($user['password']);
        return $user;
    }

    /**
     * تسجيل محاولة دخول فاشلة
     */
    private function recordFailedLogin(int $userId): void {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?"
            );
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }
    }

    /**
     * إعادة تعيين محاولات الدخول الفاشلة
     */
    private function resetFailedLogins(int $userId): void {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET failed_login_attempts = 0, account_locked_until = NULL WHERE id = ?"
            );
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }
    }

    /**
     * تحديث وقت آخر دخول
     */
    private function updateLastLogin(int $userId): void {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }
    }

    /**
     * التحقق من قفل الحساب
     */
    public function isAccountLocked(string $email): bool {
        try {
            $stmt = $this->db->prepare(
                "SELECT account_locked_until FROM {$this->table} WHERE email = ?"
            );
            $stmt->execute([$email]);
            $result = $stmt->fetch();

            if ($result && $result['account_locked_until']) {
                return strtotime($result['account_locked_until']) > time();
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * الحصول على عناوين المستخدم
     */
    public function getAddresses(int $userId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * إضافة عنوان
     */
    public function addAddress(int $userId, array $data): ?int {
        try {
            // إذا كان العنوان الأول، اجعله افتراضي
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
            $stmt->execute([$userId]);
            $isFirst = $stmt->fetchColumn() == 0;

            $sql = "INSERT INTO addresses (user_id, name, phone, address_line, city, state, country, postal_code, is_default, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $data['name'],
                $data['phone'],
                $data['address_line'],
                $data['city'],
                $data['state'] ?? null,
                $data['country'] ?? 'SA',
                $data['postal_code'] ?? null,
                $isFirst || ($data['is_default'] ?? false) ? 1 : 0
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * حذف عنوان
     */
    public function deleteAddress(int $userId, int $addressId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            return $stmt->execute([$addressId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * البحث عن مستخدم بالـ ID مع كلمة المرور (للتحقق)
     */
    public function findByIdWithPassword(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على جميع المستخدمين (للمدير)
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array {
        try {
            $where = ["1=1"];
            $params = [];

            if (!empty($filters['role'])) {
                $where[] = "role = :role";
                $params['role'] = $filters['role'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if (isset($filters['is_active'])) {
                $where[] = "is_active = :is_active";
                $params['is_active'] = $filters['is_active'];
            }

            $whereClause = implode(' AND ', $where);

            // عدد الإجمالي
            $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $offset = ($page - 1) * $perPage;

            $sql = "SELECT id, name, email, phone, role, avatar, is_active, created_at, last_login_at
                    FROM {$this->table}
                    WHERE {$whereClause}
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'users' => $stmt->fetchAll(),
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int)ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            return ['users' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        }
    }

    /**
     * عدد المستخدمين
     */
    public function count(array $conditions = []): int {
        try {
            $where = [];
            $params = [];

            if (isset($conditions['role'])) {
                $where[] = "role = ?";
                $params[] = $conditions['role'];
            }

            if (isset($conditions['is_active'])) {
                $where[] = "is_active = ?";
                $params[] = $conditions['is_active'];
            }

            $sql = "SELECT COUNT(*) FROM {$this->table}";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Paginate (للتوافق مع AdminController)
     */
    public function paginate(int $page = 1, int $perPage = 20, array $conditions = []): array {
        $result = $this->getAll($page, $perPage, $conditions);
        // تحويل users إلى data للتوافق مع AdminController
        return [
            'data' => $result['users'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'last_page' => $result['last_page']
        ];
    }
}
