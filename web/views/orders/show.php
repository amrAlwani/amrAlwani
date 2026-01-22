<?php
$statusLabels = [
    'pending' => ['text' => 'قيد الانتظار', 'class' => 'bg-yellow-100 text-yellow-800'],
    'processing' => ['text' => 'قيد المعالجة', 'class' => 'bg-blue-100 text-blue-800'],
    'shipped' => ['text' => 'تم الشحن', 'class' => 'bg-purple-100 text-purple-800'],
    'delivered' => ['text' => 'تم التوصيل', 'class' => 'bg-green-100 text-green-800'],
    'cancelled' => ['text' => 'ملغي', 'class' => 'bg-red-100 text-red-800'],
];
$status = $statusLabels[$order['status']] ?? $statusLabels['pending'];
$address = is_string($order['shipping_address']) ? json_decode($order['shipping_address'], true) : $order['shipping_address'];
?>

<div class="flex items-center gap-4 mb-6">
    <a href="<?= url('orders') ?>" class="text-gray-500 hover:text-gray-700">← العودة للطلبات</a>
    <h1 class="text-2xl font-bold">الطلب #<?= $order['order_number'] ?></h1>
    <span class="px-3 py-1 rounded-full text-sm <?= $status['class'] ?>"><?= $status['text'] ?></span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Order Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">المنتجات</h2>
            <div class="space-y-4">
                <?php foreach ($order['items'] as $item): ?>
                <div class="flex gap-4">
                    <img src="<?= $item['image'] ?? '/placeholder.svg' ?>" class="w-20 h-20 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-medium"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="text-gray-500">الكمية: <?= $item['quantity'] ?></p>
                        <p class="text-blue-600 font-bold"><?= number_format($item['price'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
                    </div>
                    <p class="font-bold"><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Shipping Address -->
        <?php if ($address): ?>
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">عنوان التوصيل</h2>
            <p class="font-medium"><?= htmlspecialchars($address['name'] ?? '') ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($address['phone'] ?? '') ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($address['city'] ?? '') ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($address['street'] ?? '') ?></p>
        </div>
        <?php endif; ?>

        <!-- Order Timeline -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">حالة الطلب</h2>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5"></div>
                    <div>
                        <p class="font-medium">تم استلام الطلب</p>
                        <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>
                <?php if ($order['shipped_at']): ?>
                <div class="flex gap-4">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mt-1.5"></div>
                    <div>
                        <p class="font-medium">تم الشحن</p>
                        <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($order['shipped_at'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($order['delivered_at']): ?>
                <div class="flex gap-4">
                    <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5"></div>
                    <div>
                        <p class="font-medium">تم التوصيل</p>
                        <p class="text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($order['delivered_at'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="bg-white rounded-lg p-6 h-fit">
        <h2 class="font-bold mb-4">ملخص الطلب</h2>
        
        <div class="space-y-3 text-gray-600">
            <div class="flex justify-between">
                <span>تاريخ الطلب</span>
                <span><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="flex justify-between">
                <span>طريقة الدفع</span>
                <span><?= $order['payment_method'] === 'cod' ? 'عند الاستلام' : 'تحويل بنكي' ?></span>
            </div>
            <hr>
            <div class="flex justify-between">
                <span>المجموع الفرعي</span>
                <span><?= number_format($order['subtotal'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
            <div class="flex justify-between text-green-600">
                <span>الخصم</span>
                <span>-<?= number_format($order['discount'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <span>الشحن</span>
                <span><?= $order['shipping_cost'] > 0 ? number_format($order['shipping_cost'], 2) . ' ' . CURRENCY_SYMBOL : 'مجاني' ?></span>
            </div>
            <div class="flex justify-between">
                <span>الضريبة</span>
                <span><?= number_format($order['tax'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <hr>
            <div class="flex justify-between text-lg font-bold">
                <span>الإجمالي</span>
                <span class="text-blue-600"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
        </div>
    </div>
</div>
