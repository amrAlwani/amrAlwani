<?php
/**
 * API WishlistController
 */

namespace Api;

require_once BASEPATH . '/models/Wishlist.php';

class WishlistController extends \Controller
{
    private \Wishlist $wishlistModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->wishlistModel = new \Wishlist();
    }
    
    /**
     * قائمة المفضلة
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $wishlist = $this->wishlistModel->getByUser($user['id']);
        
        \Response::success($wishlist);
    }
    
    /**
     * إضافة/إزالة من المفضلة
     */
    public function toggle(): void
    {
        $user = $this->requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('product_id', 'معرف المنتج مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $result = $this->wishlistModel->toggle($user['id'], (int)$data['product_id']);
        
        $message = $result['added'] ? 'تمت الإضافة للمفضلة' : 'تمت الإزالة من المفضلة';
        \Response::success($result, $message);
    }
    
    /**
     * إزالة من المفضلة
     */
    public function remove(string $id): void
    {
        $user = $this->requireAuth();
        $this->wishlistModel->remove($user['id'], (int)$id);
        
        \Response::success(null, 'تمت الإزالة من المفضلة');
    }
}
