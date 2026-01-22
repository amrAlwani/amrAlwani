<?php
/**
 * Products API Endpoints
 * واجهة برمجة المنتجات
 * 
 * تم التحسين: إضافة Validation شامل
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../models/Product.php';

$action = $_GET['action'] ?? 'list';
$productModel = new Product();

switch ($action) {
    case 'list':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 12)));
        
        // تنظيف وتحقق من الفلاتر
        $search = !empty($_GET['search']) ? Validator::sanitize($_GET['search']) : null;
        
        // التحقق من طول البحث
        if ($search && mb_strlen($search) > 200) {
            Response::error('كلمة البحث طويلة جداً', [], 400);
        }
        
        $filters = [
            'category_id' => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null,
            'search' => $search,
            'min_price' => !empty($_GET['min_price']) ? max(0, (float)$_GET['min_price']) : null,
            'max_price' => !empty($_GET['max_price']) ? max(0, (float)$_GET['max_price']) : null,
            'sort' => !empty($_GET['sort']) && in_array($_GET['sort'], ['newest', 'oldest', 'price_asc', 'price_desc', 'popular']) ? $_GET['sort'] : null,
            'in_stock' => !empty($_GET['in_stock']) ? true : null,
        ];
        
        $result = $productModel->getAll($page, $perPage, $filters);
        Response::paginate($result['products'], $result['total'], $page, $perPage);
        break;

    case 'featured':
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 8)));
        $products = $productModel->getFeatured($limit);
        Response::success($products);
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        
        if (!$id || !is_numeric($id)) {
            Response::error('معرف المنتج غير صالح', [], 400);
        }
        
        $product = $productModel->findById((int)$id);
        
        if (!$product) {
            Response::notFound('المنتج غير موجود');
        }
        
        // زيادة عدد المشاهدات
        $productModel->incrementViews((int)$id);
        
        // الحصول على المنتجات ذات الصلة
        if (!empty($product['category_id'])) {
            $product['related'] = $productModel->getRelated((int)$id, $product['category_id']);
        } else {
            $product['related'] = [];
        }
        
        Response::success($product);
        break;

    case 'search':
        $query = !empty($_GET['q']) ? Validator::sanitize($_GET['q']) : '';
        
        if (mb_strlen($query) < 2) {
            Response::error('كلمة البحث يجب أن تكون حرفين على الأقل', [], 400);
        }
        
        if (mb_strlen($query) > 200) {
            Response::error('كلمة البحث طويلة جداً', [], 400);
        }
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 12)));
        
        $result = $productModel->getAll($page, $perPage, ['search' => $query]);
        Response::paginate($result['products'], $result['total'], $page, $perPage);
        break;

    default:
        Response::error('إجراء غير صالح', [], 400);
}
