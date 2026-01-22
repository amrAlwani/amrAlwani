<?php
/**
 * Order Model - نموذج الطلب
 * 
 * تم التصحيح:
 * - تصحيح أسماء الأعمدة لتتوافق مع Schema
 * - إصلاح مشكلة LIMIT/OFFSET مع PDO
 * - تحسين معالجة الأخطاء
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Product.php';

class Order {
    private string $table = 'orders';
    private PDO $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * البحث عن طلب بالـ ID
     */
    public function findById($id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch();

            if ($order) {
                $order['items'] = $this->getItems($id);
                // تحويل عنوان الشحن من JSON
                if (!empty($order['shipping_address'])) {
                    $order['shipping_address'] = json_decode($order['shipping_address'], true);
                }
            }

            return $order ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * البحث عن طلب برقم الطلب
     */
    public function findByOrderNumber(string $orderNumber): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_number = ?");
            $stmt->execute([$orderNumber]);
            $order = $stmt->fetch();

            if ($order) {
                $order['items'] = $this->getItems($order['id']);
                if (!empty($order['shipping_address'])) {
                    $order['shipping_address'] = json_decode($order['shipping_address'], true);
                }
            }

            return $order ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * الحصول على عناصر الطلب
     */
    public function getItems(int $orderId): array {
        try {
            // تصحيح: استخدام أسماء الأعمدة الصحيحة من Schema
            $sql = "SELECT oi.*, p.image, p.slug
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * الحصول على طلبات المستخدم
     * تم التصحيح: استخدام bindParam بشكل صحيح
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 10): array {
        try {
            $page = max(1, (int)$page);
            $perPage = max(1, min(100, (int)$perPage));
            $offset = ($page - 1) * $perPage;

            // عد الإجمالي
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $total = (int)$countStmt->fetchColumn();

            // الاستعلام الرئيسي
            $sql = "SELECT * FROM {$this->table}
                    WHERE user_id = :userid
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll();

            // جلب العناصر لكل طلب
            foreach ($orders as &$order) {
                $order['items'] = $this->getItems($order['id']);
                if (!empty($order['shipping_address'])) {
                    $order['shipping_address'] = json_decode($order['shipping_address'], true);
                }
            }

            return [
                'orders' => $orders,
                'total' => $total
            ];

        } catch (PDOException $e) {
            return ['orders' => [], 'total' => 0];
        }
    }

    /**
     * إنشاء طلب جديد
     * تم التصحيح: استخدام أسماء الأعمدة الصحيحة من Schema
     */
    public function create(int $userId, array $data): array {
        $cartModel = new Cart();
        $productModel = new Product();

        try {
            $cart = $cartModel->getByUser($userId);
            
            if (empty($cart['items'])) {
                return ['success' => false, 'message' => 'السلة فارغة'];
            }

            // التحقق من الحد الأدنى للطلب
            $minOrderValue = defined('MIN_ORDER_VALUE') ? MIN_ORDER_VALUE : 0;
            if ($cart['subtotal'] < $minOrderValue) {
                $currencySymbol = defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'SAR';
                return ['success' => false, 'message' => "الحد الأدنى للطلب {$minOrderValue} {$currencySymbol}"];
            }

            // التحقق من المخزون
            foreach ($cart['items'] as $item) {
                if (!$productModel->checkStock($item['product_id'], $item['quantity'], $item['variant_id'])) {
                    return ['success' => false, 'message' => 'المنتج "' . $item['name'] . '" غير متوفر بالكمية المطلوبة'];
                }
            }

            $totals = $cartModel->calculateTotals($userId, $data['coupon_code'] ?? null);
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $this->db->beginTransaction();

            // تصحيح: استخدام أسماء الأعمدة الصحيحة من Schema
            // shipping_address هو JSON في Schema وليس أعمدة منفصلة
            $shippingAddress = json_encode([
                'name' => $data['shipping_name'] ?? '',
                'phone' => $data['shipping_phone'] ?? '',
                'city' => $data['shipping_city'] ?? '',
                'street' => $data['shipping_address'] ?? '',
                'notes' => $data['notes'] ?? ''
            ], JSON_UNESCAPED_UNICODE);

            $sql = "INSERT INTO {$this->table}
                    (order_number, user_id, status, payment_status, payment_method, 
                     subtotal, discount, tax, shipping_cost, total, coupon_code, shipping_address, notes)
                    VALUES (?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderNumber,
                $userId,
                $data['payment_method'] ?? 'cash',
                $totals['subtotal'],
                $totals['discount'],
                $totals['tax'],
                $totals['shipping'],
                $totals['total'],
                $data['coupon_code'] ?? null,
                $shippingAddress,
                $data['notes'] ?? null
            ]);
            
            $orderId = (int)$this->db->lastInsertId();

            // إضافة عناصر الطلب
            // تصحيح: استخدام أسماء الأعمدة الصحيحة (name بدلاً من product_name)
            $itemSql = "INSERT INTO order_items (order_id, product_id, variant_id, name, price, quantity, total, options)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $itemStmt = $this->db->prepare($itemSql);
            
            foreach ($cart['items'] as $item) {
                $options = $item['variant_name'] ? json_encode(['variant' => $item['variant_name']], JSON_UNESCAPED_UNICODE) : null;
                
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['name'],
                    $item['unit_price'],
                    $item['quantity'],
                    $item['total'],
                    $options
                ]);
                
                // تقليل المخزون
                $productModel->reduceStock($item['product_id'], $item['quantity'], $item['variant_id']);
            }

            // تحديث استخدام الكوبون
            if (!empty($data['coupon_code'])) {
                $this->db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$data['coupon_code']]);
            }

            // تفريغ السلة
            $cartModel->clear($userId);
            
            $this->db->commit();

            return ['success' => true, 'order' => $this->findById($orderId)];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'حدث خطأ أثناء إنشاء الطلب'];
        }
    }

    /**
     * إلغاء الطلب
     */
    public function cancel(int $orderId, int $userId): array {
        try {
            $order = $this->findById($orderId);

            if (!$order || $order['user_id'] != $userId) {
                return ['success' => false, 'message' => 'الطلب غير موجود'];
            }

            // تصحيح: التحقق من حالات يمكن إلغاؤها
            $cancellableStatuses = ['pending', 'processing'];
            if (!in_array($order['status'], $cancellableStatuses)) {
                return ['success' => false, 'message' => 'لا يمكن إلغاء هذا الطلب'];
            }

            $productModel = new Product();
            $this->db->beginTransaction();

            // تحديث حالة الطلب
            $this->db->prepare("UPDATE {$this->table} SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?")->execute([$orderId]);
            
            // استعادة المخزون
            foreach ($order['items'] as $item) {
                $productModel->restoreStock($item['product_id'], $item['quantity'], $item['variant_id']);
            }

            $this->db->commit();

            return ['success' => true, 'order' => $this->findById($orderId)];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'حدث خطأ أثناء إلغاء الطلب'];
        }
    }

    /**
     * تحديث حالة الطلب (للمدير)
     */
    public function updateStatus(int $orderId, string $status): bool {
        try {
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
            if (!in_array($status, $validStatuses)) {
                return false;
            }

            $sql = "UPDATE {$this->table} SET status = ?";
            $params = [$status];

            // إضافة التوقيت المناسب
            if ($status === 'shipped') {
                $sql .= ", shipped_at = NOW()";
            } elseif ($status === 'delivered') {
                $sql .= ", delivered_at = NOW()";
            } elseif ($status === 'cancelled') {
                $sql .= ", cancelled_at = NOW()";
            }

            $sql .= ", updated_at = NOW() WHERE id = ?";
            $params[] = $orderId;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            return false;
        }
    }
}
