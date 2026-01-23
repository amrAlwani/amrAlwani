<?php
/**
 * API ProductController
 */

namespace Api;

require_once BASEPATH . '/models/Product.php';

class ProductController extends \Controller
{
    private \Product $productModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new \Product();
    }
    
    /**
     * قائمة المنتجات
     */
    public function index(): void
    {
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 12)));
        
        $filters = [
            'category_id' => $params['category_id'] ?? null,
            'search' => $params['search'] ?? null,
            'sort' => $params['sort'] ?? 'newest',
            'min_price' => $params['min_price'] ?? null,
            'max_price' => $params['max_price'] ?? null
        ];
        
        $result = $this->productModel->getAll($page, $perPage, $filters);
        
        \Response::paginate($result['products'], $result['total'], $page, $perPage);
    }
    
    /**
     * المنتجات المميزة
     */
    public function featured(): void
    {
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 8)));
        $products = $this->productModel->getFeatured($limit);
        
        \Response::success($products);
    }
    
    /**
     * منتج واحد
     */
    public function show(string $id): void
    {
        $product = $this->productModel->findById((int)$id);
        
        if (!$product) {
            \Response::notFound('المنتج غير موجود');
        }
        
        $this->productModel->incrementViews((int)$id);
        
        // إضافة المنتجات المشابهة
        $product['related'] = $this->productModel->getRelated(
            $product['id'], 
            $product['category_id'] ?? 0, 
            4
        );
        
        \Response::success($product);
    }
}
