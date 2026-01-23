<?php
/**
 * User Model - النسخة المتوافقة تماماً مع قواعد الكلاس الأب (Model)
 */

class User extends Model {
    protected string $table = 'users';

    /**
     * إنشاء مستخدم جديد
     */
    public function create(array $data): ?int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($data)) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    /**
     * إيجاد مستخدم بواسطة البريد الإلكتروني
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * جلب أحدث المستخدمين للـ Dashboard
     */
    public function getRecent(int $limit = 5): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * حساب إجمالي المستخدمين (متوافق مع Model::count)
     */
    public function count(array $conditions = []): int {
        $where = "1=1";
        $params = [];
        
        if (!empty($conditions)) {
            $parts = [];
            foreach ($conditions as $key => $value) {
                // تجنب معالجة مفاتيح البحث الخاصة بالـ paginate هنا
                if($key === 'search') continue; 
                $parts[] = "$key = :$key";
                $params[$key] = $value;
            }
            if(!empty($parts)) {
                $where = implode(" AND ", $parts);
            }
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * الترقيم للمستخدمين - تم تصحيح التوقيع ليتوافق تماماً مع Model::paginate
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = [], string $orderBy = 'id DESC'): array {
        $offset = ($page - 1) * $perPage;
        $where = "1=1";
        $params = [];

        if (!empty($conditions['search'])) {
            $where .= " AND (name LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$conditions['search']}%";
        }

        // جلب البيانات
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // حساب الإجمالي للترقيم
        $total = $this->count($conditions);

        return [
            'data'         => $users,
            'total'        => $total,
            'current_page' => $page,
            'last_page'    => ceil($total / $perPage)
        ];
    }
}