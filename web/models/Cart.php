<?php
/**
 * Cart Model - نموذج السلة المصحح
 *
 * تم التصحيح:
 * - تصحيح اسم الجدول من 'cart' إلى 'cart_items'
 * - تصحيح أسماء الأعمدة لتتوافق مع الـ SQL Schema
 * - إضافة معالجة أفضل للأخطاء
 */

require_once __DIR__ . '/../config/database.php';

class Cart {
    // تصحيح مهم: اسم الجدول يجب أن يكون cart_items وفقاً للـ Schema
    private string $table = 'cart_items';
    private PDO $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * الحصول على سلة المستخدم
     */
    public function getByUser(int $userId): array {
        try {
            // تصحيح: تعديل أسماء الأعمدة لتتوافق مع Schema
            // products.stock_quantity بدلاً من products.stock
            // products.sale_price بدلاً من products.discount_price
            $sql = "SELECT c.id, c.quantity, c.product_id, c.variant_id,
                           p.name, p.slug, p.price, p.sale_price, p.image, p.stock_quantity,
                           pv.name as variant_name, pv.price as variant_price, pv.stock_quantity as variant_stock
                    FROM {$this->table} c
                    JOIN products p ON c.product_id = p.id
                    LEFT JOIN product_variants pv ON c.variant_id = pv.id
                    WHERE c.user_id = ? AND p.is_active = 1
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $items = $stmt->fetchAll();

            $subtotal = 0;
            foreach ($items as &$item) {
                // حساب السعر الفعلي
                if ($item['variant_id'] && $item['variant_price']) {
                    $item['final_price'] = (float)$item['variant_price'];
                    $item['available_stock'] = (int)$item['variant_stock'];
                } else {
                    $item['final_price'] = $item['sale_price'] ? (float)$item['sale_price'] : (float)$item['price'];
                    $item['available_stock'] = (int)$item['stock_quantity'];
                }

                $item['item_total'] = $item['final_price'] * $item['quantity'];
                $subtotal += $item['item_total'];

                // التحقق من توفر الكمية
                $item['is_available'] = $item['quantity'] <= $item['available_stock'];
            }

            // حساب الضريبة والشحن
            $tax = $subtotal * (defined('TAX_RATE') ? TAX_RATE : 0.15);
            $shipping = $subtotal >= (defined('FREE_SHIPPING_THRESHOLD') ? FREE_SHIPPING_THRESHOLD : 500) 
                        ? 0 
                        : (defined('SHIPPING_COST') ? SHIPPING_COST : 25);
            $total = $subtotal + $tax + $shipping;

            return [
                'items' => $items,
                'count' => count($items),
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'shipping' => round($shipping, 2),
                'total' => round($total, 2),
                'free_shipping_remaining' => max(0, (defined('FREE_SHIPPING_THRESHOLD') ? FREE_SHIPPING_THRESHOLD : 500) - $subtotal)
            ];

        } catch (PDOException $e) {
            error_log("Cart getByUser error: " . $e->getMessage());
            return [
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'total' => 0,
                'free_shipping_remaining' => 0
            ];
        }
    }

    /**
     * إضافة منتج للسلة
     */
    public function addItem(int $userId, int $productId, int $quantity = 1, ?int $variantId = null): array {
        try {
            // التحقق من وجود المنتج وتوفره
            $productSql = "SELECT id, stock_quantity, is_active FROM products WHERE id = ?";
            $productStmt = $this->db->prepare($productSql);
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();

            if (!$product || !$product['is_active']) {
                throw new Exception('المنتج غير متوفر');
            }

            // تصحيح: استخدام stock_quantity
            if ($product['stock_quantity'] < $quantity) {
                throw new Exception('الكمية المطلوبة غير متوفرة في المخزون');
            }

            // التحقق من وجود المنتج في السلة
            $existsSql = "SELECT id, quantity FROM {$this->table} 
                          WHERE user_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))";
            $existsStmt = $this->db->prepare($existsSql);
            $existsStmt->execute([$userId, $productId, $variantId, $variantId]);
            $existingItem = $existsStmt->fetch();

            if ($existingItem) {
                // تحديث الكمية
                $newQuantity = $existingItem['quantity'] + $quantity;

                if ($newQuantity > $product['stock_quantity']) {
                    throw new Exception('الكمية المطلوبة تتجاوز المتوفر في المخزون');
                }

                $updateSql = "UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$newQuantity, $existingItem['id']]);
            } else {
                // إضافة جديدة
                $insertSql = "INSERT INTO {$this->table} (user_id, product_id, variant_id, quantity, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, NOW(), NOW())";
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute([$userId, $productId, $variantId, $quantity]);
            }

            return $this->getByUser($userId);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * تحديث كمية منتج
     */
    public function updateItem(int $userId, int $itemId, int $quantity): array {
        try {
            if ($quantity < 1) {
                return $this->removeItem($userId, $itemId);
            }

            // التحقق من أن العنصر يخص هذا المستخدم
            $sql = "SELECT c.*, p.stock_quantity 
                    FROM {$this->table} c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.id = ? AND c.user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$itemId, $userId]);
            $item = $stmt->fetch();

            if (!$item) {
                throw new Exception('العنصر غير موجود في السلة');
            }

            // تصحيح: stock_quantity
            if ($quantity > $item['stock_quantity']) {
                throw new Exception('الكمية المطلوبة غير متوفرة');
            }

            $updateSql = "UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$quantity, $itemId, $userId]);

            return $this->getByUser($userId);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * حذف منتج من السلة
     */
    public function removeItem(int $userId, int $itemId): array {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$itemId, $userId]);

            return $this->getByUser($userId);
        } catch (PDOException $e) {
            throw new Exception('حدث خطأ أثناء حذف العنصر');
        }
    }

    /**
     * تفريغ السلة
     */
    public function clear(int $userId): array {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            return $this->getByUser($userId);
        } catch (PDOException $e) {
            throw new Exception('حدث خطأ أثناء تفريغ السلة');
        }
    }

    /**
     * تطبيق كوبون خصم
     * تم التصحيح: أسماء الأعمدة الصحيحة
     */
    public function applyCoupon(int $userId, string $code): array {
        try {
            // تصحيح: max_uses بدلاً من usage_limit، used_count بدلاً من usage_count
            $sql = "SELECT * FROM coupons 
                    WHERE code = ? 
                    AND is_active = 1 
                    AND (expires_at IS NULL OR expires_at > NOW())
                    AND (max_uses IS NULL OR used_count < max_uses)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                throw new Exception('كود الخصم غير صالح أو منتهي الصلاحية');
            }

            $cart = $this->getByUser($userId);

            // تصحيح: min_order_amount بدلاً من min_order_value
            if ($cart['subtotal'] < ($coupon['min_order_amount'] ?? 0)) {
                throw new Exception('الحد الأدنى للطلب هو ' . $coupon['min_order_amount'] . ' ' . (defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'ر.س'));
            }

            // حساب الخصم
            if ($coupon['discount_type'] === 'percentage') {
                $discount = $cart['subtotal'] * ($coupon['discount_value'] / 100);
                if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                    $discount = $coupon['max_discount'];
                }
            } else {
                $discount = min($coupon['discount_value'], $cart['subtotal']);
            }

            $cart['discount'] = round($discount, 2);
            $cart['coupon_code'] = $code;
            $cart['total'] = round($cart['subtotal'] - $discount + $cart['tax'] + $cart['shipping'], 2);

            return $cart;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * عدد العناصر في السلة
     */
    public function getCount(int $userId): int {
        try {
            $stmt = $this->db->prepare("SELECT SUM(quantity) FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
