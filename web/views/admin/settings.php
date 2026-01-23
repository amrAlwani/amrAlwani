<h1 class="text-2xl font-bold mb-6">الإعدادات</h1>

<form method="POST" action="<?= url('admin/settings') ?>" class="max-w-2xl">
    <?= csrf_field() ?>
    
    <div class="space-y-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">الإعدادات العامة</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">اسم المتجر</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? APP_NAME) ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">وصف المتجر</label>
                    <textarea name="site_description" rows="2"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">العملة</label>
                        <input type="text" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'SAR') ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">رمز العملة</label>
                        <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'ر.س') ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax & Shipping -->
        <div class="bg-white rounded-lg p-6">
            <h2 class="font-bold mb-4">الضريبة والشحن</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">نسبة الضريبة (%)</label>
                    <input type="number" name="tax_rate" step="0.01" value="<?= $settings['tax_rate'] ?? 15 ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">تكلفة الشحن</label>
                    <input type="number" name="shipping_cost" step="0.01" value="<?= $settings['shipping_cost'] ?? 25 ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">حد الشحن المجاني</label>
                    <input type="number" name="free_shipping_threshold" step="0.01" value="<?= $settings['free_shipping_threshold'] ?? 500 ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">الحد الأدنى للطلب</label>
                    <input type="number" name="min_order_value" step="0.01" value="<?= $settings['min_order_value'] ?? 50 ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg mt-6 hover:bg-blue-700 font-bold">
        حفظ الإعدادات
    </button>
</form>
