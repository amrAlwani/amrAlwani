<?php
/**
 * CheckoutController - إتمام الطلب
 */

require_once BASEPATH . '/models/Cart.php';
require_once BASEPATH . '/models/Order.php';
require_once BASEPATH . '/models/Address.php';
require_once BASEPATH . '/models/Coupon.php';

class CheckoutController extends Controller
{
    private Cart $cartModel;
    private Order $orderModel;
    private Address $addressModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->addressModel = new Address();
    }
    
    /**
     * صفحة الدفع
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $cart = $this->cartModel->getByUser($user['id']);
        
        if (empty($cart['items'])) {
            $this->flash('error', 'السلة فارغة');
            $this->redirect('cart');
        }
        
        $addresses = $this->addressModel->getByUser($user['id']);
        $defaultAddress = $this->addressModel->getDefault($user['id']);
        
        $this->view('checkout/index', [
            'title' => 'إتمام الطلب',
            'cart' => $cart,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress
        ]);
    }
    
    /**
     * معالجة الطلب
     */
    public function process(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        // التحقق من CSRF
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج');
            $this->redirect('checkout');
        }
        
        // التحقق من السلة
        $cart = $this->cartModel->getByUser($user['id']);
        if (empty($cart['items'])) {
            $this->flash('error', 'السلة فارغة');
            $this->redirect('cart');
        }
        
        // التحقق من الحد الأدنى
        if ($cart['subtotal'] < MIN_ORDER_VALUE) {
            $this->flash('error', 'الحد الأدنى للطلب ' . MIN_ORDER_VALUE . ' ' . CURRENCY_SYMBOL);
            $this->redirect('cart');
        }
        
        // بناء عنوان الشحن
        $shippingAddress = [];
        
        if (!empty($data['address_id'])) {
            // استخدام عنوان محفوظ
            $address = $this->addressModel->findById((int)$data['address_id']);
            if ($address && $address['user_id'] == $user['id']) {
                $shippingAddress = $address;
            }
        }
        
        if (empty($shippingAddress)) {
            // استخدام عنوان جديد
            $validator = new Validator($data);
            $validator->required('name', 'الاسم مطلوب')
                      ->required('phone', 'رقم الهاتف مطلوب')
                      ->required('city', 'المدينة مطلوبة')
                      ->required('street', 'العنوان مطلوب');
            
            if (!$validator->passes()) {
                $this->flash('error', implode('<br>', $validator->getErrors()));
                $this->redirect('checkout');
            }
            
            $shippingAddress = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'district' => $data['district'] ?? '',
                'street' => $data['street'],
                'building' => $data['building'] ?? '',
                'notes' => $data['notes'] ?? ''
            ];
            
            // حفظ العنوان إذا طلب ذلك
            if (!empty($data['save_address'])) {
                $this->addressModel->create([
                    'user_id' => $user['id'],
                    ...$shippingAddress,
                    'is_default' => empty($this->addressModel->getByUser($user['id'])) ? 1 : 0
                ]);
            }
        }
        
        // طريقة الدفع
        $paymentMethod = $data['payment_method'] ?? 'cod';
        $couponCode = $data['coupon_code'] ?? null;
        
        // إنشاء الطلب
        $order = $this->orderModel->create($user['id'], $shippingAddress, $couponCode, $paymentMethod);
        
        if (!$order) {
            $this->flash('error', 'حدث خطأ أثناء إنشاء الطلب');
            $this->redirect('checkout');
        }
        
        // تفريغ السلة
        $this->cartModel->clear($user['id']);
        
        $this->redirect('order-success/' . $order['id']);
    }
    
    /**
     * صفحة نجاح الطلب
     */
    public function success(string $id): void
    {
        $user = $this->requireAuth();
        $order = $this->orderModel->findById((int)$id);
        
        if (!$order || $order['user_id'] != $user['id']) {
            $this->flash('error', 'الطلب غير موجود');
            $this->redirect('orders');
        }
        
        $this->view('checkout/success', [
            'title' => 'تم الطلب بنجاح',
            'order' => $order
        ]);
    }
}
