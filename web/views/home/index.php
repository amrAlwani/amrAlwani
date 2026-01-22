<!-- Hero Section -->
<section class="bg-gradient-to-l from-primary-600 to-primary-800 text-white rounded-2xl p-8 md:p-12 mb-12 relative overflow-hidden">
    <div class="relative z-10">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">مرحباً بك في <?= APP_NAME ?></h1>
        <p class="text-lg md:text-xl mb-6 opacity-90">تسوق أفضل المنتجات بأفضل الأسعار مع توصيل سريع</p>
        <div class="flex flex-wrap gap-4">
            <a href="<?= url('products') ?>" class="bg-white text-primary-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                تسوق الآن
            </a>
            <a href="<?= url('categories') ?>" class="border-2 border-white text-white px-6 py-3 rounded-lg font-bold hover:bg-white hover:text-primary-600 transition">
                تصفح التصنيفات
            </a>
        </div>
    </div>
    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/10 rounded-full translate-x-1/4 translate-y-1/4"></div>
</section>

<!-- Features -->
<section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
    <div class="bg-white rounded-lg p-4 text-center shadow-sm">
        <span class="text-3xl mb-2 block">🚚</span>
        <h3 class="font-medium">توصيل سريع</h3>
        <p class="text-gray-500 text-sm">خلال 2-5 أيام</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm">
        <span class="text-3xl mb-2 block">💳</span>
        <h3 class="font-medium">دفع آمن</h3>
        <p class="text-gray-500 text-sm">بوابات موثوقة</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm">
        <span class="text-3xl mb-2 block">🔄</span>
        <h3 class="font-medium">إرجاع مجاني</h3>
        <p class="text-gray-500 text-sm">خلال 14 يوم</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm">
        <span class="text-3xl mb-2 block">🎧</span>
        <h3 class="font-medium">دعم 24/7</h3>
        <p class="text-gray-500 text-sm">نحن هنا لمساعدتك</p>
    </div>
</section>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">التصنيفات</h2>
        <a href="<?= url('categories') ?>" class="text-primary-600 hover:underline">عرض الكل</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($categories as $category): ?>
        <a href="<?= url('categories/' . htmlspecialchars($category['slug'])) ?>" 
           class="bg-white rounded-xl p-4 text-center hover:shadow-lg transition group">
            <span class="text-4xl block mb-3 group-hover:scale-110 transition-transform">
                <?= $category['icon'] ?? '📦' ?>
            </span>
            <p class="font-medium text-gray-800"><?= htmlspecialchars($category['name']) ?></p>
            <?php if (isset($category['products_count'])): ?>
            <p class="text-gray-400 text-sm"><?= $category['products_count'] ?> منتج</p>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">منتجات مميزة</h2>
        <a href="<?= url('products?featured=1') ?>" class="text-primary-600 hover:underline">عرض الكل</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($featuredProducts as $product): ?>
        <article class="bg-white rounded-xl overflow-hidden hover:shadow-lg transition group">
            <a href="<?= url('products/' . htmlspecialchars($product['slug'])) ?>" class="block relative">
                <img src="<?= htmlspecialchars($product['image'] ?? '/placeholder.svg') ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                
                <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                    خصم <?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                </span>
                <?php endif; ?>
                
                <?php if (isset($product['is_featured']) && $product['is_featured']): ?>
                <span class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold">
                    مميز
                </span>
                <?php endif; ?>
            </a>
            
            <div class="p-4">
                <a href="<?= url('products/' . htmlspecialchars($product['slug'])) ?>" class="block">
                    <h3 class="font-medium mb-2 line-clamp-2 group-hover:text-primary-600 transition">
                        <?= htmlspecialchars($product['name']) ?>
                    </h3>
                </a>
                
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-primary-600 font-bold">
                        <?= number_format($product['sale_price'] ?? $product['price'], 2) ?> <?= CURRENCY_SYMBOL ?>
                    </span>
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <span class="text-gray-400 text-sm line-through">
                        <?= number_format($product['price'], 2) ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['user'])): ?>
                <form method="POST" action="<?= url('cart/add') ?>" class="mt-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" 
                            class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                        🛒 أضف للسلة
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<?php if (!empty($latestProducts)): ?>
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">أحدث المنتجات</h2>
        <a href="<?= url('products?sort=newest') ?>" class="text-primary-600 hover:underline">عرض الكل</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($latestProducts as $product): ?>
        <article class="bg-white rounded-xl overflow-hidden hover:shadow-lg transition group">
            <a href="<?= url('products/' . htmlspecialchars($product['slug'])) ?>" class="block relative">
                <img src="<?= htmlspecialchars($product['image'] ?? '/placeholder.svg') ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                
                <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                    خصم <?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                </span>
                <?php endif; ?>
                
                <span class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                    جديد
                </span>
            </a>
            
            <div class="p-4">
                <a href="<?= url('products/' . htmlspecialchars($product['slug'])) ?>" class="block">
                    <h3 class="font-medium mb-2 line-clamp-2 group-hover:text-primary-600 transition">
                        <?= htmlspecialchars($product['name']) ?>
                    </h3>
                </a>
                
                <div class="flex items-center gap-2">
                    <span class="text-primary-600 font-bold">
                        <?= number_format($product['sale_price'] ?? $product['price'], 2) ?> <?= CURRENCY_SYMBOL ?>
                    </span>
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <span class="text-gray-400 text-sm line-through">
                        <?= number_format($product['price'], 2) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter -->
<section class="bg-gray-800 text-white rounded-2xl p-8 md:p-12 text-center">
    <h2 class="text-2xl md:text-3xl font-bold mb-4">اشترك في النشرة البريدية</h2>
    <p class="text-gray-300 mb-6 max-w-2xl mx-auto">احصل على آخر العروض والخصومات مباشرة في بريدك الإلكتروني</p>
    <form class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
        <input type="email" placeholder="بريدك الإلكتروني" 
               class="flex-1 px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <button type="submit" class="bg-primary-600 px-6 py-3 rounded-lg font-bold hover:bg-primary-700 transition">
            اشترك الآن
        </button>
    </form>
</section>
