<?php
/**
 * CategoryController - التصنيفات
 */

require_once BASEPATH . '/models/Category.php';
require_once BASEPATH . '/models/Product.php';

class CategoryController extends Controller
{
    private Category $categoryModel;
    private Product $productModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new Category();
        $this->productModel = new Product();
    }
    
    /**
     * قائمة التصنيفات
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $this->view('categories/index', [
            'title' => 'التصنيفات',
            'categories' => $categories
        ]);
    }
    
    /**
     * عرض تصنيف واحد مع منتجاته
     */
    public function show(string $slug): void
    {
        $category = $this->categoryModel->findBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'التصنيف غير موجود']);
            return;
        }
        
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->productModel->getAll($page, 12, [
            'category_id' => $category['id'],
            'sort' => $params['sort'] ?? 'newest'
        ]);
        
        $subcategories = $this->categoryModel->getSubCategories($category['id']);
        
        $this->view('categories/show', [
            'title' => $category['name'],
            'category' => $category,
            'subcategories' => $subcategories,
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => ceil($result['total'] / 12)
        ]);
    }
}
