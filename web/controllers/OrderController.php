<?php
/**
 * OrderController - الطلبات
 */

require_once BASEPATH . '/models/Order.php';

class OrderController extends Controller
{
    private Order $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
    }
    
    /**
     * قائمة طلبات المستخدم
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $params = $this->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        
        $result = $this->orderModel->getByUser($user['id'], $page, 10);
        
        $this->view('orders/index', [
            'title' => 'طلباتي',
            'orders' => $result['orders'],
            'total' => $result['total'],
            'page' => $page,
            'lastPage' => ceil($result['total'] / 10)
        ]);
    }
    
    /**
     * تفاصيل طلب
     */
    public function show(string $id): void
    {
        $user = $this->requireAuth();
        $order = $this->orderModel->findById((int)$id);
        
        if (!$order || $order['user_id'] != $user['id']) {
            $this->flash('error', 'الطلب غير موجود');
            $this->redirect('orders');
        }
        
        $this->view('orders/show', [
            'title' => 'الطلب #' . $order['order_number'],
            'order' => $order
        ]);
    }
}
