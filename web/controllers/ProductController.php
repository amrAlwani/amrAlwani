<?php
/**
 * ProductController - المنتجات
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
    
    /**
     * قائمة المنتجات
     */
    public function index(): void
    {
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = 12;
        
        $filters = [
            'category_id' => $params['category'] ?? null,
            'search' => $params['search'] ?? null,
            'sort' => $params['sort'] ?? 'newest',
            'min_price' => $params['min_price'] ?? null,
            'max_price' => $params['max_price'] ?? null
        ];
        
        $result = $this->productModel->getAll($page, $perPage, $filters);
        $categories = $this->categoryModel->getAll();
        
        $this->view('products/index', [
            'title' => 'المنتجات',
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => ceil($result['total'] / $perPage),
            'categories' => $categories,
            'filters' => $filters
        ]);
    }
    
    /**
     * عرض منتج واحد
     */
    public function show(string $slug): void
    {
        $product = $this->productModel->findBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'المنتج غير موجود']);
            return;
        }
        
        // زيادة المشاهدات
        $this->productModel->incrementViews($product['id']);
        
        // المنتجات المشابهة
        $relatedProducts = $this->productModel->getRelated($product['id'], $product['category_id'] ?? 0, 4);
        
        // التقييمات
        $reviews = $this->reviewModel->getByProduct($product['id']);
        
        // التحقق من المفضلة
        $isInWishlist = false;
        if ($this->user) {
            require_once BASEPATH . '/models/Wishlist.php';
            $wishlistModel = new Wishlist();
            $isInWishlist = $wishlistModel->exists($this->user['id'], $product['id']);
        }
        
        $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'isInWishlist' => $isInWishlist
        ]);
    }
    
    /**
     * البحث
     */
    public function search(): void
    {
        $params = $this->getQueryParams();
        $query = $params['q'] ?? '';
        $page = max(1, (int)($params['page'] ?? 1));
        
        if (empty($query)) {
            $this->redirect('products');
        }
        
        $result = $this->productModel->search($query, $page, 12);
        $categories = $this->categoryModel->getAll();
        
        $this->view('products/search', [
            'title' => 'نتائج البحث: ' . $query,
            'query' => $query,
            'products' => $result['products'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => ceil($result['total'] / 12),
            'categories' => $categories
        ]);
    }
}
