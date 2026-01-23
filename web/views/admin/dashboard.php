<h1 class="text-2xl font-bold mb-6">لوحة التحكم</h1>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">إجمالي الإيرادات</p>
        <p class="text-2xl font-bold text-green-600"><?= number_format($stats['total_revenue'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">إجمالي الطلبات</p>
        <p class="text-2xl font-bold text-blue-600"><?= $stats['total_orders'] ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">المنتجات</p>
        <p class="text-2xl font-bold text-purple-600"><?= $stats['total_products'] ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">المستخدمين</p>
        <p class="text-2xl font-bold text-orange-600"><?= $stats['total_users'] ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">آخر الطلبات</h2>
            <a href="<?= url('admin/orders') ?>" class="text-blue-600 hover:underline text-sm">عرض الكل</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-right py-2">رقم الطلب</th>
                        <th class="text-right py-2">العميل</th>
                        <th class="text-right py-2">المبلغ</th>
                        <th class="text-right py-2">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $statusLabels = [
                        'pending' => ['text' => 'معلق', 'class' => 'text-yellow-600'],
                        'processing' => ['text' => 'معالجة', 'class' => 'text-blue-600'],
                        'shipped' => ['text' => 'شحن', 'class' => 'text-purple-600'],
                        'delivered' => ['text' => 'تم', 'class' => 'text-green-600'],
                        'cancelled' => ['text' => 'ملغي', 'class' => 'text-red-600'],
                    ];
                    foreach ($recentOrders as $order): 
                        $status = $statusLabels[$order['status']] ?? $statusLabels['pending'];
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2">#<?= $order['order_number'] ?></td>
                        <td class="py-2"><?= htmlspecialchars($order['user_name'] ?? 'مجهول') ?></td>
                        <td class="py-2"><?= number_format($order['total'], 2) ?></td>
                        <td class="py-2 <?= $status['class'] ?>"><?= $status['text'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">آخر المستخدمين</h2>
            <a href="<?= url('admin/users') ?>" class="text-blue-600 hover:underline text-sm">عرض الكل</a>
        </div>
        <div class="space-y-3">
            <?php foreach ($recentUsers as $user): ?>
            <div class="flex items-center gap-3 py-2 border-b">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                    <?= mb_substr($user['name'], 0, 1) ?>
                </div>
                <div class="flex-1">
                    <p class="font-medium"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <span class="text-gray-400 text-sm"><?= date('d/m', strtotime($user['created_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
