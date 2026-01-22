<?php
/**
 * عرض التصنيفات
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التصنيفات - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">تصفح التصنيفات</h1>
            <p class="text-gray-600">اختر التصنيف المناسب لك</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
            <a href="/categories.php?action=show&id=<?= $category['id'] ?>" 
               class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                <!-- صورة التصنيف -->
                <div class="aspect-square bg-gradient-to-br from-purple-100 to-pink-100 relative overflow-hidden">
                    <?php if (!empty($category['image'])): ?>
                    <img src="<?= htmlspecialchars($category['image']) ?>" 
                         alt="<?= htmlspecialchars($category['name']) ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                    
                    <!-- عدد المنتجات -->
                    <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium text-gray-700">
                        <?= $category['products_count'] ?? 0 ?> منتج
                    </div>
                </div>
                
                <!-- معلومات التصنيف -->
                <div class="p-4">
                    <h3 class="font-bold text-gray-900 group-hover:text-purple-600 transition text-lg">
                        <?= htmlspecialchars($category['name']) ?>
                    </h3>
                    <?php if (!empty($category['description'])): ?>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2"><?= htmlspecialchars($category['description']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($categories)): ?>
        <div class="text-center py-20">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="text-xl font-medium text-gray-900 mb-2">لا توجد تصنيفات</h3>
            <p class="text-gray-500">سيتم إضافة التصنيفات قريباً</p>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
