<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">إدارة الكوبونات</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + إضافة كوبون
    </button>
</div>

<div class="bg-white rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">الكود</th>
                <th class="text-right px-4 py-3">النوع</th>
                <th class="text-right px-4 py-3">القيمة</th>
                <th class="text-right px-4 py-3">الاستخدامات</th>
                <th class="text-right px-4 py-3">تاريخ الانتهاء</th>
                <th class="text-right px-4 py-3">الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $coupon): ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-mono font-bold"><?= htmlspecialchars($coupon['code']) ?></td>
                <td class="px-4 py-3"><?= $coupon['type'] === 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت' ?></td>
                <td class="px-4 py-3">
                    <?= number_format($coupon['value'], 2) ?>
                    <?= $coupon['type'] === 'percentage' ? '%' : CURRENCY_SYMBOL ?>
                </td>
                <td class="px-4 py-3">
                    <?= $coupon['used_count'] ?>
                    <?php if ($coupon['max_uses']): ?>
                    / <?= $coupon['max_uses'] ?>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3"><?= $coupon['end_date'] ? date('d/m/Y', strtotime($coupon['end_date'])) : '-' ?></td>
                <td class="px-4 py-3">
                    <?php if ($coupon['is_active']): ?>
                    <span class="text-green-600">نشط</span>
                    <?php else: ?>
                    <span class="text-red-600">معطل</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">إضافة كوبون جديد</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?= url('admin/coupons') ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">كود الكوبون *</label>
                    <input type="text" name="code" required class="w-full px-4 py-2 border rounded-lg uppercase">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">النوع</label>
                        <select name="type" class="w-full px-4 py-2 border rounded-lg">
                            <option value="fixed">مبلغ ثابت</option>
                            <option value="percentage">نسبة مئوية</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">القيمة *</label>
                        <input type="number" name="value" step="0.01" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">الحد الأدنى للطلب</label>
                        <input type="number" name="min_order_amount" step="0.01" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">الحد الأقصى للخصم</label>
                        <input type="number" name="max_discount" step="0.01" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">تاريخ البداية</label>
                        <input type="date" name="start_date" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">تاريخ الانتهاء</label>
                        <input type="date" name="end_date" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">الحد الأقصى للاستخدامات</label>
                    <input type="number" name="max_uses" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg mt-4 hover:bg-blue-700">
                حفظ الكوبون
            </button>
        </form>
    </div>
</div>
