<h1 class="text-2xl font-bold mb-6">مرحباً <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">إجمالي الطلبات</p>
        <p class="text-3xl font-bold text-blue-600"><?= $stats['orders_count'] ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">طلبات معلقة</p>
        <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending_orders'] ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <p class="text-gray-500 text-sm">الإشعارات</p>
        <p class="text-3xl font-bold text-purple-600"><?= $unreadNotifications ?></p>
    </div>
    <div class="bg-white rounded-lg p-6">
        <a href="<?= url('wishlist') ?>" class="block">
            <p class="text-gray-500 text-sm">المفضلة</p>
            <p class="text-3xl font-bold text-red-600">❤️</p>
        </a>
    </div>
</div>

<!-- Quick Links -->
<div class="grid md:grid-cols-3 gap-4 mb-8">
    <a href="<?= url('orders') ?>" class="bg-white rounded-lg p-6 hover:shadow-lg transition flex items-center gap-4">
        <span class="text-3xl">📦</span>
        <div>
            <p class="font-bold">طلباتي</p>
            <p class="text-gray-500 text-sm">تتبع وإدارة طلباتك</p>
        </div>
    </a>
    <a href="<?= url('profile') ?>" class="bg-white rounded-lg p-6 hover:shadow-lg transition flex items-center gap-4">
        <span class="text-3xl">👤</span>
        <div>
            <p class="font-bold">الملف الشخصي</p>
            <p class="text-gray-500 text-sm">تعديل بياناتك</p>
        </div>
    </a>
    <a href="<?= url('addresses') ?>" class="bg-white rounded-lg p-6 hover:shadow-lg transition flex items-center gap-4">
        <span class="text-3xl">📍</span>
        <div>
            <p class="font-bold">عناويني</p>
            <p class="text-gray-500 text-sm">إدارة عناوين التوصيل</p>
        </div>
    </a>
</div>

<!-- Recent Orders -->
<?php if (!empty($recentOrders)): ?>
<div class="bg-white rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold">آخر الطلبات</h2>
        <a href="<?= url('orders') ?>" class="text-blue-600 hover:underline">عرض الكل</a>
    </div>
    <div class="space-y-3">
        <?php 
        $statusLabels = [
            'pending' => ['text' => 'قيد الانتظار', 'class' => 'text-yellow-600'],
            'processing' => ['text' => 'قيد المعالجة', 'class' => 'text-blue-600'],
            'shipped' => ['text' => 'تم الشحن', 'class' => 'text-purple-600'],
            'delivered' => ['text' => 'تم التوصيل', 'class' => 'text-green-600'],
            'cancelled' => ['text' => 'ملغي', 'class' => 'text-red-600'],
        ];
        foreach ($recentOrders as $order): 
            $status = $statusLabels[$order['status']] ?? $statusLabels['pending'];
        ?>
        <a href="<?= url('orders/' . $order['id']) ?>" class="flex justify-between items-center py-3 border-b hover:bg-gray-50 px-2 -mx-2 rounded">
            <div>
                <span class="font-medium">#<?= $order['order_number'] ?></span>
                <span class="text-gray-500 text-sm mr-2"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="<?= $status['class'] ?> text-sm"><?= $status['text'] ?></span>
                <span class="font-bold"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
