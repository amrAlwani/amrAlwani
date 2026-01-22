<!-- Admin Dashboard View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Products -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">إجمالي المنتجات</p>
                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_products']) ?></p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <i class="fas fa-box text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">إجمالي الطلبات</p>
                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_orders']) ?></p>
                <p class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-up"></i>
                    <?= $stats['today_orders'] ?> اليوم
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">المستخدمين</p>
                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_users']) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Sales -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">إجمالي المبيعات</p>
                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_sales'], 2) ?></p>
                <p class="text-xs text-gray-500">ر.س</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-coins text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Orders Alert -->
<?php if ($stats['pending_orders'] > 0): ?>
<div class="bg-orange-50 border-r-4 border-orange-500 p-4 rounded-lg mb-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-orange-500 text-xl ml-3"></i>
        <div>
            <p class="font-semibold text-orange-800">طلبات تحتاج مراجعة</p>
            <p class="text-orange-600">لديك <?= $stats['pending_orders'] ?> طلب في انتظار المعالجة</p>
        </div>
        <a href="/admin/orders.php?status=pending" class="mr-auto bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            عرض الطلبات
        </a>
    </div>
</div>
<?php endif; ?>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="font-semibold text-lg">آخر الطلبات</h3>
            <a href="/admin/orders.php" class="text-indigo-600 hover:underline text-sm">عرض الكل</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentOrders)): ?>
                <p class="text-gray-500 text-center py-8">لا توجد طلبات حتى الآن</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">#<?= $order['id'] ?> - <?= htmlspecialchars($order['user_name'] ?? 'زائر') ?></p>
                                <p class="text-sm text-gray-500"><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-indigo-600"><?= number_format($order['total'], 2) ?> ر.س</p>
                                <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'processing' => 'bg-blue-100 text-blue-700',
                                        'shipped' => 'bg-purple-100 text-purple-700',
                                        'delivered' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'قيد الانتظار',
                                        'processing' => 'قيد المعالجة',
                                        'shipped' => 'تم الشحن',
                                        'delivered' => 'تم التوصيل',
                                        'cancelled' => 'ملغي',
                                    ];
                                ?>
                                <span class="text-xs px-2 py-1 rounded-full <?= $statusColors[$order['status']] ?? 'bg-gray-100' ?>">
                                    <?= $statusLabels[$order['status']] ?? $order['status'] ?>
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
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="font-semibold text-lg">المنتجات الأكثر مبيعاً</h3>
            <a href="/admin/products.php" class="text-indigo-600 hover:underline text-sm">عرض الكل</a>
        </div>
        <div class="p-6">
            <?php if (empty($topProducts)): ?>
                <p class="text-gray-500 text-center py-8">لا توجد منتجات حتى الآن</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($topProducts as $index => $product): ?>
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                                <?= $index + 1 ?>
                            </span>
                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium"><?= htmlspecialchars($product['name']) ?></p>
                                <p class="text-sm text-gray-500"><?= number_format($product['price'], 2) ?> ر.س</p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-green-600"><?= number_format($product['sales'] ?? 0) ?></p>
                                <p class="text-xs text-gray-500">مبيعات</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6 bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-lg mb-4">إجراءات سريعة</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="/admin/products.php?action=create" class="flex flex-col items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
            <i class="fas fa-plus-circle text-2xl text-indigo-600 mb-2"></i>
            <span class="text-sm font-medium">إضافة منتج</span>
        </a>
        <a href="/admin/orders.php?status=pending" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
            <i class="fas fa-clock text-2xl text-orange-600 mb-2"></i>
            <span class="text-sm font-medium">الطلبات المعلقة</span>
        </a>
        <a href="/admin/users.php" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
            <i class="fas fa-user-plus text-2xl text-blue-600 mb-2"></i>
            <span class="text-sm font-medium">المستخدمين</span>
        </a>
        <a href="/admin/categories.php" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
            <i class="fas fa-tags text-2xl text-green-600 mb-2"></i>
            <span class="text-sm font-medium">التصنيفات</span>
        </a>
    </div>
</div>
