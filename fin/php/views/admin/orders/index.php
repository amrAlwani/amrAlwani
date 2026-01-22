<?php
/**
 * Admin Orders List View
 * عرض قائمة الطلبات للإدارة
 */

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'shipped' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
];

$paymentStatusColors = [
    'pending' => 'text-yellow-600',
    'paid' => 'text-green-600',
    'failed' => 'text-red-600',
    'refunded' => 'text-gray-600',
];
?>
<div class="space-y-6">
    <!-- العنوان والفلاتر -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">إدارة الطلبات</h1>
            <p class="text-gray-600 dark:text-gray-400">إجمالي <?= number_format($total) ?> طلب</p>
        </div>
        
        <!-- فلاتر البحث -->
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="hidden" name="action" value="list">
            
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       placeholder="رقم الطلب أو اسم العميل..."
                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <select name="status" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">كل الحالات</option>
                <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($status ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                بحث
            </button>
            
            <?php if (!empty($search) || !empty($status)): ?>
            <a href="?action=list" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 transition">
                إعادة تعيين
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <?php 
        $statusIcons = [
            'pending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'processing' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
            'shipped' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
            'delivered' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];
        foreach ($statuses as $key => $label): 
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg <?= str_replace(['text-', '100'], ['bg-', '200'], $statusColors[$key] ?? 'bg-gray-200') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $statusIcons[$key] ?? '' ?>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $label ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- جدول الطلبات -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            رقم الطلب
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            العميل
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            الإجمالي
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            حالة الطلب
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            حالة الدفع
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            التاريخ
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-lg font-medium">لا يوجد طلبات</p>
                            <p class="text-sm">لم يتم العثور على أي طلبات مطابقين للبحث</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                ID: <?= $order['id'] ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white text-sm font-bold">
                                    <?= mb_substr($order['user_name'] ?? 'G', 0, 1, 'UTF-8') ?>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($order['user_name'] ?? 'زائر') ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <?= htmlspecialchars($order['user_email'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                <?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?? 'SAR' ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <?= $order['payment_method'] ?? 'نقدي' ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $statuses[$order['status']] ?? $order['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium <?= $paymentStatusColors[$order['payment_status']] ?? 'text-gray-600' ?>">
                                <?php
                                $paymentLabels = [
                                    'pending' => 'في الانتظار',
                                    'paid' => 'مدفوع',
                                    'failed' => 'فشل',
                                    'refunded' => 'مسترد',
                                ];
                                echo $paymentLabels[$order['payment_status']] ?? $order['payment_status'];
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div><?= date('Y/m/d', strtotime($order['created_at'])) ?></div>
                            <div class="text-xs"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center gap-2">
                                <a href="?action=show&id=<?= $order['id'] ?>" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition"
                                   title="عرض التفاصيل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                
                                <!-- تحديث سريع للحالة -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="p-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition"
                                            title="تغيير الحالة">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- طباعة -->
                                <button onclick="window.print()" 
                                        class="p-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition"
                                        title="طباعة">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- الترقيم -->
        <?php if (($totalPages ?? 0) > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    عرض <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $total) ?> من <?= $total ?>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?action=list&page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>" 
                       class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        السابق
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?action=list&page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>" 
                       class="px-3 py-1 border rounded text-sm transition <?= $i === $page ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?action=list&page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>" 
                       class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        التالي
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
