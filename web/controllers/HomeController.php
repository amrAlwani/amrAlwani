<?php
/**
 * HomeController - الصفحة الرئيسية
 */

require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Category.php';

class HomeController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * الصفحة الرئيسية
     */
    public function index(): void
    {
        $featuredProducts = $this->productModel->getFeatured(8);
        $latestProducts = $this->productModel->getLatest(8);
        $categories = $this->categoryModel->getMainCategories();
        
        $this->view('home/index', [
            'title' => 'الرئيسية',
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories
        ]);
    }
}
