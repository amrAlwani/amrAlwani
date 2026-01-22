<?php
/**
 * Cart API
 * واجهة برمجة السلة
 * 
 * تم التحسين: إضافة Validation شامل
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../models/Cart.php';

$action = $_GET['action'] ?? 'list';

// جميع عمليات السلة تتطلب مصادقة
$user = Auth::requireAuth();
$cartModel = new Cart();

switch ($action) {
    case 'list':
        $cart = $cartModel->getByUser($user['id']);
        Response::success($cart);
        break;

    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error('بيانات غير صالحة', [], 400);
        }
        
        $validator = new Validator($data);
        $validator->required('product_id', 'معرف المنتج مطلوب')
                  ->integer('product_id', 'معرف المنتج يجب أن يكون رقماً صحيحاً');
        
        if (isset($data['quantity'])) {
            $validator->integer('quantity', 'الكمية يجب أن تكون رقماً صحيحاً')
                      ->minValue('quantity', 1, 'الكمية يجب أن تكون 1 على الأقل')
                      ->maxValue('quantity', 99, 'الكمية يجب ألا تتجاوز 99');
        }
        
        $validator->validate();
        
        $cart = $cartModel->addItem(
            $user['id'], 
            (int)$data['product_id'], 
            (int)($data['quantity'] ?? 1), 
            isset($data['variant_id']) ? (int)$data['variant_id'] : null
        );
        Response::success($cart, 'تمت الإضافة للسلة');
        break;

    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error('بيانات غير صالحة', [], 400);
        }
        
        $validator = new Validator($data);
        $validator->required('item_id', 'معرف العنصر مطلوب')
                  ->integer('item_id', 'معرف العنصر يجب أن يكون رقماً صحيحاً')
                  ->required('quantity', 'الكمية مطلوبة')
                  ->integer('quantity', 'الكمية يجب أن تكون رقماً صحيحاً')
                  ->minValue('quantity', 0, 'الكمية يجب ألا تكون سالبة')
                  ->maxValue('quantity', 99, 'الكمية يجب ألا تتجاوز 99');
        $validator->validate();
        
        $cart = $cartModel->updateQuantity(
            $user['id'], 
            (int)$data['item_id'], 
            (int)$data['quantity']
        );
        Response::success($cart, 'تم تحديث الكمية');
        break;

    case 'remove':
        $itemId = $_GET['item_id'] ?? null;
        
        if (!$itemId || !is_numeric($itemId)) {
            Response::error('معرف العنصر غير صالح', [], 400);
        }
        
        $cart = $cartModel->removeItem($user['id'], (int)$itemId);
        Response::success($cart, 'تمت الإزالة من السلة');
        break;

    case 'clear':
        $cart = $cartModel->clear($user['id']);
        Response::success($cart, 'تم تفريغ السلة');
        break;

    case 'totals':
        $coupon = !empty($_GET['coupon']) ? Validator::sanitize($_GET['coupon']) : null;
        
        // التحقق من الكوبون
        if ($coupon && mb_strlen($coupon) > 50) {
            Response::error('كود الكوبون غير صالح', [], 400);
        }
        
        $totals = $cartModel->calculateTotals($user['id'], $coupon);
        Response::success($totals);
        break;

    default:
        Response::error('إجراء غير صالح', [], 400);
}
