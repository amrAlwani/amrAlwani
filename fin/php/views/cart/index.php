<?php
/**
 * عرض سلة التسوق
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">سلة التسوق</h1>

        <?php if (empty($cart['items'])): ?>
        <!-- سلة فارغة -->
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-24 w-24 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">سلتك فارغة</h2>
            <p class="text-gray-600 mb-6">لم تضف أي منتجات بعد. ابدأ التسوق الآن!</p>
            <a href="/products.php" class="inline-flex items-center gap-2 px-8 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                تصفح المنتجات
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- قائمة المنتجات -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cart['items'] as $item): ?>
                <div class="bg-white rounded-xl shadow-sm p-4 flex gap-4" id="cart-item-<?= $item['id'] ?>">
                    <!-- صورة المنتج -->
                    <div class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                        <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    
                    <!-- التفاصيل -->
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                <?php if (!empty($item['variant_name'])): ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($item['variant_name']) ?></p>
                                <?php endif; ?>
                            </div>
                            <button onclick="removeFromCart(<?= $item['id'] ?>)" 
                                    class="p-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between mt-4">
                            <!-- التحكم بالكمية -->
                            <div class="flex items-center gap-2">
                                <button onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                        class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="w-12 text-center font-medium"><?= $item['quantity'] ?></span>
                                <button onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)"
                                        class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- السعر -->
                            <div class="text-left">
                                <p class="font-bold text-gray-900"><?= number_format($item['total'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></p>
                                <p class="text-sm text-gray-500"><?= number_format($item['unit_price'], 2) ?> × <?= $item['quantity'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- ملخص الطلب -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">ملخص الطلب</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between text-gray-600">
                            <span>المجموع الفرعي</span>
                            <span><?= number_format($cart['subtotal'] ?? 0, 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        
                        <?php if (!empty($cart['discount'])): ?>
                        <div class="flex justify-between text-green-600">
                            <span>الخصم</span>
                            <span>-<?= number_format($cart['discount'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>الضريبة (15%)</span>
                            <span><?= number_format($cart['tax'] ?? 0, 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>الشحن</span>
                            <span><?= ($cart['shipping'] ?? 0) > 0 ? number_format($cart['shipping'], 2) . ' ' . (CURRENCY_SYMBOL ?? 'SAR') : 'مجاني' ?></span>
                        </div>
                        
                        <hr class="border-gray-200">
                        
                        <div class="flex justify-between text-xl font-bold text-gray-900">
                            <span>الإجمالي</span>
                            <span><?= number_format($cart['total'] ?? 0, 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?></span>
                        </div>
                    </div>
                    
                    <!-- كود الخصم -->
                    <div class="mt-6">
                        <div class="flex gap-2">
                            <input type="text" 
                                   id="coupon-code"
                                   placeholder="كود الخصم"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <button onclick="applyCoupon()" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                                تطبيق
                            </button>
                        </div>
                    </div>
                    
                    <!-- زر الشراء -->
                    <a href="/checkout.php" 
                       class="mt-6 w-full flex items-center justify-center gap-2 px-6 py-4 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        إتمام الشراء
                    </a>
                    
                    <a href="/products.php" class="mt-3 w-full flex items-center justify-center gap-2 text-purple-600 hover:text-purple-800 transition">
                        <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        متابعة التسوق
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        function updateQuantity(itemId, quantity) {
            if (quantity < 1) {
                removeFromCart(itemId);
                return;
            }
            
            fetch('/cart.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: itemId, quantity: quantity })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || 'حدث خطأ');
            });
        }

        function removeFromCart(itemId) {
            if (!confirm('هل تريد حذف هذا المنتج؟')) return;
            
            fetch('/cart.php?action=remove&item_id=' + itemId)
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || 'حدث خطأ');
            });
        }

        function applyCoupon() {
            const code = document.getElementById('coupon-code').value;
            if (!code) return;
            
            fetch('/api/cart.php?action=totals&coupon=' + encodeURIComponent(code))
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || 'كود غير صالح');
            });
        }
    </script>
</body>
</html>
