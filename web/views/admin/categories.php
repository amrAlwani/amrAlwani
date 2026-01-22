<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">إدارة التصنيفات</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + إضافة تصنيف
    </button>
</div>

<div class="bg-white rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">التصنيف</th>
                <th class="text-right px-4 py-3">الوصف</th>
                <th class="text-right px-4 py-3">عدد المنتجات</th>
                <th class="text-right px-4 py-3">الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            function renderCategory($cat, $level = 0) {
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3">
                    <?= $indent ?>
                    <?= $level > 0 ? '└ ' : '' ?>
                    <span class="text-xl ml-2"><?= $cat['icon'] ?? '📦' ?></span>
                    <?= htmlspecialchars($cat['name']) ?>
                </td>
                <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                <td class="px-4 py-3"><?= $cat['product_count'] ?? 0 ?></td>
                <td class="px-4 py-3">
                    <?php if ($cat['is_active']): ?>
                    <span class="text-green-600">نشط</span>
                    <?php else: ?>
                    <span class="text-red-600">معطل</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
                if (!empty($cat['children'])) {
                    foreach ($cat['children'] as $child) {
                        renderCategory($child, $level + 1);
                    }
                }
            }
            foreach ($categories as $cat) {
                renderCategory($cat);
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">إضافة تصنيف جديد</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?= url('admin/categories') ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">اسم التصنيف *</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">الوصف</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">التصنيف الأب</label>
                    <select name="parent_id" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">-- تصنيف رئيسي --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg mt-4 hover:bg-blue-700">
                حفظ
            </button>
        </form>
    </div>
</div>
