<?php
/**
 * Admin DashboardController - لوحة تحكم المدير
 */

namespace App\Controllers\Admin;

require_once __DIR__ . '/../BaseController.php';
require_once BASEPATH . '/models/Product.php';
require_once BASEPATH . '/models/Order.php';
require_once BASEPATH . '/models/User.php';

class DashboardController extends \App\Controllers\BaseController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * عرض لوحة التحكم الرئيسية
     */
    public function index(): void {
        $admin = $this->requireAdmin();

        // إحصائيات
        $stats = $this->getStats();

        $this->view('admin/dashboard', [
            'title' => 'لوحة التحكم',
            'user' => $admin,
            'stats' => $stats,
            'recentOrders' => $this->getRecentOrders(),
            'topProducts' => $this->getTopProducts(),
        ], 'admin');
    }

    /**
     * الحصول على الإحصائيات
     */
    private function getStats(): array {
        $db = \Database::getInstance()->getConnection();

        // إجمالي المنتجات
        $stmt = $db->query("SELECT COUNT(*) FROM products");
        $totalProducts = (int)$stmt->fetchColumn();

        // إجمالي الطلبات
        $stmt = $db->query("SELECT COUNT(*) FROM orders");
        $totalOrders = (int)$stmt->fetchColumn();

        // إجمالي المستخدمين
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $totalUsers = (int)$stmt->fetchColumn();

        // إجمالي المبيعات
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'");
        $totalSales = (float)$stmt->fetchColumn();

        // طلبات اليوم
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $todayOrders = (int)$stmt->fetchColumn();

        // الطلبات المعلقة
        $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $pendingOrders = (int)$stmt->fetchColumn();

        return [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_users' => $totalUsers,
            'total_sales' => $totalSales,
            'today_orders' => $todayOrders,
            'pending_orders' => $pendingOrders,
        ];
    }

    /**
     * آخر الطلبات
     */
    private function getRecentOrders(int $limit = 5): array {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT o.*, u.name as user_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * المنتجات الأكثر مبيعاً
     */
    private function getTopProducts(int $limit = 5): array {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.*, COALESCE(p.sales_count, 0) as sales
            FROM products p 
            ORDER BY sales_count DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
