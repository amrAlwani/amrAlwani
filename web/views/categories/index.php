<h1 class="text-2xl font-bold mb-6">التصنيفات</h1>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php foreach ($categories as $category): ?>
    <a href="<?= url('categories/' . $category['slug']) ?>" 
       class="bg-white rounded-lg p-6 text-center hover:shadow-lg transition">
        <span class="text-4xl block mb-3"><?= $category['icon'] ?? '📦' ?></span>
        <h3 class="font-medium"><?= htmlspecialchars($category['name']) ?></h3>
        <p class="text-gray-500 text-sm"><?= $category['product_count'] ?? 0 ?> منتج</p>
    </a>
    <?php endforeach; ?>
</div>
