<?php
/**
 * Products Listing View - الموقع الرئيسي
 * عرض قائمة المنتجات للزوار والمستخدمين
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنتجات - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .product-card:hover .product-image { transform: scale(1.05); }
        .product-card:hover { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- العنوان والفلاتر -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">المنتجات</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    اكتشف مجموعتنا المميزة من المنتجات
                    <?php if (!empty($total)): ?>
                    <span class="text-sm">(<?= number_format($total) ?> منتج)</span>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- البحث والفلاتر -->
            <div class="flex flex-wrap gap-3">
                <form method="GET" class="flex gap-3">
                    <!-- البحث -->
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($search ?? '') ?>"
                               placeholder="ابحث عن منتج..."
                               class="w-64 pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl 
                                      bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <!-- التصنيف -->
                    <select name="category_id" 
                            class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl 
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="">كل التصنيفات</option>
                        <?php foreach ($categories ?? [] as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($category_id ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- الترتيب -->
                    <select name="sort" 
                            class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl 
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>الأحدث</option>
                        <option value="price_asc" <?= ($sort ?? '') === 'price_asc' ? 'selected' : '' ?>>السعر: الأقل</option>
                        <option value="price_desc" <?= ($sort ?? '') === 'price_desc' ? 'selected' : '' ?>>السعر: الأعلى</option>
                        <option value="popular" <?= ($sort ?? '') === 'popular' ? 'selected' : '' ?>>الأكثر مبيعاً</option>
                    </select>
                    
                    <button type="submit" 
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                        بحث
                    </button>
                </form>
            </div>
        </div>

        <!-- شبكة المنتجات -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if (empty($products)): ?>
            <div class="col-span-full py-20 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">لا توجد منتجات</h3>
                <p class="text-gray-500 dark:text-gray-400">لم يتم العثور على منتجات مطابقة لبحثك</p>
                <a href="?" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    عرض كل المنتجات
                </a>
            </div>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm transition-all duration-300 group">
                <!-- صورة المنتج -->
                <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-700">
                    <?php if (!empty($product['image'])): ?>
                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="product-image w-full h-full object-cover transition-transform duration-300">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                    
                    <!-- شارة الخصم -->
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                    <div class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                        -<?= $discount ?>%
                    </div>
                    <?php endif; ?>
                    
                    <!-- شارة منتج مميز -->
                    <?php if (!empty($product['is_featured'])): ?>
                    <div class="absolute top-3 left-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        مميز
                    </div>
                    <?php endif; ?>
                    
                    <!-- أزرار سريعة -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <button onclick="addToCart(<?= $product['id'] ?>)" 
                                class="p-3 bg-white rounded-full shadow-lg hover:bg-blue-600 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </button>
                        <button onclick="addToFavorites(<?= $product['id'] ?>)" 
                                class="p-3 bg-white rounded-full shadow-lg hover:bg-red-500 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- معلومات المنتج -->
                <div class="p-4">
                    <!-- التصنيف -->
                    <?php if (!empty($product['category_name'])): ?>
                    <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- الاسم -->
                    <h3 class="mt-1 font-bold text-gray-900 dark:text-white line-clamp-2">
                        <?= htmlspecialchars($product['name']) ?>
                    </h3>
                    
                    <!-- التقييم -->
                    <div class="mt-2 flex items-center gap-1">
                        <?php 
                        $rating = $product['rating'] ?? 0;
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                        <svg class="w-4 h-4 <?= $i <= $rating ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <?php endfor; ?>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mr-1">
                            (<?= $product['review_count'] ?? 0 ?>)
                        </span>
                    </div>
                    
                    <!-- السعر والتوفر -->
                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-baseline gap-2">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <span class="text-lg font-bold text-red-600">
                                <?= number_format($product['sale_price'], 2) ?>
                            </span>
                            <span class="text-sm text-gray-400 line-through">
                                <?= number_format($product['price'], 2) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                <?= number_format($product['price'], 2) ?>
                            </span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-500"><?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        
                        <!-- حالة المخزون -->
                        <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                        <span class="text-xs text-green-600 font-medium">متوفر</span>
                        <?php else: ?>
                        <span class="text-xs text-red-600 font-medium">نفذ</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- الترقيم -->
        <?php if (($totalPages ?? 0) > 1): ?>
        <div class="mt-12 flex items-center justify-center gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&category_id=<?= $category_id ?? '' ?>&sort=<?= $sort ?? '' ?>" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                السابق
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&category_id=<?= $category_id ?? '' ?>&sort=<?= $sort ?? '' ?>" 
               class="px-4 py-2 rounded-lg transition <?= $i === $page ? 'bg-blue-600 text-white' : 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&category_id=<?= $category_id ?? '' ?>&sort=<?= $sort ?? '' ?>" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                التالي
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // إضافة للسلة
        function addToCart(productId) {
            fetch('/api/cart.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('تمت الإضافة للسلة');
                    // يمكن تحديث عداد السلة هنا
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            })
            .catch(() => alert('حدث خطأ في الاتصال'));
        }

        // إضافة للمفضلة
        function addToFavorites(productId) {
            // يمكن تنفيذها بنفس الطريقة
            alert('تمت الإضافة للمفضلة');
        }
    </script>
</body>
</html>
