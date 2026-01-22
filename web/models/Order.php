<?php
/**
 * Order Model - نموذج الطلب المصحح
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
     * الحصول على طلبات المستخدم
     * تم التصحيح: LIMIT/OFFSET مع PDO
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 10): array {
        try {
            // عدد الإجمالي
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $total = (int)$countStmt->fetchColumn();

            $offset = ($page - 1) * $perPage;

            // تصحيح: استخدام bindValue مع PDO::PARAM_INT
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll();

            foreach ($orders as &$order) {
                if (!empty($order['shipping_address'])) {
                    $order['shipping_address'] = json_decode($order['shipping_address'], true);
                }
                $order['items_count'] = $this->getItemsCount($order['id']);
            }

            return [
                'orders' => $orders,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int)ceil($total / $perPage)
            ];

        } catch (PDOException $e) {
            error_log("Order getByUser error: " . $e->getMessage());
            return ['orders' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        }
    }

    /**
     * إنشاء طلب جديد
     * تم التصحيح: shipping_address كـ JSON
     */
    public function create(int $userId, array $shippingAddress, ?string $couponCode = null, string $paymentMethod = 'cod'): ?array {
        try {
            $this->db->beginTransaction();

            $cartModel = new Cart();
            $cart = $cartModel->getByUser($userId);

            if (empty($cart['items'])) {
                throw new Exception('السلة فارغة');
            }

            // التحقق من توفر جميع المنتجات
            foreach ($cart['items'] as $item) {
                if (!$item['is_available']) {
                    throw new Exception("المنتج '{$item['name']}' غير متوفر بالكمية المطلوبة");
                }
            }

            // توليد رقم الطلب
            $orderNumber = 'SC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // تطبيق الكوبون إن وجد
            $discount = 0;
            if ($couponCode) {
                try {
                    $cartWithCoupon = $cartModel->applyCoupon($userId, $couponCode);
                    $discount = $cartWithCoupon['discount'] ?? 0;
                    $cart['total'] = $cartWithCoupon['total'];
                } catch (Exception $e) {
                    // تجاهل خطأ الكوبون
                }
            }

            // تصحيح: تخزين العنوان كـ JSON
            $shippingAddressJson = json_encode($shippingAddress, JSON_UNESCAPED_UNICODE);

            // إنشاء الطلب
            $sql = "INSERT INTO {$this->table} 
                    (user_id, order_number, shipping_address, subtotal, tax, shipping, discount, total, 
                     payment_method, status, coupon_code, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $orderNumber,
                $shippingAddressJson,
                $cart['subtotal'],
                $cart['tax'],
                $cart['shipping'],
                $discount,
                $cart['total'],
                $paymentMethod,
                $couponCode
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // إضافة عناصر الطلب
            $productModel = new Product();
            foreach ($cart['items'] as $item) {
                $itemSql = "INSERT INTO order_items 
                            (order_id, product_id, variant_id, name, price, quantity, total, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

                $itemStmt = $this->db->prepare($itemSql);
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['name'],
                    $item['final_price'],
                    $item['quantity'],
                    $item['item_total']
                ]);

                // تحديث المخزون
                $productModel->updateStock($item['product_id'], $item['quantity']);
            }

            // تفريغ السلة
            $cartModel->clear($userId);

            // تحديث استخدام الكوبون
            if ($couponCode) {
                $this->incrementCouponUsage($couponCode);
            }

            $this->db->commit();

            return $this->findById($orderId);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * الحصول على عناصر الطلب
     */
    public function getItems(int $orderId): array {
        try {
            // تصحيح: name بدلاً من product_name
            $sql = "SELECT oi.*, p.image, p.slug
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                    ORDER BY oi.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * عدد عناصر الطلب
     */
    private function getItemsCount(int $orderId): int {
        try {
            $stmt = $this->db->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateStatus(int $orderId, string $status): bool {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * إلغاء الطلب
     */
    public function cancel(int $orderId, int $userId): bool {
        try {
            $order = $this->findById($orderId);

            if (!$order || $order['user_id'] != $userId) {
                return false;
            }

            if (!in_array($order['status'], ['pending', 'processing'])) {
                return false;
            }

            // إعادة المخزون
            $productModel = new Product();
            foreach ($order['items'] as $item) {
                $stmt = $this->db->prepare(
                    "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?"
                );
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            return $this->updateStatus($orderId, 'cancelled');

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * تتبع الطلب
     */
    public function track(string $orderNumber): ?array {
        $order = $this->findByOrderNumber($orderNumber);

        if (!$order) {
            return null;
        }

        $statuses = [
            'pending' => ['label' => 'قيد الانتظار', 'icon' => 'clock', 'order' => 1],
            'processing' => ['label' => 'جاري التجهيز', 'icon' => 'package', 'order' => 2],
            'shipped' => ['label' => 'تم الشحن', 'icon' => 'truck', 'order' => 3],
            'delivered' => ['label' => 'تم التوصيل', 'icon' => 'check-circle', 'order' => 4],
            'cancelled' => ['label' => 'ملغي', 'icon' => 'x-circle', 'order' => 0],
        ];

        $currentStatus = $statuses[$order['status']] ?? $statuses['pending'];

        return [
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'status_label' => $currentStatus['label'],
            'total' => $order['total'],
            'items_count' => count($order['items']),
            'created_at' => $order['created_at'],
            'timeline' => $this->getTimeline($order, $statuses)
        ];
    }

    /**
     * الحصول على الجدول الزمني للطلب
     */
    private function getTimeline(array $order, array $statuses): array {
        $timeline = [];
        $currentOrder = $statuses[$order['status']]['order'] ?? 0;

        foreach ($statuses as $key => $status) {
            if ($key === 'cancelled' && $order['status'] !== 'cancelled') {
                continue;
            }

            $timeline[] = [
                'status' => $key,
                'label' => $status['label'],
                'completed' => $status['order'] <= $currentOrder && $order['status'] !== 'cancelled',
                'current' => $key === $order['status']
            ];
        }

        return $timeline;
    }

    /**
     * زيادة استخدام الكوبون
     */
    private function incrementCouponUsage(string $code): void {
        try {
            // تصحيح: used_count بدلاً من usage_count
            $stmt = $this->db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
            $stmt->execute([$code]);
        } catch (PDOException $e) {
            // تجاهل الخطأ
        }
    }

    /**
     * عدد الطلبات بشروط معينة
     */
    public function count(array $conditions = []): int {
        try {
            $where = [];
            $params = [];

            if (isset($conditions['user_id'])) {
                $where[] = "user_id = ?";
                $params[] = $conditions['user_id'];
            }

            if (isset($conditions['status'])) {
                $where[] = "status = ?";
                $params[] = $conditions['status'];
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
     * الحصول على جميع الطلبات (للمدير)
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['status'])) {
                $where[] = "o.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(o.order_number LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // عدد الإجمالي
            $countSql = "SELECT COUNT(*) FROM {$this->table} o 
                         LEFT JOIN users u ON o.user_id = u.id {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $offset = ($page - 1) * $perPage;

            $sql = "SELECT o.*, u.name as user_name, u.email as user_email
                    FROM {$this->table} o
                    LEFT JOIN users u ON o.user_id = u.id
                    {$whereClause}
                    ORDER BY o.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll();

            foreach ($orders as &$order) {
                if (!empty($order['shipping_address'])) {
                    $order['shipping_address'] = json_decode($order['shipping_address'], true);
                }
                $order['items_count'] = $this->getItemsCount($order['id']);
            }

            return [
                'orders' => $orders,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int)ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            error_log("Order getAll error: " . $e->getMessage());
            return ['orders' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1];
        }
    }

    /**
     * Paginate (للتوافق مع AdminController)
     */
    public function paginate(int $page = 1, int $perPage = 20, array $conditions = [], string $orderBy = 'created_at DESC'): array {
        $result = $this->getAll($page, $perPage, ['status' => $conditions['status'] ?? null]);
        return [
            'data' => $result['orders'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'last_page' => $result['last_page']
        ];
    }
}
