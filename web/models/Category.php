<?php
/**
 * Category Model - نموذج التصنيفات
 */

require_once __DIR__ . '/../config/database.php';

class Category {
    private string $table = 'categories';
    private PDO $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * البحث عن تصنيف بالـ ID
     */
    public function findById($id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * البحث عن تصنيف بالـ slug
     */
    public function findBySlug(string $slug): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على جميع التصنيفات
     */
    public function getAll(bool $activeOnly = true): array {
        try {
            $sql = "SELECT * FROM {$this->table}";
            if ($activeOnly) {
                $sql .= " WHERE is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC, name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على التصنيفات الرئيسية (بدون أب)
     */
    public function getMainCategories(): array {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_active = 1 AND parent_id IS NULL 
                    ORDER BY sort_order ASC, name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على التصنيفات الفرعية
     */
    public function getSubCategories(int $parentId): array {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_active = 1 AND parent_id = ? 
                    ORDER BY sort_order ASC, name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$parentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على شجرة التصنيفات
     */
    public function getTree(): array {
        try {
            $allCategories = $this->getAll();
            return $this->buildTree($allCategories);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * بناء الشجرة
     */
    private function buildTree(array $categories, ?int $parentId = null): array {
        $tree = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * عدد المنتجات في التصنيف
     */
    public function getProductCount(int $categoryId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1"
            );
            $stmt->execute([$categoryId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * التصنيفات مع عدد المنتجات
     */
    public function getAllWithProductCount(): array {
        try {
            $sql = "SELECT c.*, COUNT(p.id) as product_count
                    FROM {$this->table} c
                    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                    WHERE c.is_active = 1
                    GROUP BY c.id
                    ORDER BY c.sort_order ASC, c.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * إنشاء تصنيف جديد
     */
    public function create(array $data): ?int {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (name, slug, description, icon, parent_id, is_active, sort_order, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'] ?? '',
                $data['icon'] ?? '📦',
                $data['parent_id'] ?? null,
                $data['is_active'] ?? 1,
                $data['sort_order'] ?? 0
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Category create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * تحديث تصنيف
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $values = [];

            $allowedFields = ['name', 'slug', 'description', 'icon', 'parent_id', 'is_active', 'sort_order'];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
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
     * حذف تصنيف
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
