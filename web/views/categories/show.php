<div class="flex items-center gap-2 mb-6">
    <a href="<?= url('categories') ?>" class="text-gray-500 hover:text-gray-700">التصنيفات</a>
    <span class="text-gray-400">/</span>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($category['name']) ?></h1>
</div>

<?php if (!empty($category['description'])): ?>
<p class="text-gray-600 mb-6"><?= htmlspecialchars($category['description']) ?></p>
<?php endif; ?>

<?php if (!empty($subcategories)): ?>
<div class="flex flex-wrap gap-2 mb-6">
    <?php foreach ($subcategories as $sub): ?>
    <a href="<?= url('categories/' . $sub['slug']) ?>" 
       class="bg-white px-4 py-2 rounded-full hover:bg-blue-50 hover:text-blue-600">
        <?= htmlspecialchars($sub['name']) ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($products)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-gray-500 text-xl">لا توجد منتجات في هذا التصنيف</p>
</div>
<?php else: ?>
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
    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
