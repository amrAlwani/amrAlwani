<h1 class="text-2xl font-bold mb-6">سلة التسوق</h1>

<?php if (empty($cart['items'])): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-6xl mb-4">🛒</p>
    <p class="text-gray-500 text-xl mb-6">سلتك فارغة</p>
    <a href="<?= url('products') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg inline-block">تسوق الآن</a>
</div>
<?php else: ?>
<div class="grid lg:grid-cols-3 gap-8">
    <!-- Cart Items -->
    <div class="lg:col-span-2 space-y-4">
        <?php foreach ($cart['items'] as $item): ?>
        <div class="bg-white rounded-lg p-4 flex gap-4">
            <img src="<?= $item['image'] ?? '/placeholder.svg' ?>" alt="" class="w-24 h-24 object-cover rounded">
            <div class="flex-1">
                <h3 class="font-medium"><?= htmlspecialchars($item['name']) ?></h3>
                <p class="text-blue-600 font-bold"><?= number_format($item['price'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
                
                <div class="flex items-center gap-4 mt-2">
                    <form method="POST" action="<?= url('cart/update') ?>" class="flex items-center">
                        <?= csrf_field() ?>
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <div class="flex items-center border rounded">
                            <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="px-3 py-1 hover:bg-gray-100">-</button>
                            <span class="px-3"><?= $item['quantity'] ?></span>
                            <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="px-3 py-1 hover:bg-gray-100">+</button>
                        </div>
                    </form>
                    <a href="<?= url('cart/remove/' . $item['id']) ?>" class="text-red-500 hover:text-red-700">🗑️ حذف</a>
                </div>
            </div>
            <div class="text-left">
                <p class="font-bold"><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Order Summary -->
    <div class="bg-white rounded-lg p-6 h-fit sticky top-20">
        <h2 class="text-xl font-bold mb-4">ملخص الطلب</h2>
        
        <div class="space-y-3 text-gray-600">
            <div class="flex justify-between">
                <span>المجموع الفرعي</span>
                <span><?= number_format($cart['subtotal'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <div class="flex justify-between">
                <span>الضريبة (<?= TAX_RATE * 100 ?>%)</span>
                <span><?= number_format($cart['tax'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <div class="flex justify-between">
                <span>الشحن</span>
                <span><?= $cart['shipping'] > 0 ? number_format($cart['shipping'], 2) . ' ' . CURRENCY_SYMBOL : 'مجاني' ?></span>
            </div>
            <?php if (!empty($cart['discount'])): ?>
            <div class="flex justify-between text-green-600">
                <span>الخصم</span>
                <span>-<?= number_format($cart['discount'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
            <?php endif; ?>
            <hr>
            <div class="flex justify-between text-lg font-bold">
                <span>الإجمالي</span>
                <span class="text-blue-600"><?= number_format($cart['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
            </div>
        </div>

        <!-- Coupon -->
        <form method="POST" action="<?= url('cart/coupon') ?>" class="mt-4">
            <?= csrf_field() ?>
            <div class="flex gap-2">
                <input type="text" name="coupon_code" placeholder="كود الخصم" 
                       class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">تطبيق</button>
            </div>
        </form>

        <a href="<?= url('checkout') ?>" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg mt-6 hover:bg-blue-700 font-bold">
            إتمام الطلب
        </a>
        
        <a href="<?= url('products') ?>" class="block text-center text-blue-600 mt-4 hover:underline">
            متابعة التسوق
        </a>
    </div>
</div>
<?php endif; ?>
