<?php
/**
 * Cart Model - نموذج السلة
 * 
 * تم التصحيح:
 * - تصحيح اسم الجدول من 'cart' إلى 'cart_items'
 * - تصحيح أسماء الأعمدة لتتوافق مع الـ SQL Schema
 * - إضافة معالجة أفضل للأخطاء
 */

require_once __DIR__ . '/../config/database.php';

class Cart {
    // تصحيح: اسم الجدول يجب أن يكون cart_items وفقاً للـ Schema
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
                // تصحيح: استخدام sale_price بدلاً من discount_price
                $price = $item['variant_price'] ?? ($item['sale_price'] ?? $item['price']);
                $item['unit_price'] = (float)$price;
                $item['total'] = (float)$price * (int)$item['quantity'];
                $subtotal += $item['total'];
            }

            return [
                'items' => $items,
                'subtotal' => round($subtotal, 2),
                'item_count' => count($items)
            ];
        } catch (PDOException $e) {
            // في حالة الخطأ، إرجاع سلة فارغة
            return [
                'items' => [],
                'subtotal' => 0,
                'item_count' => 0
            ];
        }
    }

    /**
     * إضافة عنصر للسلة
     */
    public function addItem(int $userId, int $productId, int $quantity = 1, ?int $variantId = null): array {
        try {
            // التحقق من وجود العنصر مسبقاً
            $sql = "SELECT id, quantity FROM {$this->table}
                    WHERE user_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $productId, $variantId, $variantId]);
            $existing = $stmt->fetch();

            if ($existing) {
                // تحديث الكمية
                $newQuantity = (int)$existing['quantity'] + $quantity;
                $updateStmt = $this->db->prepare("UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$newQuantity, $existing['id']]);
            } else {
                // إضافة عنصر جديد
                $sql = "INSERT INTO {$this->table} (user_id, product_id, variant_id, quantity, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $productId, $variantId, $quantity]);
            }

            return $this->getByUser($userId);
        } catch (PDOException $e) {
            return $this->getByUser($userId);
        }
    }

    /**
     * تحديث كمية العنصر
     */
    public function updateQuantity(int $userId, int $itemId, int $quantity): array {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $itemId);
        }

        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $itemId, $userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ وإرجاع السلة الحالية
        }

        return $this->getByUser($userId);
    }

    /**
     * إزالة عنصر من السلة
     */
    public function removeItem(int $userId, int $itemId): array {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ? AND user_id = ?");
            $stmt->execute([$itemId, $userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }

        return $this->getByUser($userId);
    }

    /**
     * تفريغ السلة
     */
    public function clear(int $userId): array {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }

        return $this->getByUser($userId);
    }

    /**
     * حساب الإجماليات
     */
    public function calculateTotals(int $userId, ?string $couponCode = null): array {
        $cart = $this->getByUser($userId);
        $subtotal = $cart['subtotal'];
        $discount = 0;
        $coupon = null;

        // تطبيق الكوبون
        if ($couponCode) {
            $coupon = $this->validateCoupon($couponCode, $subtotal);
            if ($coupon) {
                $discount = $this->calculateDiscount($coupon, $subtotal);
            }
        }

        // حساب الضريبة
        $taxableAmount = $subtotal - $discount;
        // تصحيح: استخدام الثابت بدلاً من المتغير
        $taxRate = defined('TAX_RATE') ? TAX_RATE : 0.15;
        $tax = $taxableAmount * $taxRate;

        // الشحن
        $freeShippingThreshold = defined('FREE_SHIPPING_THRESHOLD') ? FREE_SHIPPING_THRESHOLD : 500;
        $shippingCost = defined('SHIPPING_COST') ? SHIPPING_COST : 25;
        $shipping = ($subtotal - $discount) >= $freeShippingThreshold ? 0 : $shippingCost;

        $total = $subtotal - $discount + $tax + $shipping;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2),
            'coupon' => $coupon ? [
                'code' => $coupon['code'],
                'description' => $coupon['type'] === 'percentage' 
                    ? "خصم {$coupon['value']}%" 
                    : "خصم " . $coupon['value'] . " " . (defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'SAR')
            ] : null,
            'item_count' => $cart['item_count']
        ];
    }

    /**
     * التحقق من صلاحية الكوبون
     */
    private function validateCoupon(string $code, float $orderValue): ?array {
        try {
            // تصحيح: تعديل أسماء الأعمدة لتتوافق مع Schema
            // usage_limit -> max_uses, usage_count -> used_count
            $stmt = $this->db->prepare("
                SELECT * FROM coupons
                WHERE code = ?
                AND is_active = 1
                AND (start_date IS NULL OR start_date <= CURDATE())
                AND (end_date IS NULL OR end_date >= CURDATE())
                AND (max_uses IS NULL OR used_count < max_uses)
            ");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                return null;
            }

            // تصحيح: min_order_value -> min_order_amount
            if (isset($coupon['min_order_amount']) && $orderValue < $coupon['min_order_amount']) {
                return null;
            }

            return $coupon;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * حساب قيمة الخصم
     */
    private function calculateDiscount(array $coupon, float $subtotal): float {
        if ($coupon['type'] === 'percentage') {
            $discount = $subtotal * ((float)$coupon['value'] / 100);
            // تصحيح: max_discount بدلاً من max_discount_amount
            if (!empty($coupon['max_discount']) && $discount > $coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['value'];
        }

        return min($discount, $subtotal);
    }
}
