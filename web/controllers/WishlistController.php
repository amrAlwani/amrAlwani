<?php
/**
 * WishlistController - المفضلة
 */

require_once BASEPATH . '/models/Wishlist.php';

class WishlistController extends Controller
{
    private Wishlist $wishlistModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->wishlistModel = new Wishlist();
    }
    
    /**
     * قائمة المفضلة
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $wishlist = $this->wishlistModel->getByUser($user['id']);
        
        $this->view('wishlist/index', [
            'title' => 'المفضلة',
            'wishlist' => $wishlist
        ]);
    }
    
    /**
     * إضافة/إزالة من المفضلة
     */
    public function toggle(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        $productId = (int)($data['product_id'] ?? 0);
        
        if (!$productId) {
            $this->flash('error', 'المنتج غير موجود');
            $this->redirect('products');
        }
        
        $result = $this->wishlistModel->toggle($user['id'], $productId);
        
        if ($result['added']) {
            $this->flash('success', 'تمت الإضافة للمفضلة');
        } else {
            $this->flash('success', 'تمت الإزالة من المفضلة');
        }
        
        // العودة للصفحة السابقة
        $referer = $_SERVER['HTTP_REFERER'] ?? url('wishlist');
        header('Location: ' . $referer);
        exit;
    }
}
