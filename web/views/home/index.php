<section class="bg-gradient-to-l from-primary-600 to-primary-800 text-white rounded-2xl p-8 md:p-12 mb-12 relative overflow-hidden">
    <div class="relative z-10">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">مرحباً بك في <?= defined('APP_NAME') ? APP_NAME : 'SwiftCart' ?></h1>
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
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/10 rounded-full translate-x-1/4 translate-y-1/4"></div>
</section>

<section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
    <div class="bg-white rounded-lg p-4 text-center shadow-sm border border-gray-100">
        <span class="text-3xl mb-2 block">🚚</span>
        <h3 class="font-medium text-gray-800">توصيل سريع</h3>
        <p class="text-gray-500 text-sm">خلال 2-5 أيام</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm border border-gray-100">
        <span class="text-3xl mb-2 block">💳</span>
        <h3 class="font-medium text-gray-800">دفع آمن</h3>
        <p class="text-gray-500 text-sm">بوابات موثوقة</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm border border-gray-100">
        <span class="text-3xl mb-2 block">🔄</span>
        <h3 class="font-medium text-gray-800">إرجاع مجاني</h3>
        <p class="text-gray-500 text-sm">خلال 14 يوم</p>
    </div>
    <div class="bg-white rounded-lg p-4 text-center shadow-sm border border-gray-100">
        <span class="text-3xl mb-2 block">🎧</span>
        <h3 class="font-medium text-gray-800">دعم 24/7</h3>
        <p class="text-gray-500 text-sm">نحن هنا لمساعدتك</p>
    </div>
</section>

<?php if (!empty($categories)): ?>
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">التصنيفات</h2>
        <a href="<?= url('categories') ?>" class="text-primary-600 hover:underline">عرض الكل</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($categories as $category): ?>
        <?php 
            // حماية الرابط: إذا لم يوجد slug نستخدم الـ ID، وإذا لم يوجد نضع #
            $safeSlug = htmlspecialchars($category['slug'] ?? $category['id'] ?? '#');
        ?>
        <a href="<?= url('categories/' . $safeSlug) ?>" 
           class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-lg transition group border border-gray-50">
            <span class="text-4xl block mb-3 group-hover:scale-110 transition-transform">
                <?= $category['icon'] ?? '📦' ?>
            </span>
            <p class="font-medium text-gray-800"><?= htmlspecialchars($category['name'] ?? 'تصنيف') ?></p>
            <?php if (isset($category['products_count'])): ?>
                <p class="text-gray-400 text-xs mt-1"><?= (int)$category['products_count'] ?> منتج</p>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">منتجات مميزة</h2>
        <a href="<?= url('products?featured=1') ?>" class="text-primary-600 hover:underline">عرض الكل</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($featuredProducts as $product): ?>
        <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition group border border-gray-100 flex flex-col">
            <a href="<?= url('products/' . htmlspecialchars($product['slug'] ?? $product['id'] ?? '')) ?>" class="block relative aspect-square overflow-hidden bg-gray-50">
                <img src="<?= htmlspecialchars($product['image'] ?? '/assets/img/placeholder.png') ?>" 
                     alt="<?= htmlspecialchars($product['name'] ?? 'منتج') ?>" 
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                     loading="lazy">
                
                <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold shadow-sm">
                    خصم <?= round((1 - ($product['sale_price'] / $product['price'])) * 100) ?>%
                </span>
                <?php endif; ?>
                
                <span class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold shadow-sm">مميز</span>
            </a>
            
            <div class="p-4 flex-1 flex flex-col">
                <a href="<?= url('products/' . htmlspecialchars($product['slug'] ?? $product['id'] ?? '')) ?>" class="hover:text-primary-600 transition">
                    <h3 class="font-medium mb-2 text-gray-800 line-clamp-2 min-h-[3rem]"><?= htmlspecialchars($product['name'] ?? 'بدون اسم') ?></h3>
                </a>
                
                <div class="mt-auto flex items-center gap-2 mb-4">
                    <span class="text-primary-600 font-bold text-lg">
                        <?= number_format($product['sale_price'] ?? $product['price'] ?? 0, 2) ?> <?= defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'ر.س' ?>
                    </span>
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <span class="text-gray-400 text-sm line-through"><?= number_format($product['price'], 2) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user'])): ?>
                <form method="POST" action="<?= url('cart/add') ?>">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 0) ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="w-full bg-primary-600 text-white py-2.5 rounded-lg hover:bg-primary-700 transition flex items-center justify-center gap-2 font-medium">
                        <span>🛒</span> أضف للسلة
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="bg-gray-900 text-white rounded-2xl p-8 md:p-12 text-center relative overflow-hidden">
    <div class="relative z-10">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">اشترك في النشرة البريدية</h2>
        <p class="text-gray-400 mb-8 max-w-2xl mx-auto">احصل على آخر العروض والخصومات الحصرية مباشرة في بريدك الإلكتروني</p>
        <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" onsubmit="event.preventDefault(); alert('تم الاشتراك بنجاح!');">
            <input type="email" placeholder="بريدك الإلكتروني" required
                   class="flex-1 px-5 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500 border-none">
            <button type="submit" class="bg-primary-600 px-8 py-3 rounded-lg font-bold hover:bg-primary-700 transition shadow-lg">
                اشترك الآن
            </button>
        </form>
    </div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-primary-600/10 rounded-full blur-3xl"></div>
</section>