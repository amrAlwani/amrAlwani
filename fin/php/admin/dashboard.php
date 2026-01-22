<?php
/**
 * لوحة تحكم الأدمن - SwiftCart
 * صفحة مبسطة بدون استخدام MVC Controller
 */
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// التحقق من صلاحيات الأدمن
$userRole = $_SESSION['user_role'] ?? ($_SESSION['user']['role'] ?? 'user');
if ($userRole !== 'admin') {
    header('Location: ../dashboard.php?error=unauthorized');
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

$stats = [];
$recentOrders = [];
$topProducts = [];

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إحصائيات
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stats['total_users'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'");
    $stats['total_sales'] = (float)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['today_orders'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = (int)$stmt->fetchColumn();
    
    // آخر الطلبات
    $stmt = $db->query("
        SELECT o.*, u.name as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // المنتجات الأكثر مبيعاً
    $stmt = $db->query("
        SELECT * FROM products 
        ORDER BY sales_count DESC 
        LIMIT 5
    ");
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $stats = [
        'total_products' => 0,
        'total_orders' => 0,
        'total_users' => 0,
        'total_sales' => 0,
        'today_orders' => 0,
        'pending_orders' => 0
    ];
}

$userName = $_SESSION['user_name'] ?? 'الأدمن';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن - SwiftCart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white">
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold">🛒 SwiftCart Admin</h1>
            </div>
            <nav class="p-4 space-y-2">
                <a href="dashboard.php" class="flex items-center px-4 py-2 bg-primary-600 rounded-lg">
                    <span class="mr-2">📊</span> لوحة التحكم
                </a>
                <a href="products.php" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded-lg">
                    <span class="mr-2">📦</span> المنتجات
                </a>
                <a href="orders.php" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded-lg">
                    <span class="mr-2">🛍️</span> الطلبات
                </a>
                <a href="users.php" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded-lg">
                    <span class="mr-2">👥</span> المستخدمين
                </a>
                <a href="../categories.php" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded-lg">
                    <span class="mr-2">📁</span> التصنيفات
                </a>
                <hr class="border-gray-700 my-4">
                <a href="../index.php" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded-lg text-gray-400">
                    <span class="mr-2">🌐</span> عرض الموقع
                </a>
                <a href="../logout.php" class="flex items-center px-4 py-2 hover:bg-red-800 rounded-lg text-red-400">
                    <span class="mr-2">🚪</span> تسجيل الخروج
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">مرحباً، <?= htmlspecialchars($userName) ?> 👋</h1>
                <p class="text-gray-600">هذه لوحة تحكم الأدمن الخاصة بك</p>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">إجمالي المنتجات</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_products']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-2xl">📦</div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">إجمالي الطلبات</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_orders']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-2xl">🛍️</div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">إجمالي المستخدمين</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_users']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-2xl">👥</div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">إجمالي المبيعات</p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_sales'], 2) ?> ر.س</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center text-2xl">💰</div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Orders Alert -->
            <?php if ($stats['pending_orders'] > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0 text-2xl">⚠️</div>
                    <div class="mr-3">
                        <p class="text-yellow-700">
                            لديك <strong><?= $stats['pending_orders'] ?></strong> طلب في انتظار المعالجة!
                            <a href="orders.php?status=pending" class="underline hover:text-yellow-900">عرض الطلبات</a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-semibold">آخر الطلبات</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recentOrders)): ?>
                        <p class="text-gray-500 text-center py-4">لا توجد طلبات بعد</p>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentOrders as $order): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium">#<?= $order['id'] ?> - <?= htmlspecialchars($order['user_name'] ?? 'زائر') ?></p>
                                    <p class="text-sm text-gray-500"><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div class="text-left">
                                    <p class="font-bold text-primary-600"><?= number_format($order['total'] ?? 0, 2) ?> ر.س</p>
                                    <?php
                                    $statusColors = [
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
                                    $status = $order['status'] ?? 'pending';
                                    ?>
                                    <span class="text-xs px-2 py-1 rounded-full <?= $statusColors[$status] ?? 'bg-gray-100' ?>">
                                        <?= $statusLabels[$status] ?? $status ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Top Products -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-semibold">المنتجات الأكثر مبيعاً</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($topProducts)): ?>
                        <p class="text-gray-500 text-center py-4">لا توجد منتجات بعد</p>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($topProducts as $product): ?>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <img src="<?= htmlspecialchars($product['image'] ?? 'https://placehold.co/60x60') ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="w-12 h-12 rounded-lg object-cover">
                                <div class="mr-3 flex-1">
                                    <p class="font-medium"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= number_format($product['price'], 2) ?> ر.س</p>
                                </div>
                                <div class="text-left">
                                    <span class="text-sm bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        <?= $product['sales_count'] ?? 0 ?> مبيعات
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="products.php?action=add" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition">
                    <div class="text-3xl mb-2">➕</div>
                    <p class="font-medium">إضافة منتج</p>
                </a>
                <a href="orders.php?status=pending" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition">
                    <div class="text-3xl mb-2">📋</div>
                    <p class="font-medium">الطلبات المعلقة</p>
                </a>
                <a href="users.php" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition">
                    <div class="text-3xl mb-2">👤</div>
                    <p class="font-medium">إدارة المستخدمين</p>
                </a>
                <a href="../categories.php" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition">
                    <div class="text-3xl mb-2">📁</div>
                    <p class="font-medium">التصنيفات</p>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
