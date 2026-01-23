<?php
/**
 * API CategoryController
 */

namespace Api;

require_once BASEPATH . '/models/Category.php';

class CategoryController extends \Controller
{
    private \Category $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new \Category();
    }
    
    /**
     * قائمة التصنيفات
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAll();
        \Response::success($categories);
    }
    
    /**
     * شجرة التصنيفات
     */
    public function tree(): void
    {
        $tree = $this->categoryModel->getTree();
        \Response::success($tree);
    }
    
    /**
     * تصنيف واحد
     */
    public function show(string $id): void
    {
        $category = $this->categoryModel->findById((int)$id);
        
        if (!$category) {
            \Response::notFound('التصنيف غير موجود');
        }
        
        // إضافة التصنيفات الفرعية
        $category['subcategories'] = $this->categoryModel->getSubCategories((int)$id);
        
        \Response::success($category);
    }
}
