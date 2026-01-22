<?php
/**
 * CartController - سلة التسوق
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/Cart.php';

class CartController extends BaseController {

    private \Cart $cartModel;

    public function __construct() {
        parent::__construct();
        $this->cartModel = new \Cart();
    }

    /**
     * عرض السلة
     */
    public function index(): void {
        $user = $this->getUser();
        $cart = [];
        
        if ($user) {
            $cart = $this->cartModel->getByUser($user['id']);
        }

        $this->view('cart/index', [
            'title' => 'سلة التسوق',
            'cart' => $cart,
            'user' => $user,
            'csrf_token' => $this->getCsrfToken(),
        ], 'main');
    }

    /**
     * إضافة منتج للسلة (AJAX)
     */
    public function add(): void {
        $user = $this->requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['product_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'معرف المنتج مطلوب'], 400);
        }

        $cart = $this->cartModel->addItem(
            $user['id'],
            (int)$data['product_id'],
            (int)($data['quantity'] ?? 1),
            isset($data['variant_id']) ? (int)$data['variant_id'] : null
        );

        $this->jsonResponse(['success' => true, 'cart' => $cart, 'message' => 'تمت الإضافة للسلة']);
    }

    /**
     * تحديث الكمية (AJAX)
     */
    public function update(): void {
        $user = $this->requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['item_id']) || !isset($data['quantity'])) {
            $this->jsonResponse(['success' => false, 'message' => 'البيانات غير كاملة'], 400);
        }

        $cart = $this->cartModel->updateQuantity(
            $user['id'],
            (int)$data['item_id'],
            (int)$data['quantity']
        );

        $this->jsonResponse(['success' => true, 'cart' => $cart, 'message' => 'تم تحديث الكمية']);
    }

    /**
     * حذف عنصر من السلة (AJAX)
     */
    public function remove(): void {
        $user = $this->requireAuth();
        
        $itemId = $_GET['item_id'] ?? null;
        
        if (!$itemId) {
            $this->jsonResponse(['success' => false, 'message' => 'معرف العنصر مطلوب'], 400);
        }

        $cart = $this->cartModel->removeItem($user['id'], (int)$itemId);

        $this->jsonResponse(['success' => true, 'cart' => $cart, 'message' => 'تم الحذف من السلة']);
    }

    /**
     * تفريغ السلة
     */
    public function clear(): void {
        $user = $this->requireAuth();
        
        $cart = $this->cartModel->clear($user['id']);

        $this->jsonResponse(['success' => true, 'cart' => $cart, 'message' => 'تم تفريغ السلة']);
    }

    /**
     * إرجاع JSON response
     */
    private function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
