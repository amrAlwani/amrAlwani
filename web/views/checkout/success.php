<div class="max-w-2xl mx-auto text-center">
    <div class="bg-white rounded-lg p-12">
        <div class="text-6xl mb-6">✅</div>
        <h1 class="text-3xl font-bold text-green-600 mb-4">تم الطلب بنجاح!</h1>
        <p class="text-gray-600 mb-6">شكراً لك، تم استلام طلبك وسيتم معالجته قريباً</p>
        
        <div class="bg-gray-50 rounded-lg p-6 text-right mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500">رقم الطلب</p>
                    <p class="font-bold text-lg"><?= $order['order_number'] ?></p>
                </div>
                <div>
                    <p class="text-gray-500">تاريخ الطلب</p>
                    <p class="font-bold"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">طريقة الدفع</p>
                    <p class="font-bold"><?= $order['payment_method'] === 'cod' ? 'الدفع عند الاستلام' : 'تحويل بنكي' ?></p>
                </div>
                <div>
                    <p class="text-gray-500">الإجمالي</p>
                    <p class="font-bold text-blue-600 text-lg"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="text-right mb-6">
            <h3 class="font-bold mb-3">المنتجات</h3>
            <?php foreach ($order['items'] as $item): ?>
            <div class="flex justify-between py-2 border-b">
                <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                <span><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Shipping Address -->
        <?php 
        $address = is_string($order['shipping_address']) ? json_decode($order['shipping_address'], true) : $order['shipping_address'];
        if ($address): 
        ?>
        <div class="text-right mb-6">
            <h3 class="font-bold mb-3">عنوان التوصيل</h3>
            <p class="text-gray-600">
                <?= htmlspecialchars($address['name'] ?? '') ?><br>
                <?= htmlspecialchars($address['phone'] ?? '') ?><br>
                <?= htmlspecialchars($address['city'] ?? '') ?> - <?= htmlspecialchars($address['street'] ?? '') ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="flex gap-4 justify-center">
            <a href="<?= url('orders/' . $order['id']) ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                تفاصيل الطلب
            </a>
            <a href="<?= url('products') ?>" class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg hover:bg-blue-50">
                متابعة التسوق
            </a>
        </div>
    </div>
</div>
