<?php
/**
 * OrderController - طلبات المستخدم
 */

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once BASEPATH . '/models/Order.php';

class OrderController extends BaseController {

    private \Order $orderModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = new \Order();
    }

    /**
     * عرض طلبات المستخدم
     */
    public function index(): void {
        $user = $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $result = $this->orderModel->getByUser($user['id'], $page, $perPage);

        $this->view('orders/index', [
            'title' => 'طلباتي',
            'user' => $user,
            'orders' => $result['orders'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($result['total'] / $perPage),
        ], 'main');
    }

    /**
     * عرض تفاصيل طلب
     */
    public function show(int $id): void {
        $user = $this->requireAuth();

        $order = $this->orderModel->findById($id);

        if (!$order || $order['user_id'] != $user['id']) {
            $this->setFlash('error', 'الطلب غير موجود');
            $this->redirect('/orders.php');
        }

        $this->view('orders/show', [
            'title' => 'طلب #' . ($order['order_number'] ?? $id),
            'user' => $user,
            'order' => $order,
        ], 'main');
    }

    /**
     * تتبع الطلب
     */
    public function track(): void {
        $orderNumber = $_GET['order_number'] ?? '';

        if (!$orderNumber) {
            $this->view('orders/track', [
                'title' => 'تتبع الطلب',
                'order' => null,
            ], 'main');
            return;
        }

        $order = $this->orderModel->findByOrderNumber($orderNumber);

        $this->view('orders/track', [
            'title' => 'تتبع الطلب',
            'order' => $order,
            'orderNumber' => $orderNumber,
        ], 'main');
    }

    /**
     * إلغاء طلب
     */
    public function cancel(int $id): void {
        $user = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/orders.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/orders.php?action=show&id=' . $id);
        }

        $result = $this->orderModel->cancel($id, $user['id']);

        if ($result['success']) {
            $this->setFlash('success', 'تم إلغاء الطلب بنجاح');
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/orders.php?action=show&id=' . $id);
    }
}
