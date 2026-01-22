<?php
/**
 * Categories API
 * واجهة برمجة التصنيفات
 * 
 * ملف جديد - مفقود من المستودع الأصلي
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Category.php';

$action = $_GET['action'] ?? 'list';
$categoryModel = new Category();

switch ($action) {
    case 'list':
        $categories = $categoryModel->getAll();
        Response::success($categories);
        break;

    case 'tree':
        $categories = $categoryModel->getTree();
        Response::success($categories);
        break;

    case 'main':
        $categories = $categoryModel->getMainCategories();
        Response::success($categories);
        break;

    case 'sub':
        $parentId = $_GET['parent_id'] ?? null;
        
        if (!$parentId) {
            Response::error('معرف التصنيف الأب مطلوب', [], 400);
        }
        
        $categories = $categoryModel->getSubCategories((int)$parentId);
        Response::success($categories);
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        $slug = $_GET['slug'] ?? null;
        
        if (!$id && !$slug) {
            Response::error('معرف التصنيف أو الـ slug مطلوب', [], 400);
        }
        
        $category = $id 
            ? $categoryModel->findById((int)$id)
            : $categoryModel->findBySlug($slug);
        
        if (!$category) {
            Response::notFound('التصنيف غير موجود');
        }
        
        // إضافة عدد المنتجات
        $category['product_count'] = $categoryModel->getProductCount($category['id']);
        
        // إضافة التصنيفات الفرعية
        $category['subcategories'] = $categoryModel->getSubCategories($category['id']);
        
        Response::success($category);
        break;

    default:
        Response::error('إجراء غير صالح', [], 400);
}
