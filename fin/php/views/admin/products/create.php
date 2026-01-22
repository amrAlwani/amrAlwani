<!-- Admin Create Product -->
<form action="?action=store" method="POST" enctype="multipart/form-data" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">معلومات المنتج الأساسية</h3>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">اسم المنتج *</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                       placeholder="أدخل اسم المنتج">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">رمز المنتج (SKU)</label>
                <input type="text" name="sku"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                       placeholder="مثال: PRD-001">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">وصف مختصر</label>
            <input type="text" name="short_description"
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                   placeholder="وصف مختصر يظهر في قائمة المنتجات">
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف التفصيلي</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                      placeholder="أدخل وصفاً تفصيلياً للمنتج"></textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">التسعير والمخزون</h3>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">السعر (ر.س) *</label>
                <input type="number" name="price" step="0.01" min="0" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                       placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">سعر التخفيض (ر.س)</label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                       placeholder="0.00">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الكمية المتوفرة *</label>
                <input type="number" name="stock_quantity" min="0" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                       placeholder="0">
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
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">صور المنتج</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <p class="text-sm text-gray-500 mt-1">يمكنك رفع عدة صور (JPG, PNG, GIF, WebP)</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-lg mb-4 border-b pb-2">خيارات العرض</h3>
        
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked
                       class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                <span>منتج نشط (يظهر في المتجر)</span>
            </label>
            
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                       class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                <span>منتج مميز (يظهر في الصفحة الرئيسية)</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
            <i class="fas fa-save"></i>
            حفظ المنتج
        </button>
        <a href="/admin/products.php" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            إلغاء
        </a>
    </div>
</form>
