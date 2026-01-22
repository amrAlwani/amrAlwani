<!-- Admin Products List -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="بحث..." class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            <select name="category" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">كل التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <a href="?action=create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
        <i class="fas fa-plus"></i>
        إضافة منتج
    </a>
</div>

<!-- Products Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المنتج</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التصنيف</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">السعر</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المخزون</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-box-open text-4xl mb-4"></i>
                        <p>لا توجد منتجات</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                                    <?php 
                                        $images = is_array($product['images']) ? $product['images'] : [];
                                        $image = !empty($images) ? $images[0] : ($product['image'] ?? '');
                                    ?>
                                    <?php if ($image): ?>
                                        <img src="<?= htmlspecialchars($image) ?>" alt="" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-sm text-gray-500">SKU: <?= htmlspecialchars($product['sku'] ?? '-') ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <?= htmlspecialchars($product['category_name'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (!empty($product['sale_price'])): ?>
                                <p class="font-medium text-green-600"><?= number_format($product['sale_price'], 2) ?> ر.س</p>
                                <p class="text-sm text-gray-400 line-through"><?= number_format($product['price'], 2) ?> ر.س</p>
                            <?php else: ?>
                                <p class="font-medium"><?= number_format($product['price'], 2) ?> ر.س</p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                                $stock = $product['stock_quantity'] ?? 0;
                                $stockClass = $stock > 10 ? 'text-green-600' : ($stock > 0 ? 'text-yellow-600' : 'text-red-600');
                            ?>
                            <span class="<?= $stockClass ?> font-medium"><?= $stock ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($product['is_active']): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">نشط</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">غير نشط</span>
                            <?php endif; ?>
                            <?php if (!empty($product['is_featured'])): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs mr-1">مميز</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="?action=edit&id=<?= $product['id'] ?>" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=delete&id=<?= $product['id'] ?>" 
                                   class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="حذف"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6">
        <nav class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                   class="px-4 py-2 border rounded-lg hover:bg-gray-100">السابق</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                   class="px-4 py-2 border rounded-lg <?= $i === $page ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                   class="px-4 py-2 border rounded-lg hover:bg-gray-100">التالي</a>
            <?php endif; ?>
        </nav>
    </div>
<?php endif; ?>
