<?php
/**
 * API CartController
 */

namespace Api;

require_once BASEPATH . '/models/Cart.php';

class CartController extends \Controller
{
    private \Cart $cartModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new \Cart();
    }
    
    /**
     * عرض السلة
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $cart = $this->cartModel->getByUser($user['id']);
        
        \Response::success($cart);
    }
    
    /**
     * إضافة للسلة
     */
    public function add(): void
    {
        $user = $this->requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('product_id', 'معرف المنتج مطلوب')
                  ->integer('product_id', 'معرف المنتج غير صالح');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        try {
            $cart = $this->cartModel->addItem(
                $user['id'],
                (int)$data['product_id'],
                (int)($data['quantity'] ?? 1),
                isset($data['variant_id']) ? (int)$data['variant_id'] : null
            );
            
            \Response::success($cart, 'تمت الإضافة للسلة');
        } catch (\Exception $e) {
            \Response::error($e->getMessage(), [], 400);
        }
    }
    
    /**
     * تحديث الكمية
     */
    public function update(): void
    {
        $user = $this->requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('item_id', 'معرف العنصر مطلوب')
                  ->required('quantity', 'الكمية مطلوبة');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        try {
            $cart = $this->cartModel->updateItem(
                $user['id'],
                (int)$data['item_id'],
                (int)$data['quantity']
            );
            
            \Response::success($cart, 'تم التحديث');
        } catch (\Exception $e) {
            \Response::error($e->getMessage(), [], 400);
        }
    }
    
    /**
     * إزالة من السلة
     */
    public function remove(string $id): void
    {
        $user = $this->requireAuth();
        $cart = $this->cartModel->removeItem($user['id'], (int)$id);
        
        \Response::success($cart, 'تمت الإزالة');
    }
    
    /**
     * تفريغ السلة
     */
    public function clear(): void
    {
        $user = $this->requireAuth();
        $cart = $this->cartModel->clear($user['id']);
        
        \Response::success($cart, 'تم تفريغ السلة');
    }
    
    /**
     * تطبيق كوبون
     */
    public function applyCoupon(): void
    {
        $user = $this->requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('code', 'كود الكوبون مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        try {
            $cart = $this->cartModel->applyCoupon($user['id'], $data['code']);
            \Response::success($cart, 'تم تطبيق الكوبون');
        } catch (\Exception $e) {
            \Response::error($e->getMessage(), [], 400);
        }
    }
}
