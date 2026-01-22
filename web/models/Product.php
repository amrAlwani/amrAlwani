<?php
/**
 * Product Model - نموذج المنتج المصحح
 *
 * تم التصحيح:
 * - تصحيح أسماء الأعمدة (stock_quantity بدلاً من stock، sale_price بدلاً من discount_price)
 * - إصلاح مشكلة LIMIT/OFFSET مع PDO
 * - إضافة معالجة أفضل للأخطاء
 * - تصحيح vendor_id (غير موجود في Schema)
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private string $table = 'products';
    private PDO $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * البحث عن منتج بالـ ID
     */
    public function findById($id): ?array {
        if (empty($id)) {
            return null;
        }

        try {
            // تصحيح: إزالة vendor_id لأنه غير موجود في Schema
            // إضافة stock_quantity alias لتوافق Flutter
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           COALESCE(p.stock_quantity, 0) as stock,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $product = $stmt->fetch();

            if ($product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
                $product['dimensions'] = !empty($product['dimensions']) ? json_decode($product['dimensions'], true) : null;
                $product['variants'] = $this->getVariants($id);
                $product['reviews_count'] = $this->getReviewsCount($id);
                $product['average_rating'] = $this->getAverageRating($id);
            }

            return $product ?: null;
        } catch (PDOException $e) {
            error_log("Product findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * البحث عن منتج بالـ slug
     */
    public function findBySlug(string $slug): ?array {
        try {
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.slug = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$slug]);
            $product = $stmt->fetch();

            if ($product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
                $product['variants'] = $this->getVariants($product['id']);
            }

            return $product ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على جميع المنتجات مع الفلترة والتصفح
     * تم التصحيح: إصلاح LIMIT/OFFSET مع PDO
     */
    public function getAll(int $page = 1, int $perPage = 12, array $filters = []): array {
        try {
            $where = ["p.is_active = 1"];
            $params = [];

            // فلتر التصنيف
            if (!empty($filters['category_id'])) {
                $where[] = "p.category_id = :category_id";
                $params['category_id'] = $filters['category_id'];
            }

            // فلتر البحث
            if (!empty($filters['search'])) {
                $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // فلتر السعر
            if (!empty($filters['min_price'])) {
                $where[] = "COALESCE(p.sale_price, p.price) >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $where[] = "COALESCE(p.sale_price, p.price) <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            // فلتر المخزون - تصحيح: stock_quantity بدلاً من stock
            if (!empty($filters['in_stock'])) {
                $where[] = "p.stock_quantity > 0";
            }

            $whereClause = implode(' AND ', $where);

            // ترتيب - تصحيح: استخدام views_count بدلاً من views
            $orderBy = match($filters['sort'] ?? 'newest') {
                'oldest' => 'p.created_at ASC',
                'price_asc' => 'COALESCE(p.sale_price, p.price) ASC',
                'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
                'popular' => 'p.views_count DESC',
                default => 'p.created_at DESC'
            };

            // عدد الإجمالي
            $countSql = "SELECT COUNT(*) FROM {$this->table} p WHERE {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // تصحيح: حساب OFFSET بشكل صحيح
            $offset = ($page - 1) * $perPage;

            // تصحيح: استخدام bindValue مع PDO::PARAM_INT للـ LIMIT و OFFSET
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           COALESCE(p.stock_quantity, 0) as stock,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE {$whereClause}
                    ORDER BY {$orderBy}
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            // ربط المعاملات العادية
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            // تصحيح مهم: ربط LIMIT و OFFSET كأرقام صحيحة
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $products = $stmt->fetchAll();

            // معالجة الصور
            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return [
                'products' => $products,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int)ceil($total / $perPage)
            ];

        } catch (PDOException $e) {
            error_log("Product getAll error: " . $e->getMessage());
            return ['products' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        }
    }

    /**
     * المنتجات المميزة
     */
    public function getFeatured(int $limit = 8): array {
        try {
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1 AND p.is_featured = 1
                    ORDER BY p.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();

            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return $products;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * أحدث المنتجات
     */
    public function getLatest(int $limit = 8): array {
        try {
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1
                    ORDER BY p.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();

            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return $products;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * المنتجات ذات الصلة
     */
    public function getRelated(int $productId, int $categoryId, int $limit = 4): array {
        try {
            $sql = "SELECT p.*,
                           COALESCE(p.stock_quantity, 0) as stock_quantity
                    FROM {$this->table} p
                    WHERE p.is_active = 1 
                    AND p.category_id = :category_id 
                    AND p.id != :product_id
                    ORDER BY RAND()
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $products = $stmt->fetchAll();

            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return $products;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على متغيرات المنتج
     */
    public function getVariants(int $productId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY sort_order"
            );
            $stmt->execute([$productId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * عدد التقييمات
     */
    private function getReviewsCount(int $productId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM reviews WHERE product_id = ? AND status = 'approved'");
            $stmt->execute([$productId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * متوسط التقييم
     */
    private function getAverageRating(int $productId): float {
        try {
            $stmt = $this->db->prepare("SELECT AVG(rating) FROM reviews WHERE product_id = ? AND status = 'approved'");
            $stmt->execute([$productId]);
            return round((float)$stmt->fetchColumn(), 1);
        } catch (PDOException $e) {
            return 0.0;
        }
    }

    /**
     * زيادة عدد المشاهدات - تصحيح: views_count بدلاً من views
     */
    public function incrementViews(int $productId): void {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?");
            $stmt->execute([$productId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }
    }

    /**
     * تحديث المخزون - تصحيح: stock_quantity
     */
    public function updateStock(int $productId, int $quantity): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?"
            );
        return $stmt->execute([$quantity, $productId, $quantity]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * البحث عن منتجات
     */
    public function search(string $query, int $page = 1, int $perPage = 12): array {
        try {
            $searchTerm = '%' . $query . '%';
            $offset = ($page - 1) * $perPage;

            // عدد النتائج
            $countSql = "SELECT COUNT(*) FROM {$this->table} 
                         WHERE is_active = 1 AND (name LIKE ? OR description LIKE ? OR sku LIKE ?)";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $total = (int)$countStmt->fetchColumn();

            // النتائج
            $sql = "SELECT p.*, 
                           COALESCE(p.stock_quantity, 0) as stock_quantity,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1 AND (p.name LIKE :search1 OR p.description LIKE :search2 OR p.sku LIKE :search3)
                    ORDER BY 
                        CASE WHEN p.name LIKE :search4 THEN 1 ELSE 2 END,
                        p.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':search1', $searchTerm);
            $stmt->bindValue(':search2', $searchTerm);
            $stmt->bindValue(':search3', $searchTerm);
            $stmt->bindValue(':search4', $searchTerm);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();

            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return [
                'products' => $products,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int)ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Product search error: " . $e->getMessage());
            return ['products' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        }
    }

    /**
     * إنشاء منتج جديد
     */
    public function create(array $data): ?int {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (name, slug, description, short_description, price, sale_price, cost_price, 
                     sku, stock_quantity, category_id, image, images, is_active, is_featured, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'] ?? '',
                $data['short_description'] ?? '',
                $data['price'],
                $data['sale_price'] ?? null,
                $data['cost_price'] ?? null,
                $data['sku'] ?? null,
                $data['stock_quantity'] ?? 0,
                $data['category_id'] ?? null,
                $data['image'] ?? null,
                isset($data['images']) ? json_encode($data['images']) : null,
                $data['is_active'] ?? 1,
                $data['is_featured'] ?? 0
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Product create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * تحديث منتج
     */
    public function update(int $id, array $data): bool {
        try {
            $fields = [];
            $values = [];

            $allowedFields = [
                'name', 'slug', 'description', 'short_description', 
                'price', 'sale_price', 'cost_price', 'sku', 
                'stock_quantity', 'category_id', 'image', 'is_active', 'is_featured'
            ];

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
            error_log("Product update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف منتج
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * عدد المنتجات بشروط معينة
     */
    public function count(array $conditions = []): int {
        try {
            $where = [];
            $params = [];

            if (isset($conditions['is_active'])) {
                $where[] = "is_active = ?";
                $params[] = $conditions['is_active'];
            }

            if (isset($conditions['category_id'])) {
                $where[] = "category_id = ?";
                $params[] = $conditions['category_id'];
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
}
