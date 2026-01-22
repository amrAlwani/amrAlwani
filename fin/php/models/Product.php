<?php
/**
 * Product Model - نموذج المنتج
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
                           COALESCE(p.stock_quantity, p.stock, 0) as stock_quantity,
                           COALESCE(p.stock, p.stock_quantity, 0) as stock,
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
            }

            return $product ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * البحث عن منتج بالـ slug
     */
    public function findBySlug(string $slug): ?array {
        try {
            $sql = "SELECT p.*, 
                           COALESCE(p.stock_quantity, p.stock, 0) as stock_quantity,
                           COALESCE(p.stock, p.stock_quantity, 0) as stock,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.slug = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$slug]);
            $product = $stmt->fetch();

            if ($product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
                $product['dimensions'] = !empty($product['dimensions']) ? json_decode($product['dimensions'], true) : null;
                $product['variants'] = $this->getVariants($product['id']);
            }

            return $product ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على جميع المنتجات مع التصفح والفلترة
     */
    public function getAll(int $page = 1, int $perPage = 12, array $filters = []): array {
        try {
            $page = max(1, $page);
            $perPage = max(1, min(100, $perPage)); // حد أقصى 100 منتج
            $offset = ($page - 1) * $perPage;
            
            $where = ["p.is_active = 1"];
            $params = [];

            // فلتر التصنيف
            if (!empty($filters['category_id'])) {
                $where[] = "p.category_id = ?";
                $params[] = $filters['category_id'];
            }

            // فلتر البحث
            if (!empty($filters['search'])) {
                $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // نطاق السعر - تصحيح: sale_price بدلاً من discount_price
            if (!empty($filters['min_price'])) {
                $where[] = "COALESCE(p.sale_price, p.price) >= ?";
                $params[] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $where[] = "COALESCE(p.sale_price, p.price) <= ?";
                $params[] = $filters['max_price'];
            }

            // متوفر في المخزون - دعم كلا العمودين
            if (!empty($filters['in_stock'])) {
                $where[] = "COALESCE(p.stock_quantity, p.stock, 0) > 0";
            }

            $whereClause = implode(' AND ', $where);

            // الترتيب - تصحيح: sale_price بدلاً من discount_price
            $orderBy = "p.created_at DESC";
            if (!empty($filters['sort'])) {
                $allowedSorts = [
                    'price_asc' => "COALESCE(p.sale_price, p.price) ASC",
                    'price_desc' => "COALESCE(p.sale_price, p.price) DESC",
                    'newest' => "p.created_at DESC",
                    'popular' => "p.sales_count DESC",
                    'views' => "p.views_count DESC"
                ];
                $orderBy = $allowedSorts[$filters['sort']] ?? "p.created_at DESC";
            }

            // عد الإجمالي
            $countSql = "SELECT COUNT(*) FROM {$this->table} p WHERE {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // تصحيح: استخدام bindParam مع LIMIT/OFFSET وإضافة stock_quantity
            $sql = "SELECT p.*, 
                           COALESCE(p.stock_quantity, p.stock, 0) as stock_quantity,
                           COALESCE(p.stock, p.stock_quantity, 0) as stock,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE {$whereClause}
                    ORDER BY {$orderBy}
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            // ربط المعلمات
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $products = $stmt->fetchAll();

            foreach ($products as &$product) {
                $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
            }

            return [
                'products' => $products,
                'total' => $total
            ];
        } catch (PDOException $e) {
            return [
                'products' => [],
                'total' => 0
            ];
        }
    }

    /**
     * الحصول على المنتجات المميزة
     */
    public function getFeatured(int $limit = 8): array {
        try {
            $limit = max(1, min(50, $limit));
            
            $sql = "SELECT p.*, 
                           COALESCE(p.stock_quantity, p.stock, 0) as stock_quantity,
                           COALESCE(p.stock, p.stock_quantity, 0) as stock,
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
     * الحصول على المنتجات ذات الصلة
     */
    public function getRelated($productId, $categoryId, int $limit = 4): array {
        try {
            $limit = max(1, min(20, $limit));
            
            $sql = "SELECT p.*, 
                           COALESCE(p.stock_quantity, p.stock, 0) as stock_quantity,
                           COALESCE(p.stock, p.stock_quantity, 0) as stock,
                           c.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1 AND p.category_id = :category_id AND p.id != :product_id
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
    public function getVariants($productId): array {
        try {
            $sql = "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            $variants = $stmt->fetchAll();

            foreach ($variants as &$variant) {
                $variant['attributes'] = !empty($variant['attributes']) ? json_decode($variant['attributes'], true) : [];
            }

            return $variants;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * زيادة عدد المشاهدات
     */
    public function incrementViews($id): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * التحقق من المخزون - دعم كلا العمودين stock و stock_quantity
     */
    public function checkStock($id, int $quantity, ?int $variantId = null): bool {
        try {
            if ($variantId) {
                $stmt = $this->db->prepare("SELECT COALESCE(stock_quantity, stock, 0) as stock_quantity FROM product_variants WHERE id = ? AND product_id = ?");
                $stmt->execute([$variantId, $id]);
            } else {
                $stmt = $this->db->prepare("SELECT COALESCE(stock_quantity, stock, 0) as stock_quantity FROM {$this->table} WHERE id = ?");
                $stmt->execute([$id]);
            }
            $result = $stmt->fetch();
            return $result && (int)$result['stock_quantity'] >= $quantity;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * تقليل المخزون - تصحيح: stock_quantity بدلاً من stock
     */
    public function reduceStock($id, int $quantity, ?int $variantId = null): bool {
        try {
            if ($variantId) {
                $stmt = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ? AND product_id = ?");
                return $stmt->execute([$quantity, $variantId, $id]);
            } else {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET stock_quantity = stock_quantity - ? WHERE id = ?");
                return $stmt->execute([$quantity, $id]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * استعادة المخزون - تصحيح: stock_quantity بدلاً من stock
     */
    public function restoreStock($id, int $quantity, ?int $variantId = null): bool {
        try {
            if ($variantId) {
                $stmt = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE id = ? AND product_id = ?");
                return $stmt->execute([$quantity, $variantId, $id]);
            } else {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET stock_quantity = stock_quantity + ? WHERE id = ?");
                return $stmt->execute([$quantity, $id]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
