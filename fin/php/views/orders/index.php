<?php
/**
 * عرض طلبات المستخدم
 */

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-purple-100 text-purple-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
];

$statusLabels = [
    'pending' => 'قيد الانتظار',
    'processing' => 'قيد المعالجة',
    'shipped' => 'تم الشحن',
    'delivered' => 'تم التوصيل',
    'cancelled' => 'ملغي',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">طلباتي</h1>
            <a href="/orders.php?action=track" class="text-purple-600 hover:text-purple-800 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                تتبع طلب
            </a>
        </div>

        <?php if (empty($orders)): ?>
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-24 w-24 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">لا توجد طلبات</h2>
            <p class="text-gray-600 mb-6">لم تقم بأي طلبات بعد. ابدأ التسوق الآن!</p>
            <a href="/products.php" class="inline-flex items-center gap-2 px-8 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                تصفح المنتجات
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- رأس الطلب -->
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-sm text-gray-500">رقم الطلب</span>
                            <p class="font-bold text-gray-900">#<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?></p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div>
                            <span class="text-gray-500">التاريخ</span>
                            <p class="font-medium text-gray-900"><?= date('Y/m/d', strtotime($order['created_at'])) ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">الإجمالي</span>
                            <p class="font-bold text-gray-900"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></p>
                        </div>
                        <a href="/orders.php?action=show&id=<?= $order['id'] ?>" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                            التفاصيل
                        </a>
                    </div>
                </div>
                
                <!-- عناصر الطلب -->
                <div class="p-4 flex flex-wrap gap-4">
                    <?php foreach (array_slice($order['items'] ?? [], 0, 4) as $item): ?>
                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                        <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($order['items'] ?? []) > 4): ?>
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 font-medium">
                        +<?= count($order['items']) - 4 ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- الترقيم -->
        <?php if (($totalPages ?? 0) > 1): ?>
        <div class="mt-8 flex items-center justify-center gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" 
               class="px-4 py-2 rounded-lg transition <?= $i === $page ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
