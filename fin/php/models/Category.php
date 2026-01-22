<?php
/**
 * Category Model - نموذج التصنيفات
 * 
 * ملف جديد مفقود من المستودع الأصلي
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
     * الحصول على التصنيفات الرئيسية (بدون parent)
     */
    public function getMainCategories(bool $activeOnly = true): array {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE parent_id IS NULL";
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
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
     * الحصول على التصنيفات الفرعية
     */
    public function getSubCategories(int $parentId, bool $activeOnly = true): array {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE parent_id = ?";
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC, name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$parentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على شجرة التصنيفات (مع التصنيفات الفرعية)
     */
    public function getTree(bool $activeOnly = true): array {
        $categories = $this->getAll($activeOnly);
        return $this->buildTree($categories);
    }

    /**
     * بناء شجرة التصنيفات
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
     * عد المنتجات في التصنيف
     */
    public function getProductCount(int $categoryId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
            $stmt->execute([$categoryId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
