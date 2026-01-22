<?php
/**
 * Admin OrderController - إدارة الطلبات
 */

namespace App\Controllers\Admin;

require_once __DIR__ . '/../BaseController.php';
require_once BASEPATH . '/models/Order.php';

class OrderController extends \App\Controllers\BaseController {

    private \Order $orderModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = new \Order();
    }

    /**
     * عرض قائمة الطلبات
     */
    public function index(): void {
        $admin = $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $orders = $this->getOrders($page, $perPage, $status, $search);

        $this->view('admin/orders/index', [
            'title' => 'إدارة الطلبات',
            'user' => $admin,
            'orders' => $orders['data'],
            'total' => $orders['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($orders['total'] / $perPage),
            'status' => $status,
            'search' => $search,
            'statuses' => $this->getStatuses(),
        ], 'admin');
    }

    /**
     * عرض تفاصيل طلب
     */
    public function show(int $id): void {
        $admin = $this->requireAdmin();

        $order = $this->orderModel->findById($id);
        if (!$order) {
            $this->setFlash('error', 'الطلب غير موجود');
            $this->redirect('/admin/orders.php');
        }

        $this->view('admin/orders/show', [
            'title' => 'تفاصيل الطلب #' . $id,
            'user' => $admin,
            'order' => $order,
            'statuses' => $this->getStatuses(),
            'csrf_token' => $this->getCsrfToken(),
        ], 'admin');
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateStatus(int $id): void {
        $admin = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/admin/orders.php?action=show&id=' . $id);
        }

        $status = $_POST['status'] ?? '';
        $paymentStatus = $_POST['payment_status'] ?? '';

        $data = [];
        if ($status) $data['status'] = $status;
        if ($paymentStatus) $data['payment_status'] = $paymentStatus;

        if ($this->orderModel->update($id, $data)) {
            $this->setFlash('success', 'تم تحديث حالة الطلب');
        } else {
            $this->setFlash('error', 'فشل في تحديث الطلب');
        }
        $this->redirect('/admin/orders.php?action=show&id=' . $id);
    }

    /**
     * الحصول على الطلبات
     */
    private function getOrders(int $page, int $perPage, string $status = '', string $search = ''): array {
        try {
            $db = \Database::getInstance()->getConnection();
            $offset = ($page - 1) * $perPage;

            $where = ["1=1"];
            $params = [];

            if ($status) {
                $where[] = "o.status = ?";
                $params[] = $status;
            }

            if ($search) {
                $where[] = "(o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $where);

            // عدد الإجمالي
            $countSql = "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE {$whereClause}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // البيانات
            $sql = "SELECT o.*, u.name as user_name, u.email as user_email
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    WHERE {$whereClause} 
                    ORDER BY o.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return [
                'data' => $stmt->fetchAll(),
                'total' => $total
            ];
        } catch (\PDOException $e) {
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * حالات الطلب
     */
    private function getStatuses(): array {
        return [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
        ];
    }
}
