<?php
/**
 * API OrderController
 */

namespace Api;

require_once BASEPATH . '/models/Order.php';
require_once BASEPATH . '/models/Cart.php';

class OrderController extends \Controller
{
    private \Order $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new \Order();
    }
    
    /**
     * قائمة الطلبات
     */
    public function index(): void
    {
        $user = \Auth::requireAuth();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->orderModel->getByUser($user['id'], $page, 10);
        
        \Response::paginate($result['orders'], $result['total'], $page, 10);
    }
    
    /**
     * تفاصيل طلب
     */
    public function show(string $id): void
    {
        $user = \Auth::requireAuth();
        $order = $this->orderModel->findById((int)$id);
        
        if (!$order || $order['user_id'] != $user['id']) {
            \Response::notFound('الطلب غير موجود');
        }
        
        \Response::success($order);
    }
    
    /**
     * إنشاء طلب
     */
    public function store(): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        // التحقق من السلة
        $cartModel = new \Cart();
        $cart = $cartModel->getByUser($user['id']);
        
        if (empty($cart['items'])) {
            \Response::error('السلة فارغة', [], 400);
        }
        
        // التحقق من العنوان
        $validator = new \Validator($data);
        $validator->required('shipping_address', 'عنوان الشحن مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $shippingAddress = $data['shipping_address'];
        $couponCode = $data['coupon_code'] ?? null;
        $paymentMethod = $data['payment_method'] ?? 'cod';
        
        $order = $this->orderModel->create($user['id'], $shippingAddress, $couponCode, $paymentMethod);
        
        if (!$order) {
            \Response::error('حدث خطأ أثناء إنشاء الطلب', [], 500);
        }
        
        // تفريغ السلة
        $cartModel->clear($user['id']);
        
        \Response::created($order, 'تم إنشاء الطلب بنجاح');
    }
    
    /**
     * إلغاء طلب
     */
    public function cancel(string $id): void
    {
        $user = \Auth::requireAuth();
        
        $result = $this->orderModel->cancel((int)$id, $user['id']);
        
        if (!$result) {
            \Response::error('لا يمكن إلغاء هذا الطلب', [], 400);
        }
        
        \Response::success(null, 'تم إلغاء الطلب');
    }
    
    /**
     * تتبع طلب
     */
    public function track(string $number): void
    {
        $tracking = $this->orderModel->track($number);
        
        if (!$tracking) {
            \Response::notFound('الطلب غير موجود');
        }
        
        \Response::success($tracking);
    }
}
