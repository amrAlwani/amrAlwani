<?php
/**
 * عرض تفاصيل الطلب
 */

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
    'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
    'delivered' => 'bg-green-100 text-green-800 border-green-200',
    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
];

$statusLabels = [
    'pending' => 'قيد الانتظار',
    'processing' => 'قيد المعالجة',
    'shipped' => 'تم الشحن',
    'delivered' => 'تم التوصيل',
    'cancelled' => 'ملغي',
];

$statusSteps = ['pending', 'processing', 'shipped', 'delivered'];
$currentStepIndex = array_search($order['status'], $statusSteps);
if ($currentStepIndex === false) $currentStepIndex = -1;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?> - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- العودة -->
        <a href="/orders.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة للطلبات
        </a>

        <!-- رأس الطلب -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">طلب #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?></h1>
                    <p class="text-gray-500 mt-1"><?= date('Y/m/d - h:i A', strtotime($order['created_at'])) ?></p>
                </div>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium <?= $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                    <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                </span>
            </div>

            <!-- خطوات التتبع -->
            <?php if ($order['status'] !== 'cancelled'): ?>
            <div class="mt-8">
                <div class="flex items-center justify-between relative">
                    <div class="absolute top-5 right-0 left-0 h-0.5 bg-gray-200 -z-10"></div>
                    <?php foreach ($statusSteps as $index => $step): ?>
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $index <= $currentStepIndex ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-400' ?>">
                            <?php if ($index < $currentStepIndex): ?>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <?php else: ?>
                            <span class="font-bold"><?= $index + 1 ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="mt-2 text-sm <?= $index <= $currentStepIndex ? 'text-purple-600 font-medium' : 'text-gray-400' ?>">
                            <?= $statusLabels[$step] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- المنتجات -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">المنتجات</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($order['items'] ?? [] as $item): ?>
                        <div class="p-4 flex gap-4">
                            <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="text-sm text-gray-500">الكمية: <?= $item['quantity'] ?></p>
                                <p class="text-sm text-gray-500">السعر: <?= number_format($item['price'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></p>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-900"><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- الملخص -->
            <div class="lg:col-span-1 space-y-6">
                <!-- ملخص المبالغ -->
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="font-bold text-gray-900 mb-4">ملخص الطلب</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>المجموع الفرعي</span>
                            <span><?= number_format($order['subtotal'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                        <div class="flex justify-between text-green-600">
                            <span>الخصم</span>
                            <span>-<?= number_format($order['discount'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-gray-600">
                            <span>الضريبة</span>
                            <span><?= number_format($order['tax'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>الشحن</span>
                            <span><?= $order['shipping_cost'] > 0 ? number_format($order['shipping_cost'], 2) . ' ' . (CURRENCY_SYMBOL ?? 'SAR') : 'مجاني' ?></span>
                        </div>
                        <hr>
                        <div class="flex justify-between text-lg font-bold text-gray-900">
                            <span>الإجمالي</span>
                            <span><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                    </div>
                </div>

                <!-- عنوان الشحن -->
                <?php 
                $address = $order['shipping_address'];
                if (is_string($address)) $address = json_decode($address, true);
                ?>
                <?php if ($address): ?>
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="font-bold text-gray-900 mb-4">عنوان الشحن</h2>
                    <div class="text-gray-600 space-y-1">
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($address['name'] ?? '') ?></p>
                        <p><?= htmlspecialchars($address['phone'] ?? '') ?></p>
                        <p><?= htmlspecialchars($address['street'] ?? $address['address'] ?? '') ?></p>
                        <p><?= htmlspecialchars($address['city'] ?? '') ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- إجراءات -->
                <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                <form method="POST" action="/orders.php?action=cancel&id=<?= $order['id'] ?>" 
                      onsubmit="return confirm('هل أنت متأكد من إلغاء الطلب؟')">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <button type="submit" 
                            class="w-full px-4 py-3 border border-red-300 text-red-600 rounded-xl font-medium hover:bg-red-50 transition">
                        إلغاء الطلب
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
