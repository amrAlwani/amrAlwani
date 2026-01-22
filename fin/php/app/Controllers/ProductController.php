<?php
/**
 * ProductController - متحكم المنتجات
 * يتعامل مع عرض وإدارة المنتجات
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Category.php';

class ProductController extends BaseController {

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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        
        $filters = [
            'category_id' => !empty($_GET['category']) ? (int)$_GET['category'] : null,
            'search' => !empty($_GET['search']) ? trim($_GET['search']) : null,
            'min_price' => !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null,
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'sort' => $_GET['sort'] ?? 'newest',
        ];

        $result = $this->productModel->getAll($page, $perPage, $filters);
        $categories = $this->categoryModel->getAll();

        $this->view('products/index', [
            'title' => 'المنتجات',
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($result['total'] / $perPage),
            'categories' => $categories,
            'filters' => $filters,
            'user' => $this->currentUser()
        ]);
    }

    /**
     * عرض تفاصيل منتج
     */
    public function show(int $id): void {
        $product = $this->productModel->findById($id);

        if (!$product) {
            $this->redirect('/products.php?error=not_found');
        }

        // زيادة المشاهدات
        $this->productModel->incrementViews($id);

        // منتجات ذات صلة
        $relatedProducts = [];
        if (!empty($product['category_id'])) {
            $relatedProducts = $this->productModel->getRelated($id, $product['category_id']);
        }

        $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'user' => $this->currentUser()
        ]);
    }

    /**
     * البحث عن منتجات (AJAX)
     */
    public function search(): void {
        $query = trim($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->json(['products' => []]);
        }

        $result = $this->productModel->getAll(1, 10, ['search' => $query]);
        $this->json(['products' => $result['products']]);
    }
}
