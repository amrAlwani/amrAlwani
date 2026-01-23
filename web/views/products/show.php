<div class="bg-white rounded-lg overflow-hidden">
    <div class="grid md:grid-cols-2 gap-8 p-6">
        <!-- Product Images -->
        <div>
            <img src="<?= $product['image'] ?? '/placeholder.svg' ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="w-full rounded-lg" id="mainImage">
            <?php if (!empty($product['images'])): 
                $images = json_decode($product['images'], true) ?? [];
            ?>
            <div class="flex gap-2 mt-4">
                <img src="<?= $product['image'] ?? '/placeholder.svg' ?>" 
                     class="w-20 h-20 object-cover rounded cursor-pointer border-2 border-blue-600"
                     onclick="document.getElementById('mainImage').src=this.src">
                <?php foreach ($images as $img): ?>
                <img src="<?= $img ?>" 
                     class="w-20 h-20 object-cover rounded cursor-pointer border-2 border-transparent hover:border-blue-600"
                     onclick="document.getElementById('mainImage').src=this.src">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div>
            <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($product['name']) ?></h1>
            
            <?php if (!empty($reviews)): ?>
            <div class="flex items-center gap-2 mb-4">
                <div class="text-yellow-400">
                    <?php 
                    $avgRating = array_sum(array_column($reviews, 'rating')) / count($reviews);
                    for ($i = 1; $i <= 5; $i++): 
                    ?>
                    <span><?= $i <= $avgRating ? '★' : '☆' ?></span>
                    <?php endfor; ?>
                </div>
                <span class="text-gray-500">(<?= count($reviews) ?> تقييم)</span>
            </div>
            <?php endif; ?>
            
            <div class="flex items-center gap-4 mb-6">
                <span class="text-3xl font-bold text-blue-600">
                    <?= number_format($product['sale_price'] ?? $product['price'], 2) ?> <?= CURRENCY_SYMBOL ?>
                </span>
                <?php if (!empty($product['sale_price'])): ?>
                <span class="text-xl text-gray-400 line-through"><?= number_format($product['price'], 2) ?> <?= CURRENCY_SYMBOL ?></span>
                <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-sm">
                    خصم <?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                </span>
                <?php endif; ?>
            </div>

            <p class="text-gray-600 mb-6"><?= nl2br(htmlspecialchars($product['short_description'] ?? '')) ?></p>

            <!-- Stock Status -->
            <div class="mb-6">
                <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                <span class="text-green-600">✓ متوفر (<?= $product['stock_quantity'] ?> قطعة)</span>
                <?php else: ?>
                <span class="text-red-600">✗ غير متوفر</span>
                <?php endif; ?>
            </div>

            <!-- Add to Cart -->
            <form method="POST" action="<?= url('cart/add') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="flex items-center gap-4">
                    <label class="text-gray-600">الكمية:</label>
                    <div class="flex items-center border rounded-lg">
                        <button type="button" onclick="decrementQty()" class="px-4 py-2 hover:bg-gray-100">-</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="<?= $product['stock_quantity'] ?? 10 ?>" 
                               class="w-16 text-center border-x py-2">
                        <button type="button" onclick="incrementQty()" class="px-4 py-2 hover:bg-gray-100">+</button>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-bold">
                        🛒 أضف للسلة
                    </button>
                    <form method="POST" action="<?= url('wishlist/toggle') ?>" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="px-4 py-3 border rounded-lg hover:bg-gray-100 <?= $isInWishlist ? 'text-red-500' : '' ?>">
                            <?= $isInWishlist ? '❤️' : '🤍' ?>
                        </button>
                    </form>
                </div>
            </form>

            <!-- SKU -->
            <?php if (!empty($product['sku'])): ?>
            <p class="text-gray-500 text-sm mt-4">رمز المنتج: <?= htmlspecialchars($product['sku']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($product['description'])): ?>
    <div class="border-t p-6">
        <h2 class="text-xl font-bold mb-4">وصف المنتج</h2>
        <div class="prose max-w-none text-gray-600">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Reviews -->
<div class="bg-white rounded-lg p-6 mt-6">
    <h2 class="text-xl font-bold mb-6">التقييمات والمراجعات</h2>
    
    <?php if (empty($reviews)): ?>
    <p class="text-gray-500 text-center py-8">لا توجد تقييمات بعد</p>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($reviews as $review): ?>
        <div class="border-b pb-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="text-yellow-400">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span><?= $i <= $review['rating'] ? '★' : '☆' ?></span>
                    <?php endfor; ?>
                </div>
                <span class="font-medium"><?= htmlspecialchars($review['user_name'] ?? 'مستخدم') ?></span>
                <span class="text-gray-400 text-sm"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
            </div>
            <?php if (!empty($review['title'])): ?>
            <h4 class="font-medium"><?= htmlspecialchars($review['title']) ?></h4>
            <?php endif; ?>
            <p class="text-gray-600"><?= htmlspecialchars($review['comment'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<div class="mt-8">
    <h2 class="text-xl font-bold mb-6">منتجات مشابهة</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($relatedProducts as $related): ?>
        <a href="<?= url('products/' . $related['slug']) ?>" class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition">
            <img src="<?= $related['image'] ?? '/placeholder.svg' ?>" alt="" class="w-full h-40 object-cover">
            <div class="p-4">
                <h3 class="font-medium mb-2 line-clamp-2"><?= htmlspecialchars($related['name']) ?></h3>
                <p class="text-blue-600 font-bold"><?= number_format($related['sale_price'] ?? $related['price'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function incrementQty() {
    const input = document.getElementById('qty');
    if (input.value < input.max) input.value = parseInt(input.value) + 1;
}
function decrementQty() {
    const input = document.getElementById('qty');
    if (input.value > 1) input.value = parseInt(input.value) - 1;
}
</script>
