<?php
/**
 * CartController - سلة التسوق
 */

require_once BASEPATH . '/models/Cart.php';
require_once BASEPATH . '/models/Product.php';

class CartController extends Controller
{
    private Cart $cartModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
    }
    
    /**
     * عرض السلة
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $cart = $this->cartModel->getByUser($user['id']);
        
        $this->view('cart/index', [
            'title' => 'سلة التسوق',
            'cart' => $cart
        ]);
    }
    
    /**
     * إضافة للسلة
     */
    public function add(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = max(1, (int)($data['quantity'] ?? 1));
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        
        if (!$productId) {
            $this->flash('error', 'المنتج غير موجود');
            $this->redirect('products');
        }
        
        try {
            $this->cartModel->addItem($user['id'], $productId, $quantity, $variantId);
            $this->flash('success', 'تمت الإضافة للسلة');
        } catch (Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        
        // العودة للصفحة السابقة أو السلة
        $referer = $_SERVER['HTTP_REFERER'] ?? url('cart');
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * تحديث الكمية
     */
    public function update(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        $itemId = (int)($data['item_id'] ?? 0);
        $quantity = max(1, (int)($data['quantity'] ?? 1));
        
        if (!$itemId) {
            $this->flash('error', 'العنصر غير موجود');
            $this->redirect('cart');
        }
        
        try {
            $this->cartModel->updateItem($user['id'], $itemId, $quantity);
            $this->flash('success', 'تم التحديث');
        } catch (Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        
        $this->redirect('cart');
    }
    
    /**
     * إزالة من السلة
     */
    public function remove(string $id): void
    {
        $user = $this->requireAuth();
        
        $this->cartModel->removeItem($user['id'], (int)$id);
        $this->flash('success', 'تمت الإزالة من السلة');
        
        $this->redirect('cart');
    }
}
