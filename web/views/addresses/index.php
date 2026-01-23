<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">عناويني</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + إضافة عنوان
    </button>
</div>

<?php if (empty($addresses)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-6xl mb-4">📍</p>
    <p class="text-gray-500 text-xl mb-6">لا توجد عناوين محفوظة</p>
</div>
<?php else: ?>
<div class="grid md:grid-cols-2 gap-4">
    <?php foreach ($addresses as $address): ?>
    <div class="bg-white rounded-lg p-6 <?= $address['is_default'] ? 'border-2 border-blue-500' : '' ?>">
        <?php if ($address['is_default']): ?>
        <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded mb-2 inline-block">الافتراضي</span>
        <?php endif; ?>
        <h3 class="font-bold"><?= htmlspecialchars($address['name']) ?></h3>
        <p class="text-gray-600"><?= htmlspecialchars($address['phone']) ?></p>
        <p class="text-gray-600"><?= htmlspecialchars($address['city']) ?> - <?= htmlspecialchars($address['district'] ?? '') ?></p>
        <p class="text-gray-600"><?= htmlspecialchars($address['street']) ?></p>
        
        <div class="flex gap-2 mt-4">
            <a href="<?= url('addresses/' . $address['id'] . '/delete') ?>" 
               onclick="return confirm('هل تريد حذف هذا العنوان؟')"
               class="text-red-500 hover:text-red-700">حذف</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add Address Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">إضافة عنوان جديد</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?= url('addresses') ?>">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-gray-700 mb-1">الاسم</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">الهاتف</label>
                    <input type="tel" name="phone" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">المدينة</label>
                    <input type="text" name="city" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">الحي</label>
                    <input type="text" name="district" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">المبنى</label>
                    <input type="text" name="building" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="col-span-2">
                    <label class="block text-gray-700 mb-1">العنوان التفصيلي</label>
                    <input type="text" name="street" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_default" value="1">
                        <span>تعيين كعنوان افتراضي</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg mt-4 hover:bg-blue-700">
                حفظ العنوان
            </button>
        </form>
    </div>
</div>
