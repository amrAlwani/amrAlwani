<div class="flex gap-4 mb-6">
    <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
           placeholder="ابحث عن منتج..." 
           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
    <select name="sort" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>الأحدث</option>
        <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>السعر: الأقل</option>
        <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>السعر: الأعلى</option>
        <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>الأكثر مبيعاً</option>
    </select>
</div>

<div class="flex gap-8">
    <!-- Sidebar Filters -->
    <aside class="w-64 hidden lg:block">
        <div class="bg-white rounded-lg p-4 sticky top-20">
            <h3 class="font-bold mb-4">التصنيفات</h3>
            <ul class="space-y-2">
                <li><a href="<?= url('products') ?>" class="hover:text-blue-600 <?= empty($filters['category_id']) ? 'text-blue-600 font-bold' : '' ?>">الكل</a></li>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?= url('products?category=' . $cat['id']) ?>" 
                       class="hover:text-blue-600 <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'text-blue-600 font-bold' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- Products Grid -->
    <div class="flex-1">
        <p class="text-gray-600 mb-4">عرض <?= count($products) ?> من <?= $total ?> منتج</p>
        
        <?php if (empty($products)): ?>
        <div class="bg-white rounded-lg p-12 text-center">
            <p class="text-gray-500 text-xl">لا توجد منتجات</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition group">
                <a href="<?= url('products/' . $product['slug']) ?>">
                    <div class="relative">
                        <img src="<?= $product['image'] ?? '/placeholder.svg' ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-48 object-cover group-hover:scale-105 transition">
                        <?php if (!empty($product['sale_price'])): ?>
                        <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs rounded">خصم</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium mb-2 line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="flex items-center gap-2">
                            <span class="text-blue-600 font-bold"><?= number_format($product['sale_price'] ?? $product['price'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
                            <?php if (!empty($product['sale_price'])): ?>
                            <span class="text-gray-400 line-through text-sm"><?= number_format($product['price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <div class="px-4 pb-4">
                    <form method="POST" action="<?= url('cart/add') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                            أضف للسلة
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($lastPage > 1): ?>
        <div class="flex justify-center gap-2 mt-8">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white rounded-lg hover:bg-gray-100">السابق</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $lastPage): ?>
            <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white rounded-lg hover:bg-gray-100">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
