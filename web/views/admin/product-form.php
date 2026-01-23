<div class="flex items-center gap-4 mb-6">
    <a href="<?= url('admin/products') ?>" class="text-gray-500 hover:text-gray-700">← العودة</a>
    <h1 class="text-2xl font-bold"><?= $product ? 'تعديل المنتج' : 'إضافة منتج جديد' ?></h1>
</div>

<form method="POST" class="max-w-4xl">
    <?= csrf_field() ?>
    
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">المعلومات الأساسية</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-2">اسم المنتج *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">الوصف الكامل</label>
                        <textarea name="description" rows="5"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">التسعير</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">السعر *</label>
                        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?? '' ?>" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">سعر العرض (اختياري)</label>
                        <input type="number" name="sale_price" step="0.01" value="<?= $product['sale_price'] ?? '' ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">المخزون</h2>
                <div>
                    <label class="block text-gray-700 mb-2">الكمية المتوفرة</label>
                    <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?? 0 ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">الحالة والإعدادات</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span>نشط (متاح في المتجر)</span>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">التصنيف</h2>
                <select name="category_id" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">-- اختر التصنيف --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bg-white rounded-lg p-6">
                <h2 class="font-bold mb-4">صورة المنتج</h2>
                <input type="text" name="image" value="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                       placeholder="رابط الصورة المباشر"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-bold">حفظ</button>
                <a href="<?= url('admin/products') ?>" class="flex-1 bg-gray-200 text-center py-3 rounded-lg hover:bg-gray-300">إلغاء</a>
            </div>
        </div>
    </div>
</form>