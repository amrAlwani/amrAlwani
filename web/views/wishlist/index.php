<h1 class="text-2xl font-bold mb-6">المفضلة</h1>

<?php if (empty($wishlist)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-6xl mb-4">❤️</p>
    <p class="text-gray-500 text-xl mb-6">قائمة المفضلة فارغة</p>
    <a href="<?= url('products') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg inline-block">تصفح المنتجات</a>
</div>
<?php else: ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <?php foreach ($wishlist as $item): ?>
    <div class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition">
        <a href="<?= url('products/' . $item['slug']) ?>">
            <img src="<?= $item['image'] ?? '/placeholder.svg' ?>" alt="" class="w-full h-48 object-cover">
            <div class="p-4">
                <h3 class="font-medium mb-2"><?= htmlspecialchars($item['name']) ?></h3>
                <p class="text-blue-600 font-bold"><?= number_format($item['sale_price'] ?? $item['price'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
            </div>
        </a>
        <div class="px-4 pb-4 flex gap-2">
            <form method="POST" action="<?= url('cart/add') ?>" class="flex-1">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm">
                    أضف للسلة
                </button>
            </form>
            <form method="POST" action="<?= url('wishlist/toggle') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" class="px-3 py-2 border rounded-lg hover:bg-red-50 text-red-500">
                    🗑️
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
