<?php
/**
 * Admin UserController - إدارة المستخدمين
 */

namespace App\Controllers\Admin;

require_once __DIR__ . '/../BaseController.php';
require_once BASEPATH . '/models/User.php';

class UserController extends \App\Controllers\BaseController {

    private \User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new \User();
    }

    /**
     * عرض قائمة المستخدمين
     */
    public function index(): void {
        $admin = $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';

        $users = $this->getUsers($page, $perPage, $search, $role);

        $this->view('admin/users/index', [
            'title' => 'إدارة المستخدمين',
            'user' => $admin,
            'users' => $users['data'],
            'total' => $users['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($users['total'] / $perPage),
            'search' => $search,
            'role' => $role,
        ], 'admin');
    }

    /**
     * عرض تفاصيل مستخدم
     */
    public function show(int $id): void {
        $admin = $this->requireAdmin();

        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->setFlash('error', 'المستخدم غير موجود');
            $this->redirect('/admin/users.php');
        }

        // طلبات المستخدم
        $orders = $this->getUserOrders($id);

        $this->view('admin/users/show', [
            'title' => 'تفاصيل المستخدم: ' . $targetUser['name'],
            'user' => $admin,
            'targetUser' => $targetUser,
            'orders' => $orders,
        ], 'admin');
    }

    /**
     * تعديل مستخدم
     */
    public function edit(int $id): void {
        $admin = $this->requireAdmin();

        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->setFlash('error', 'المستخدم غير موجود');
            $this->redirect('/admin/users.php');
        }

        $this->view('admin/users/edit', [
            'title' => 'تعديل المستخدم: ' . $targetUser['name'],
            'user' => $admin,
            'targetUser' => $targetUser,
            'csrf_token' => $this->getCsrfToken(),
        ], 'admin');
    }

    /**
     * تحديث مستخدم
     */
    public function update(int $id): void {
        $admin = $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users.php');
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->setFlash('error', 'طلب غير صالح');
            $this->redirect('/admin/users.php?action=edit&id=' . $id);
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'] ?? 'user',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($this->updateUser($id, $data)) {
            $this->setFlash('success', 'تم تحديث المستخدم بنجاح');
        } else {
            $this->setFlash('error', 'فشل في تحديث المستخدم');
        }
        $this->redirect('/admin/users.php');
    }

    /**
     * تفعيل/تعطيل مستخدم
     */
    public function toggleStatus(int $id): void {
        $admin = $this->requireAdmin();

        // منع تعطيل نفسه
        if ($id === (int)$admin['id']) {
            $this->setFlash('error', 'لا يمكنك تعطيل حسابك');
            $this->redirect('/admin/users.php');
        }

        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->setFlash('error', 'المستخدم غير موجود');
            $this->redirect('/admin/users.php');
        }

        $newStatus = $targetUser['is_active'] ? 0 : 1;
        
        if ($this->updateUser($id, ['is_active' => $newStatus])) {
            $message = $newStatus ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم';
            $this->setFlash('success', $message);
        } else {
            $this->setFlash('error', 'فشل في تحديث الحالة');
        }
        $this->redirect('/admin/users.php');
    }

    /**
     * الحصول على قائمة المستخدمين
     */
    private function getUsers(int $page, int $perPage, string $search = '', string $role = ''): array {
        try {
            $db = \Database::getInstance()->getConnection();
            $offset = ($page - 1) * $perPage;

            $where = ["1=1"];
            $params = [];

            if ($search) {
                $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($role) {
                $where[] = "role = ?";
                $params[] = $role;
            }

            $whereClause = implode(' AND ', $where);

            // عدد الإجمالي
            $countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE {$whereClause}");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // البيانات
            $sql = "SELECT id, name, email, phone, role, is_active, created_at 
                    FROM users WHERE {$whereClause} 
                    ORDER BY created_at DESC 
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
     * الحصول على طلبات مستخدم
     */
    private function getUserOrders(int $userId): array {
        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * تحديث مستخدم
     */
    private function updateUser(int $id, array $data): bool {
        try {
            $db = \Database::getInstance()->getConnection();
            
            $fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            return false;
        }
    }
}
