<h1 class="text-2xl font-bold mb-6">نتائج البحث: <?= htmlspecialchars($query) ?></h1>

<?php if (empty($products)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-gray-500 text-xl mb-4">لم نجد نتائج لـ "<?= htmlspecialchars($query) ?>"</p>
    <a href="<?= url('products') ?>" class="text-blue-600 hover:underline">تصفح جميع المنتجات</a>
</div>
<?php else: ?>
<p class="text-gray-600 mb-6">تم العثور على <?= $total ?> نتيجة</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <?php foreach ($products as $product): ?>
    <a href="<?= url('products/' . $product['slug']) ?>" class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition">
        <img src="<?= $product['image'] ?? '/placeholder.svg' ?>" alt="" class="w-full h-48 object-cover">
        <div class="p-4">
            <h3 class="font-medium mb-2"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-blue-600 font-bold"><?= number_format($product['sale_price'] ?? $product['price'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-8">
    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
    <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>" 
       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
