<?php
/**
 * Product Model - النسخة الشاملة والمتوافقة تماماً
 */

class Product extends Model {
    protected string $table = 'products';

    /**
     * جلب كافة المنتجات مع الترقيم والتصفية
     */
    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "name LIKE :search";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        $whereClause = implode(" AND ", $where);

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}");
        $stmtTotal->execute($params);
        $total = (int)$stmtTotal->fetchColumn();

        return [
            'products' => $products,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * جلب أحدث المنتجات (إصلاح خطأ HomeController السطر 27)
     */
    public function getLatest(int $limit = 8): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * جلب المنتجات المميزة (إصلاح خطأ HomeController السطر 26)
     */
    public function getFeatured(int $limit = 8): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * إيجاد منتج بواسطة المعرف
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * إضافة منتج جديد
     */
    public function create(array $data): ?int {
        $sql = "INSERT INTO {$this->table} (name, slug, description, price, discount_price, stock, category_id, image, is_active, is_featured) 
                VALUES (:name, :slug, :description, :price, :discount_price, :stock, :category_id, :image, :is_active, :is_featured)";
        
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($data)) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    /**
     * تحديث منتج
     */
    public function update(int $id, array $data): bool {
        $fields = "";
        foreach ($data as $key => $value) {
            if ($key === 'id') continue;
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ", ");
        
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        
        return $this->db->prepare($sql)->execute($data);
    }

    /**
     * حذف منتج
     */
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?")->execute([$id]);
    }

    /**
     * حساب إجمالي المنتجات
     */
    public function count(array $conditions = []): int {
        $where = "1=1";
        $params = [];
        
        if (!empty($conditions)) {
            $parts = [];
            foreach ($conditions as $key => $value) {
                $parts[] = "$key = :$key";
                $params[$key] = $value;
            }
            $where = implode(" AND ", $parts);
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}