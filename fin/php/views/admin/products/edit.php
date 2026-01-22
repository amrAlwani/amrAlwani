<!-- Admin Edit Product -->
<form action="?action=update&id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">معلومات المنتج الأساسية</h3>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">اسم المنتج *</label>
                <input type="text" name="name" required
                       value="<?= htmlspecialchars($product['name']) ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">رمز المنتج (SKU)</label>
                <input type="text" name="sku"
                       value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">وصف مختصر</label>
            <input type="text" name="short_description"
                   value="<?= htmlspecialchars($product['short_description'] ?? '') ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف التفصيلي</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">التسعير والمخزون</h3>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">السعر (ر.س) *</label>
                <input type="number" name="price" step="0.01" min="0" required
                       value="<?= $product['price'] ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">سعر التخفيض (ر.س)</label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       value="<?= $product['sale_price'] ?? '' ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الكمية المتوفرة *</label>
                <input type="number" name="stock_quantity" min="0" required
                       value="<?= $product['stock_quantity'] ?? 0 ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">التصنيف والصور</h3>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">التصنيف</label>
                <select name="category_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- اختر التصنيف --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إضافة صور جديدة</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Current Images -->
        <?php if (!empty($product['images'])): ?>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">الصور الحالية</label>
                <div class="flex gap-4 flex-wrap">
                    <?php foreach ($product['images'] as $image): ?>
                        <div class="relative">
                            <img src="<?= htmlspecialchars($image) ?>" alt="" 
                                 class="w-24 h-24 object-cover rounded-lg border">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">خيارات العرض</h3>
        
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>
                       class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                <span>منتج نشط (يظهر في المتجر)</span>
            </label>
            
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" <?= ($product['is_featured'] ?? false) ? 'checked' : '' ?>
                       class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                <span>منتج مميز (يظهر في الصفحة الرئيسية)</span>
            </label>
        </div>
    </div>

    <!-- Statistics -->
    <div class="bg-gray-50 rounded-xl p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4">إحصائيات المنتج</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-indigo-600"><?= number_format($product['views_count'] ?? 0) ?></p>
                <p class="text-sm text-gray-500">المشاهدات</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600"><?= number_format($product['sales_count'] ?? 0) ?></p>
                <p class="text-sm text-gray-500">المبيعات</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-yellow-600"><?= number_format($product['reviews_count'] ?? 0) ?></p>
                <p class="text-sm text-gray-500">التقييمات</p>
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
            <i class="fas fa-save"></i>
            حفظ التعديلات
        </button>
        <a href="/admin/products.php" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            إلغاء
        </a>
    </div>
</form>
