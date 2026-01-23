<?php
/**
 * ProductController - المتحكم في عرض المنتجات
 */

require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Category.php';
require_once BASEPATH . '/models/Review.php';

class ProductController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;
    private Review $reviewModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->reviewModel = new Review();
    }
    
    public function index(): void
    {
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = 12;
        
        $filters = [
            'category_id' => $params['category'] ?? null,
            'search'      => $params['search'] ?? null,
            'sort'        => $params['sort'] ?? 'newest',
            'min_price'   => $params['min_price'] ?? null,
            'max_price'   => $params['max_price'] ?? null,
            'is_featured' => $params['featured'] ?? null 
        ];
        
        $result = $this->productModel->getAll($page, $perPage, $filters);
        $categories = $this->categoryModel->getAll();
        
        $this->view('products/index', [
            'title' => isset($params['featured']) ? 'المنتجات المميزة' : 'كل المنتجات',
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => $result['last_page'],
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    public function show(string $slug): void
    {
        $product = $this->productModel->findBySlug($slug);
        if (!$product) $this->redirect('products');

        $this->productModel->incrementViews($product['id']);
        $reviews = $this->reviewModel->getByProduct($product['id']);
        
        $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'reviews' => $reviews
        ]);
    }
}