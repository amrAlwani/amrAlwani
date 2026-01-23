<?php
/**
 * AdminController - النسخة الكاملة الشاملة والمختبرة
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
    private Coupon $couponModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->categoryModel = new Category();
        $this->couponModel = new Coupon();
    }

    // --- 1. الرئيسية (Dashboard) ---
 public function dashboard(): void {
        $this->requireAdmin();
        
        // 1. جلب الإحصائيات (Stats)
        $stats = [
            'total_revenue'  => method_exists($this->orderModel, 'getTotalRevenue') ? $this->orderModel->getTotalRevenue() : 0,
            'total_orders'   => method_exists($this->orderModel, 'count') ? $this->orderModel->count() : 0,
            'total_products' => method_exists($this->productModel, 'count') ? $this->productModel->count() : 0,
            'total_users'    => method_exists($this->userModel, 'count') ? $this->userModel->count() : 0
        ];

        // 2. جلب آخر الطلبات (Recent Orders)
        $recentOrders = [];
        if (method_exists($this->orderModel, 'getRecent')) {
            $recentOrders = $this->orderModel->getRecent(5);
        }

        // 3. جلب آخر المستخدمين (Recent Users) - تأكد من وجود دالة getRecent في User.php
        $recentUsers = [];
        if (method_exists($this->userModel, 'getRecent')) {
            $recentUsers = $this->userModel->getRecent(5);
        } else {
            // محاولة جلب المستخدمين يدوياً إذا لم تكن الدالة موجودة في الموديل
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $recentUsers = [];
            }
        }

        // إرسال المتغيرات بشكل منفصل كما يتوقعها ملف dashboard.php
        $this->view('admin/dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers'  => $recentUsers
        ], 'admin');
    }

    // --- 2. إدارة المنتجات (مطابقة لقاعدة بياناتك تماماً) ---
    public function products(): void {
        $this->requireAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->productModel->getAll($page, 10, [
            'search' => $params['search'] ?? '',
            'category_id' => $params['category'] ?? ''
        ]);
        
        $this->view('admin/products', [
            'products'   => $result['products'],
            'total'      => $result['total'],
            'page'       => $page,
            'lastPage'   => $result['last_page'],
            'categories' => $this->categoryModel->getAll()
        ], 'admin');
    }

    public function createProduct(): void {
        $this->requireAdmin();
        $this->view('admin/product-form', [
            'product' => null,
            'categories' => $this->categoryModel->getAll()
        ], 'admin');
    }

    public function storeProduct(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareProductData($_POST);
            if ($this->productModel->create($data)) {
                $this->flash('success', 'تم إضافة المنتج بنجاح');
                $this->redirect('admin/products');
            } else {
                $this->flash('error', 'فشل حفظ المنتج. تأكد من إدخال البيانات بشكل صحيح');
                $this->redirect('admin/products/create');
            }
        }
    }

    public function editProduct(string $id): void {
        $this->requireAdmin();
        $product = $this->productModel->findById((int)$id);
        $this->view('admin/product-form', [
            'product' => $product,
            'categories' => $this->categoryModel->getAll()
        ], 'admin');
    }

    public function updateProduct(string $id): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareProductData($_POST);
            if ($this->productModel->update((int)$id, $data)) {
                $this->flash('success', 'تم تحديث المنتج بنجاح');
                $this->redirect('admin/products');
            } else {
                $this->flash('error', 'فشل تحديث المنتج');
                $this->redirect("admin/products/$id/edit");
            }
        }
    }

    public function deleteProduct(string $id): void {
        $this->requireAdmin();
        if ($this->productModel->delete((int)$id)) {
            $this->flash('success', 'تم حذف المنتج');
        }
        $this->redirect('admin/products');
    }

    // --- 3. إدارة المستخدمين ---
    public function users(): void {
        $this->requireAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->userModel->paginate($page, 15, $params);
        
        $this->view('admin/users', [
            'users'    => $result['data'],
            'total'    => $result['total'],
            'page'     => $page,
            'lastPage' => $result['last_page']
        ], 'admin');
    }

    // --- 4. إدارة الطلبات ---
    public function orders(): void {
        $this->requireAdmin();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->orderModel->paginate($page, 20, $params);
        
        $this->view('admin/orders', [
            'orders'   => $result['data'],
            'page'     => $page,
            'lastPage' => $result['last_page']
        ], 'admin');
    }

    // --- 5. بقية الصفحات ---
    public function categories(): void {
        $this->requireAdmin();
        $this->view('admin/categories', ['categories' => $this->categoryModel->getAll()], 'admin');
    }

    public function coupons(): void {
        $this->requireAdmin();
        $this->view('admin/coupons', ['coupons' => $this->couponModel->getAll()], 'admin');
    }

    public function settings(): void {
        $this->requireAdmin();
        $this->view('admin/settings', ['settings' => []], 'admin');
    }

    // --- 6. الدوال المساعدة ---
    private function prepareProductData(array $post): array {
        return [
            'name'           => $post['name'] ?? '',
            'slug'           => $this->generateSlug($post['name'] ?? ''),
            'description'    => $post['description'] ?? '',
            'price'          => (float)($post['price'] ?? 0),
            'discount_price' => !empty($post['sale_price']) ? (float)$post['sale_price'] : null,
            'stock'          => (int)($post['stock_quantity'] ?? 0),
            'category_id'    => !empty($post['category_id']) ? (int)$post['category_id'] : null,
            'image'          => $post['image'] ?? '',
            'is_active'      => isset($post['is_active']) ? 1 : 0,
            'is_featured'    => isset($post['is_featured']) ? 1 : 0
        ];
    }

    private function generateSlug(string $text): string {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $text);
        return mb_strtolower(trim($slug, '-'), 'UTF-8') ?: 'product-' . time();
    }
}