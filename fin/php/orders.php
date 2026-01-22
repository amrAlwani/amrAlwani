<?php
/**
 * صفحة طلبات المستخدم - SwiftCart
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';
require_once 'config/database.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$orders = [];
$totalOrders = 0;
$totalPages = 0;

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // عد الطلبات
    $countStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id");
    $countStmt->execute(['user_id' => $_SESSION['user_id']]);
    $totalOrders = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalOrders / $perPage);
    
    // جلب الطلبات
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $orders = [];
}

$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-purple-100 text-purple-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];

$statusLabels = [
    'pending' => 'قيد الانتظار',
    'processing' => 'قيد المعالجة',
    'shipped' => 'تم الشحن',
    'delivered' => 'تم التوصيل',
    'cancelled' => 'ملغي'
];
?>

<div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="mr-2 text-xl font-bold text-gray-900">SwiftCart</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-700">مرحباً، <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">لوحة التحكم</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">خروج</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">طلباتي</h1>
            <span class="text-gray-500"><?= $totalOrders ?> طلب</span>
        </div>
        
        <?php if (empty($orders)): ?>
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p class="text-gray-500 text-lg mb-4">لا توجد طلبات حتى الآن</p>
            <a href="products.php" class="inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                تصفح المنتجات
            </a>
        </div>
        <?php else: ?>
        
        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="mb-4 md:mb-0">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <span class="text-lg font-semibold text-gray-900">
                                طلب #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>
                            </span>
                            <?php $status = $order['status'] ?? 'pending'; ?>
                            <span class="px-3 py-1 text-sm rounded-full <?= $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $statusLabels[$status] ?? $status ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            <?= date('Y/m/d H:i', strtotime($order['created_at'])) ?>
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="text-left">
                            <p class="text-sm text-gray-500">المبلغ الإجمالي</p>
                            <p class="text-xl font-bold text-primary-600">
                                <?= number_format($order['total'] ?? 0, 2) ?> ر.س
                            </p>
                        </div>
                        
                        <a href="order.php?id=<?= $order['id'] ?>" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            التفاصيل
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8 space-x-2 space-x-reverse">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="orders.php?page=<?= $i ?>"
               class="px-4 py-2 rounded-lg <?= $i == $page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
