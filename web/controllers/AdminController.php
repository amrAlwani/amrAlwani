<?php
/**
 * AdminController - لوحة تحكم المدير
 */

require_once BASEPATH . '/models/User.php';
require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Order.php';
require_once BASEPATH . '/models/Category.php';
require_once BASEPATH . '/models/Coupon.php';

class AdminController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Order $orderModel;
    private Category $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->categoryModel = new Category();
    }
    
    /**
     * لوحة التحكم الرئيسية
     */
    public function dashboard(): void
    {
        $this->requireAdmin();
        
        $db = db();
        
        // الإحصائيات
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_products' => $this->productModel->count(['is_active' => 1]),
            'total_orders' => $this->orderModel->count([]),
            'pending_orders' => $this->orderModel->count(['status' => 'pending']),
        ];
        
        // إجمالي الإيرادات
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE payment_status = 'paid'");
        $stats['total_revenue'] = $stmt->fetch()['revenue'];
        
        // آخر الطلبات
        $stmt = $db->query("SELECT o.*, u.name as user_name FROM orders o 
                            LEFT JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC LIMIT 10");
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // آخر المستخدمين
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('admin/dashboard', [
            'title' => 'لوحة التحكم',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers' => $recentUsers
        ], 'admin');
    }
    
    /**
     * إدارة الطلبات
     */
    public function orders(): void
    {
        $this->requireAdmin();
        
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $status = $params['status'] ?? null;
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $result = $this->orderModel->paginate($page, 20, $conditions, 'created_at DESC');
        
        // إضافة اسم المستخدم لكل طلب
        $db = db();
        foreach ($result['data'] as &$order) {
            $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$order['user_id']]);
            $order['user_name'] = $stmt->fetchColumn() ?: 'مجهول';
        }
        
        $this->view('admin/orders', [
            'title' => 'إدارة الطلبات',
            'orders' => $result['data'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => $result['last_page'],
            'currentStatus' => $status
        ], 'admin');
    }
    
    /**
     * تفاصيل طلب
     */
    public function orderDetails(string $id): void
    {
        $this->requireAdmin();
        
        $order = $this->orderModel->findById((int)$id);
        
        if (!$order) {
            $this->flash('error', 'الطلب غير موجود');
            $this->redirect('admin/orders');
        }
        
        $this->view('admin/order-details', [
            'title' => 'تفاصيل الطلب #' . $order['order_number'],
            'order' => $order
        ], 'admin');
    }
    
    /**
     * تحديث حالة الطلب
     */
    public function updateOrderStatus(string $id): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        $status = $data['status'] ?? '';
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $this->flash('error', 'حالة غير صالحة');
            $this->redirect('admin/orders/' . $id);
        }
        
        $this->orderModel->updateStatus((int)$id, $status);
        $this->flash('success', 'تم تحديث حالة الطلب');
        $this->redirect('admin/orders/' . $id);
    }
    
    /**
     * إدارة المنتجات
     */
    public function products(): void
    {
        $this->requireAdmin();
        
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $search = $params['search'] ?? '';
        $categoryId = $params['category'] ?? null;
        
        $result = $this->productModel->getAll($page, 20, [
            'search' => $search,
            'category_id' => $categoryId
        ]);
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('admin/products', [
            'title' => 'إدارة المنتجات',
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => ceil($result['total'] / 20),
            'categories' => $categories,
            'search' => $search,
            'categoryId' => $categoryId
        ], 'admin');
    }
    
    /**
     * نموذج إضافة منتج
     */
    public function createProduct(): void
    {
        $this->requireAdmin();
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('admin/product-form', [
            'title' => 'إضافة منتج',
            'product' => null,
            'categories' => $categories
        ], 'admin');
    }
    
    /**
     * حفظ منتج جديد
     */
    public function storeProduct(): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج');
            $this->redirect('admin/products/create');
        }
        
        $validator = new Validator($data);
        $validator->required('name', 'اسم المنتج مطلوب')
                  ->required('price', 'السعر مطلوب')
                  ->numeric('price', 'السعر يجب أن يكون رقماً');
        
        if (!$validator->passes()) {
            $_SESSION['old'] = $data;
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('admin/products/create');
        }
        
        // إنشاء الـ slug
        $slug = $this->createSlug($data['name']);
        
        $productId = $this->productModel->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'price' => (float)$data['price'],
            'sale_price' => !empty($data['sale_price']) ? (float)$data['sale_price'] : null,
            'cost_price' => !empty($data['cost_price']) ? (float)$data['cost_price'] : null,
            'sku' => $data['sku'] ?? null,
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'image' => $data['image'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'is_featured' => isset($data['is_featured']) ? 1 : 0
        ]);
        
        if ($productId) {
            $this->flash('success', 'تم إضافة المنتج بنجاح');
            $this->redirect('admin/products');
        } else {
            $this->flash('error', 'حدث خطأ أثناء إضافة المنتج');
            $this->redirect('admin/products/create');
        }
    }
    
    /**
     * نموذج تعديل منتج
     */
    public function editProduct(string $id): void
    {
        $this->requireAdmin();
        
        $product = $this->productModel->findById((int)$id);
        
        if (!$product) {
            $this->flash('error', 'المنتج غير موجود');
            $this->redirect('admin/products');
        }
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('admin/product-form', [
            'title' => 'تعديل المنتج',
            'product' => $product,
            'categories' => $categories
        ], 'admin');
    }
    
    /**
     * تحديث منتج
     */
    public function updateProduct(string $id): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        $product = $this->productModel->findById((int)$id);
        
        if (!$product) {
            $this->flash('error', 'المنتج غير موجود');
            $this->redirect('admin/products');
        }
        
        $validator = new Validator($data);
        $validator->required('name', 'اسم المنتج مطلوب')
                  ->required('price', 'السعر مطلوب');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('admin/products/' . $id . '/edit');
        }
        
        $this->productModel->update((int)$id, [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'price' => (float)$data['price'],
            'sale_price' => !empty($data['sale_price']) ? (float)$data['sale_price'] : null,
            'cost_price' => !empty($data['cost_price']) ? (float)$data['cost_price'] : null,
            'sku' => $data['sku'] ?? null,
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'image' => $data['image'] ?? $product['image'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'is_featured' => isset($data['is_featured']) ? 1 : 0
        ]);
        
        $this->flash('success', 'تم تحديث المنتج');
        $this->redirect('admin/products');
    }
    
    /**
     * حذف منتج
     */
    public function deleteProduct(string $id): void
    {
        $this->requireAdmin();
        
        $this->productModel->delete((int)$id);
        $this->flash('success', 'تم حذف المنتج');
        $this->redirect('admin/products');
    }
    
    /**
     * إدارة التصنيفات
     */
    public function categories(): void
    {
        $this->requireAdmin();
        
        $categories = $this->categoryModel->getTree();
        
        $this->view('admin/categories', [
            'title' => 'إدارة التصنيفات',
            'categories' => $categories
        ], 'admin');
    }
    
    /**
     * إضافة تصنيف
     */
    public function storeCategory(): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        $validator = new Validator($data);
        $validator->required('name', 'اسم التصنيف مطلوب');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('admin/categories');
        }
        
        $slug = $this->createSlug($data['name']);
        
        $this->categoryModel->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'is_active' => 1
        ]);
        
        $this->flash('success', 'تم إضافة التصنيف');
        $this->redirect('admin/categories');
    }
    
    /**
     * إدارة المستخدمين
     */
    public function users(): void
    {
        $this->requireAdmin();
        
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $search = $params['search'] ?? '';
        $role = $params['role'] ?? null;
        
        $conditions = [];
        if ($role) {
            $conditions['role'] = $role;
        }
        
        $result = $this->userModel->paginate($page, 20, $conditions);
        
        $this->view('admin/users', [
            'title' => 'إدارة المستخدمين',
            'users' => $result['data'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => $result['last_page'],
            'search' => $search,
            'role' => $role
        ], 'admin');
    }
    
    /**
     * تفعيل/تعطيل مستخدم
     */
    public function toggleUser(string $id): void
    {
        $this->requireAdmin();
        
        $user = $this->userModel->findById((int)$id);
        
        if (!$user) {
            $this->flash('error', 'المستخدم غير موجود');
            $this->redirect('admin/users');
        }
        
        // لا يمكن تعطيل نفسك
        if ($user['id'] == $this->user['id']) {
            $this->flash('error', 'لا يمكنك تعطيل حسابك');
            $this->redirect('admin/users');
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update((int)$id, ['is_active' => $newStatus]);
        
        $message = $newStatus ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب';
        $this->flash('success', $message);
        $this->redirect('admin/users');
    }
    
    /**
     * إدارة الكوبونات
     */
    public function coupons(): void
    {
        $this->requireAdmin();
        
        $couponModel = new Coupon();
        $coupons = $couponModel->all([], 'created_at DESC');
        
        $this->view('admin/coupons', [
            'title' => 'إدارة الكوبونات',
            'coupons' => $coupons
        ], 'admin');
    }
    
    /**
     * إضافة كوبون
     */
    public function storeCoupon(): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        $validator = new Validator($data);
        $validator->required('code', 'كود الكوبون مطلوب')
                  ->required('value', 'قيمة الخصم مطلوبة');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('admin/coupons');
        }
        
        $couponModel = new Coupon();
        $couponModel->create([
            'code' => strtoupper($data['code']),
            'type' => $data['type'] ?? 'fixed',
            'value' => (float)$data['value'],
            'min_order_amount' => !empty($data['min_order_amount']) ? (float)$data['min_order_amount'] : null,
            'max_discount' => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'max_uses' => !empty($data['max_uses']) ? (int)$data['max_uses'] : null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_active' => 1
        ]);
        
        $this->flash('success', 'تم إضافة الكوبون');
        $this->redirect('admin/coupons');
    }
    
    /**
     * الإعدادات
     */
    public function settings(): void
    {
        $this->requireAdmin();
        
        $db = db();
        $stmt = $db->query("SELECT * FROM settings");
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        $this->view('admin/settings', [
            'title' => 'الإعدادات',
            'settings' => $settings
        ], 'admin');
    }
    
    /**
     * تحديث الإعدادات
     */
    public function updateSettings(): void
    {
        $this->requireAdmin();
        $data = $this->getPostData();
        
        $db = db();
        
        unset($data['_csrf_token']);
        
        foreach ($data as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE `value` = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $this->flash('success', 'تم حفظ الإعدادات');
        $this->redirect('admin/settings');
    }
    
    /**
     * إنشاء slug من النص
     */
    private function createSlug(string $text): string
    {
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = mb_strtolower($slug);
        
        return $slug ?: uniqid();
    }
}
