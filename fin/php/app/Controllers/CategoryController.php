<?php
/**
 * CategoryController - التصنيفات
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/Category.php';

class CategoryController extends BaseController {

    private \Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new \Category();
    }

    /**
     * عرض كل التصنيفات
     */
    public function index(): void {
        $categories = $this->categoryModel->getAll();

        $this->view('categories/index', [
            'title' => 'التصنيفات',
            'categories' => $categories,
        ], 'main');
    }

    /**
     * عرض منتجات تصنيف معين
     */
    public function show(int $id): void {
        $category = $this->categoryModel->findById($id);
        
        if (!$category) {
            $this->setFlash('error', 'التصنيف غير موجود');
            $this->redirect('/categories.php');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $sort = $_GET['sort'] ?? 'newest';

        require_once BASEPATH . '/models/Product.php';
        $productModel = new \Product();
        
        $result = $productModel->getAll($page, $perPage, [
            'category_id' => $id,
            'sort' => $sort
        ]);

        $this->view('categories/show', [
            'title' => $category['name'],
            'category' => $category,
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($result['total'] / $perPage),
            'sort' => $sort,
        ], 'main');
    }
}
