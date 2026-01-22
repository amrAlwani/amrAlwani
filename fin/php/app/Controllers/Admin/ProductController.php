<?php
/**
 * Admin ProductController - إدارة المنتجات
 */

namespace App\Controllers\Admin;

require_once __DIR__ . '/../BaseController.php';
require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Category.php';
require_once BASEPATH . '/utils/FileUpload.php';

class ProductController extends \App\Controllers\BaseController {

    private \Product $productModel;
    private \Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = new \Product();
        $this->categoryModel = new \Category();
    }

    /**
     * عرض قائمة المنتجات
     */
    public function index(): void {
        $admin = $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category'] ?? '';

        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($categoryId) $filters['category_id'] = (int)$categoryId;

        $result = $this->productModel->getAll($page, $perPage, $filters);
        $categories = $this->categoryModel->getAll();

        $this->view('admin/products/index', [
            'title' => 'إدارة المنتجات',
            'user' => $admin,
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($result['total'] / $perPage),
            'categories' => $categories,
            'search' => $search,
            'categoryId' => $categoryId,
        ], 'admin');
    }

    /**
     * عرض صفحة إضافة منتج
     */
    public function create(): void {
        $admin = $this->requireAdmin();

        $categories = $this->categoryModel->getAll();

        $this->view('admin/products/create', [
            'title' => 'إضافة منتج جديد',
            'user' => $admin,
            'categories' => $categories,
            'csrf_token' => $this->getCsrfToken(),
        ], 'admin');
    }

    /**
     * حفظ منتج جديد
     */
    public function store(): void {
        $admin = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/admin/products.php?action=create');
        }

        // جمع البيانات
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => $this->generateSlug($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'sku' => trim($_POST['sku'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        // التحقق من المدخلات
        if (empty($data['name']) || $data['price'] <= 0) {
            $this->setFlash('error', 'الاسم والسعر مطلوبان');
            $this->redirect('/admin/products.php?action=create');
        }

        // رفع الصور
        $images = [];
        if (!empty($_FILES['images']['name'][0])) {
            $uploader = new \FileUpload();
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $result = $uploader->upload([
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    ], 'products');
                    if ($result['success']) {
                        $images[] = $result['path'];
                    }
                }
            }
        }
        $data['images'] = json_encode($images);
        $data['image'] = $images[0] ?? null;

        // إنشاء المنتج
        $productId = $this->createProduct($data);

        if ($productId) {
            $this->setFlash('success', 'تم إضافة المنتج بنجاح');
            $this->redirect('/admin/products.php');
        } else {
            $this->setFlash('error', 'فشل في إضافة المنتج');
            $this->redirect('/admin/products.php?action=create');
        }
    }

    /**
     * عرض صفحة تعديل منتج
     */
    public function edit(int $id): void {
        $admin = $this->requireAdmin();

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->setFlash('error', 'المنتج غير موجود');
            $this->redirect('/admin/products.php');
        }

        $categories = $this->categoryModel->getAll();

        $this->view('admin/products/edit', [
            'title' => 'تعديل المنتج: ' . $product['name'],
            'user' => $admin,
            'product' => $product,
            'categories' => $categories,
            'csrf_token' => $this->getCsrfToken(),
        ], 'admin');
    }

    /**
     * تحديث منتج
     */
    public function update(int $id): void {
        $admin = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/admin/products.php?action=edit&id=' . $id);
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->setFlash('error', 'المنتج غير موجود');
            $this->redirect('/admin/products.php');
        }

        // جمع البيانات
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'sku' => trim($_POST['sku'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        // رفع صور جديدة
        $images = $product['images'] ?? [];
        if (!empty($_FILES['images']['name'][0])) {
            $uploader = new \FileUpload();
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $result = $uploader->upload([
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    ], 'products');
                    if ($result['success']) {
                        $images[] = $result['path'];
                    }
                }
            }
        }
        $data['images'] = json_encode($images);
        if (!empty($images)) {
            $data['image'] = $images[0];
        }

        // تحديث المنتج
        if ($this->updateProduct($id, $data)) {
            $this->setFlash('success', 'تم تحديث المنتج بنجاح');
        } else {
            $this->setFlash('error', 'فشل في تحديث المنتج');
        }
        $this->redirect('/admin/products.php');
    }

    /**
     * حذف منتج
     */
    public function delete(int $id): void {
        $admin = $this->requireAdmin();

        if ($this->deleteProduct($id)) {
            $this->setFlash('success', 'تم حذف المنتج بنجاح');
        } else {
            $this->setFlash('error', 'فشل في حذف المنتج');
        }
        $this->redirect('/admin/products.php');
    }

    /**
     * إنشاء منتج في قاعدة البيانات
     */
    private function createProduct(array $data): ?int {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO products (name, slug, description, short_description, price, sale_price, 
                    category_id, stock_quantity, sku, image, images, is_active, is_featured, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['short_description'],
                $data['price'],
                $data['sale_price'],
                $data['category_id'],
                $data['stock_quantity'],
                $data['sku'],
                $data['image'],
                $data['images'],
                $data['is_active'],
                $data['is_featured'],
            ]);

            return (int)$db->lastInsertId();
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * تحديث منتج في قاعدة البيانات
     */
    private function updateProduct(int $id, array $data): bool {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $sql = "UPDATE products SET 
                    name = ?, description = ?, short_description = ?, price = ?, sale_price = ?,
                    category_id = ?, stock_quantity = ?, sku = ?, images = ?, 
                    is_active = ?, is_featured = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['description'],
                $data['short_description'],
                $data['price'],
                $data['sale_price'],
                $data['category_id'],
                $data['stock_quantity'],
                $data['sku'],
                $data['images'],
                $data['is_active'],
                $data['is_featured'],
                $id
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * حذف منتج من قاعدة البيانات
     */
    private function deleteProduct(int $id): bool {
        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * توليد slug من الاسم
     */
    private function generateSlug(string $name): string {
        $slug = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\s-]/u', '', $name);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug . '-' . time();
    }
}
