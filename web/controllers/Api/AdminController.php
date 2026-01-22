<?php
/**
 * API AdminController
 */

namespace Api;

require_once BASEPATH . '/models/User.php';
require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Order.php';

class AdminController extends \Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * التحقق من صلاحية المدير
     */
    private function requireApiAdmin(): array
    {
        $user = \Auth::requireAuth();
        
        if ($user['role'] !== 'admin') {
            \Response::forbidden('غير مصرح لك');
        }
        
        return $user;
    }
    
    /**
     * إحصائيات لوحة التحكم
     */
    public function dashboard(): void
    {
        $this->requireApiAdmin();
        
        $db = db();
        
        $stats = [];
        
        // إجمالي المستخدمين
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = (int)$stmt->fetchColumn();
        
        // إجمالي المنتجات
        $stmt = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
        $stats['total_products'] = (int)$stmt->fetchColumn();
        
        // إجمالي الطلبات
        $stmt = $db->query("SELECT COUNT(*) FROM orders");
        $stats['total_orders'] = (int)$stmt->fetchColumn();
        
        // الطلبات المعلقة
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = (int)$stmt->fetchColumn();
        
        // إجمالي الإيرادات
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'");
        $stats['total_revenue'] = (float)$stmt->fetchColumn();
        
        // إيرادات اليوم
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()");
        $stats['today_revenue'] = (float)$stmt->fetchColumn();
        
        // المبيعات الأخيرة (7 أيام)
        $stmt = $db->query("
            SELECT DATE(created_at) as date, SUM(total) as total, COUNT(*) as count
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stats['recent_sales'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        \Response::success($stats);
    }
    
    /**
     * قائمة الطلبات
     */
    public function orders(): void
    {
        $this->requireApiAdmin();
        
        $orderModel = new \Order();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $status = $params['status'] ?? null;
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $result = $orderModel->paginate($page, 20, $conditions, 'created_at DESC');
        
        \Response::paginate($result['data'], $result['total'], $page, 20);
    }
    
    /**
     * تحديث حالة طلب
     */
    public function updateOrderStatus(string $id): void
    {
        $this->requireApiAdmin();
        
        $data = $this->getJsonInput();
        $status = $data['status'] ?? '';
        
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            \Response::error('حالة غير صالحة', [], 400);
        }
        
        $orderModel = new \Order();
        $orderModel->updateStatus((int)$id, $status);
        
        \Response::success(null, 'تم تحديث حالة الطلب');
    }
    
    /**
     * قائمة المنتجات
     */
    public function products(): void
    {
        $this->requireApiAdmin();
        
        $productModel = new \Product();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $productModel->getAll($page, 20, [
            'search' => $params['search'] ?? null
        ]);
        
        \Response::paginate($result['products'], $result['total'], $page, 20);
    }
    
    /**
     * إضافة منتج
     */
    public function storeProduct(): void
    {
        $this->requireApiAdmin();
        
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('name', 'اسم المنتج مطلوب')
                  ->required('price', 'السعر مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $productModel = new \Product();
        
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $data['name']);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = mb_strtolower(trim($slug, '-')) ?: uniqid();
        
        $productId = $productModel->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'price' => (float)$data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'category_id' => $data['category_id'] ?? null,
            'image' => $data['image'] ?? null,
            'is_active' => 1
        ]);
        
        $product = $productModel->findById($productId);
        
        \Response::created($product, 'تم إضافة المنتج');
    }
    
    /**
     * تحديث منتج
     */
    public function updateProduct(string $id): void
    {
        $this->requireApiAdmin();
        
        $data = $this->getJsonInput();
        $productModel = new \Product();
        
        $product = $productModel->findById((int)$id);
        
        if (!$product) {
            \Response::notFound('المنتج غير موجود');
        }
        
        $productModel->update((int)$id, array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => isset($data['price']) ? (float)$data['price'] : null,
            'sale_price' => $data['sale_price'] ?? null,
            'stock_quantity' => isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : null,
            'category_id' => $data['category_id'] ?? null,
            'image' => $data['image'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : null
        ], fn($v) => $v !== null));
        
        $updatedProduct = $productModel->findById((int)$id);
        
        \Response::success($updatedProduct, 'تم تحديث المنتج');
    }
    
    /**
     * حذف منتج
     */
    public function deleteProduct(string $id): void
    {
        $this->requireApiAdmin();
        
        $productModel = new \Product();
        $productModel->delete((int)$id);
        
        \Response::success(null, 'تم حذف المنتج');
    }
    
    /**
     * قائمة المستخدمين
     */
    public function users(): void
    {
        $this->requireApiAdmin();
        
        $userModel = new \User();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $userModel->paginate($page, 20);
        
        \Response::paginate($result['data'], $result['total'], $page, 20);
    }
    
    /**
     * تفعيل/تعطيل مستخدم
     */
    public function toggleUser(string $id): void
    {
        $user = $this->requireApiAdmin();
        
        if ((int)$id === $user['id']) {
            \Response::error('لا يمكنك تعطيل حسابك', [], 400);
        }
        
        $userModel = new \User();
        $targetUser = $userModel->findById((int)$id);
        
        if (!$targetUser) {
            \Response::notFound('المستخدم غير موجود');
        }
        
        $newStatus = $targetUser['is_active'] ? 0 : 1;
        $userModel->update((int)$id, ['is_active' => $newStatus]);
        
        $message = $newStatus ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب';
        \Response::success(null, $message);
    }
    
    /**
     * الإحصائيات
     */
    public function stats(): void
    {
        $this->dashboard();
    }
}
