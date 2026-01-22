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
    <a href="<?= url('admin/orders') ?>" class="text-gray-500 hover:text-gray-700">← العودة</a>
    <h1 class="text-2xl font-bold">الطلب #<?= $order['order_number'] ?></h1>
    <span class="px-3 py-1 rounded-full text-sm <?= $status['class'] ?>"><?= $status['text'] ?></span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Update Status -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">تحديث الحالة</h2>
            <form method="POST" action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" class="flex gap-4">
                <?= csrf_field() ?>
                <select name="status" class="flex-1 px-4 py-2 border rounded-lg">
                    <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $label['text'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">تحديث</button>
            </form>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">المنتجات</h2>
            <div class="space-y-3">
                <?php foreach ($order['items'] as $item): ?>
                <div class="flex gap-4 py-3 border-b">
                    <img src="<?= $item['image'] ?? '/placeholder.svg' ?>" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <p class="font-medium"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-gray-500">الكمية: <?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?></p>
                    </div>
                    <p class="font-bold"><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Shipping -->
        <?php if ($address): ?>
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">عنوان التوصيل</h2>
            <p><strong><?= htmlspecialchars($address['name'] ?? '') ?></strong></p>
            <p><?= htmlspecialchars($address['phone'] ?? '') ?></p>
            <p><?= htmlspecialchars($address['city'] ?? '') ?> - <?= htmlspecialchars($address['street'] ?? '') ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Order Summary -->
    <div class="bg-white rounded-lg p-6 h-fit">
        <h2 class="font-bold mb-4">ملخص الطلب</h2>
        <div class="space-y-2 text-gray-600">
            <div class="flex justify-between">
                <span>المجموع الفرعي</span>
                <span><?= number_format($order['subtotal'], 2) ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
            <div class="flex justify-between text-green-600">
                <span>الخصم</span>
                <span>-<?= number_format($order['discount'], 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <span>الشحن</span>
                <span><?= number_format($order['shipping_cost'], 2) ?></span>
            </div>
            <div class="flex justify-between">
                <span>الضريبة</span>
                <span><?= number_format($order['tax'], 2) ?></span>
            </div>
            <hr>
            <div class="flex justify-between font-bold text-lg">
                <span>الإجمالي</span>
                <span class="text-blue-600"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="space-y-2 text-sm">
            <p><strong>طريقة الدفع:</strong> <?= $order['payment_method'] === 'cod' ? 'عند الاستلام' : 'تحويل بنكي' ?></p>
            <p><strong>تاريخ الطلب:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
    </div>
</div>
