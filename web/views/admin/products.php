<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">إدارة المنتجات</h1>
    <a href="<?= url('admin/products/create') ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        + إضافة منتج
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg p-4 mb-6 flex flex-wrap gap-4">
    <form class="flex flex-1 gap-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="بحث..."
               class="flex-1 px-4 py-2 border rounded-lg">
        <select name="category" class="px-4 py-2 border rounded-lg">
            <option value="">كل التصنيفات</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300">بحث</button>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">المنتج</th>
                <th class="text-right px-4 py-3">التصنيف</th>
                <th class="text-right px-4 py-3">السعر</th>
                <th class="text-right px-4 py-3">المخزون</th>
                <th class="text-right px-4 py-3">الحالة</th>
                <th class="text-right px-4 py-3">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="<?= $product['image'] ?? '/placeholder.svg' ?>" class="w-12 h-12 object-cover rounded">
                        <div>
                            <p class="font-medium"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="text-gray-500 text-sm"><?= $product['sku'] ?? '-' ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3"><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                <td class="px-4 py-3">
                    <?= number_format($product['sale_price'] ?? $product['price'], 2) ?>
                    <?php if (!empty($product['sale_price'])): ?>
                    <span class="text-gray-400 line-through text-sm"><?= number_format($product['price'], 2) ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <span class="<?= ($product['stock_quantity'] ?? 0) <= 5 ? 'text-red-600' : 'text-green-600' ?>">
                        <?= $product['stock_quantity'] ?? 0 ?>
                    </span>
                </td>
                <td class="px-4 py-3">
                    <?php if ($product['is_active']): ?>
                    <span class="text-green-600">نشط</span>
                    <?php else: ?>
                    <span class="text-red-600">معطل</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <a href="<?= url('admin/products/' . $product['id'] . '/edit') ?>" class="text-blue-600 hover:underline ml-2">تعديل</a>
                    <a href="<?= url('admin/products/' . $product['id'] . '/delete') ?>" 
                       onclick="return confirm('هل تريد حذف هذا المنتج؟')"
                       class="text-red-600 hover:underline">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
