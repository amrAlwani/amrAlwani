<?php
/**
 * الصفحة الرئيسية
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';

$productModel = new Product();
$categoryModel = new Category();

// المنتجات المميزة
$featuredProducts = $productModel->getFeatured(8);

// التصنيفات
$categories = $categoryModel->getAll();

// أحدث المنتجات
$latestResult = $productModel->getAll(1, 8, ['sort' => 'newest']);
$latestProducts = $latestResult['products'];

// التحقق من تسجيل الدخول
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$currentUser = $isLoggedIn ? ($_SESSION['user'] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?? 'SwiftCart' ?> - متجرك الإلكتروني</title>
    <meta name="description" content="اكتشف أفضل المنتجات بأسعار منافسة مع توصيل سريع وآمن">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include __DIR__ . '/views/layouts/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 text-center lg:text-right">
                    <h1 class="text-4xl lg:text-6xl font-extrabold mb-6">
                        تسوق بذكاء
                        <span class="block text-yellow-300">وفّر أكثر</span>
                    </h1>
                    <p class="text-xl text-white/80 mb-8">
                        اكتشف آلاف المنتجات المميزة بأسعار لا تُقاوم مع توصيل سريع لباب منزلك
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="<?= BASE_URL ?>/products.php" class="px-8 py-4 bg-white text-purple-600 rounded-xl font-bold hover:bg-yellow-300 hover:text-purple-800 transition transform hover:scale-105">
                            تسوق الآن
                        </a>
                        <a href="<?= BASE_URL ?>/offers.php" class="px-8 py-4 border-2 border-white text-white rounded-xl font-bold hover:bg-white hover:text-purple-600 transition">
                            عروض اليوم
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-white/20 rounded-3xl blur-xl"></div>
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600" 
                             alt="تسوق" 
                             class="relative rounded-3xl shadow-2xl w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- التصنيفات -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">تصفح حسب التصنيف</h2>
                <p class="text-gray-600">اختر التصنيف المناسب لك</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                <a href="<?= BASE_URL ?>/categories.php?action=show&id=<?= $category['id'] ?>" 
                   class="group p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl text-center hover:from-purple-50 hover:to-purple-100 transition">
                    <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-2xl flex items-center justify-center group-hover:bg-purple-200 transition">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 group-hover:text-purple-600 transition"><?= htmlspecialchars($category['name']) ?></h3>
                    <p class="text-sm text-gray-500 mt-1"><?= $category['products_count'] ?? 0 ?> منتج</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- المنتجات المميزة -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">منتجات مميزة</h2>
                    <p class="text-gray-600 mt-2">اختيارنا لأفضل المنتجات</p>
                </div>
                <a href="<?= BASE_URL ?>/products.php?featured=1" class="text-purple-600 font-medium hover:text-purple-800 transition flex items-center gap-2">
                    عرض الكل
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="card-hover bg-white rounded-2xl overflow-hidden shadow-sm transition-all duration-300">
                    <div class="relative aspect-square bg-gray-100">
                        <?php if (!empty($product['image'])): ?>
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                        <div class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                            -<?= $discount ?>%
                        </div>
                        <?php endif; ?>
                        
                        <div class="absolute top-3 left-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-lg flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            مميز
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="hover:text-purple-600 transition">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>
                        <div class="flex items-baseline gap-2">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <span class="text-lg font-bold text-red-600"><?= number_format($product['sale_price'], 2) ?></span>
                            <span class="text-sm text-gray-400 line-through"><?= number_format($product['price'], 2) ?></span>
                            <?php else: ?>
                            <span class="text-lg font-bold text-gray-900"><?= number_format($product['price'], 2) ?></span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-500"><?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- أحدث المنتجات -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">وصل حديثاً</h2>
                    <p class="text-gray-600 mt-2">أحدث المنتجات في متجرنا</p>
                </div>
                <a href="<?= BASE_URL ?>/products.php?sort=newest" class="text-purple-600 font-medium hover:text-purple-800 transition flex items-center gap-2">
                    عرض الكل
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($latestProducts as $product): ?>
                <div class="card-hover bg-gray-50 rounded-2xl overflow-hidden transition-all duration-300">
                    <div class="relative aspect-square bg-gray-100">
                        <?php if (!empty($product['image'])): ?>
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="w-full h-full object-cover">
                        <?php endif; ?>
                        
                        <div class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                            جديد
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="hover:text-purple-600 transition">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-bold text-gray-900"><?= number_format($product['sale_price'] ?? $product['price'], 2) ?></span>
                            <span class="text-xs text-gray-500"><?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- مميزات المتجر -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">شحن سريع</h3>
                    <p class="text-gray-600 text-sm">توصيل خلال 24-48 ساعة</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">دفع آمن</h3>
                    <p class="text-gray-600 text-sm">معاملات مشفرة 100%</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">استرجاع سهل</h3>
                    <p class="text-gray-600 text-sm">استرجاع خلال 14 يوم</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">دعم 24/7</h3>
                    <p class="text-gray-600 text-sm">فريق دعم متواجد دائماً</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/views/layouts/footer.php'; ?>
</body>
</html>
