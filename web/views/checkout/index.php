<h1 class="text-2xl font-bold mb-6">إتمام الطلب</h1>

<form method="POST" action="<?= url('checkout') ?>">
    <?= csrf_field() ?>
    
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Shipping Address -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Saved Addresses -->
            <?php if (!empty($addresses)): ?>
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">العناوين المحفوظة</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($addresses as $address): ?>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 <?= $address['is_default'] ? 'border-blue-500 bg-blue-50' : '' ?>">
                        <input type="radio" name="address_id" value="<?= $address['id'] ?>" 
                               <?= $address['is_default'] ? 'checked' : '' ?> class="hidden peer">
                        <div class="peer-checked:text-blue-600">
                            <p class="font-medium"><?= htmlspecialchars($address['name']) ?></p>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars($address['phone']) ?></p>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars($address['city']) ?> - <?= htmlspecialchars($address['street']) ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- New Address -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4"><?= empty($addresses) ? 'عنوان الشحن' : 'أو أضف عنوان جديد' ?></h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" name="name" value="<?= old('name', $_SESSION['user']['name'] ?? '') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">رقم الهاتف *</label>
                        <input type="tel" name="phone" value="<?= old('phone', $_SESSION['user']['phone'] ?? '') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">المدينة *</label>
                        <input type="text" name="city" value="<?= old('city') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">الحي</label>
                        <input type="text" name="district" value="<?= old('district') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 mb-2">العنوان التفصيلي *</label>
                        <input type="text" name="street" value="<?= old('street') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">المبنى</label>
                        <input type="text" name="building" value="<?= old('building') ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 mt-8">
                            <input type="checkbox" name="save_address" value="1" class="rounded">
                            <span>حفظ العنوان للطلبات القادمة</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">طريقة الدفع</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:border-blue-500">
                        <input type="radio" name="payment_method" value="cod" checked class="text-blue-600">
                        <span>💵 الدفع عند الاستلام</span>
                    </label>
                    <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:border-blue-500">
                        <input type="radio" name="payment_method" value="bank" class="text-blue-600">
                        <span>🏦 تحويل بنكي</span>
                    </label>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">ملاحظات (اختياري)</h2>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                          placeholder="ملاحظات خاصة بالطلب أو التوصيل..."></textarea>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg p-6 h-fit sticky top-20">
            <h2 class="text-lg font-bold mb-4">ملخص الطلب</h2>
            
            <div class="space-y-3 mb-4">
                <?php foreach ($cart['items'] as $item): ?>
                <div class="flex gap-3">
                    <img src="<?= $item['image'] ?? '/placeholder.svg' ?>" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <p class="text-sm"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-gray-500 text-sm"><?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?></p>
                    </div>
                    <p class="font-medium"><?= number_format($item['total'], 2) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <hr class="my-4">

            <div class="space-y-2 text-gray-600">
                <div class="flex justify-between">
                    <span>المجموع الفرعي</span>
                    <span><?= number_format($cart['subtotal'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
                </div>
                <div class="flex justify-between">
                    <span>الضريبة</span>
                    <span><?= number_format($cart['tax'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
                </div>
                <div class="flex justify-between">
                    <span>الشحن</span>
                    <span><?= $cart['shipping'] > 0 ? number_format($cart['shipping'], 2) . ' ' . CURRENCY_SYMBOL : 'مجاني' ?></span>
                </div>
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>الإجمالي</span>
                    <span class="text-blue-600"><?= number_format($cart['total'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg mt-6 hover:bg-blue-700 font-bold">
                تأكيد الطلب
            </button>
            
            <p class="text-center text-gray-500 text-sm mt-4">
                بالضغط على "تأكيد الطلب" أنت توافق على شروط الاستخدام
            </p>
        </div>
    </div>
</form>
