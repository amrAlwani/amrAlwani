<h1 class="text-2xl font-bold mb-6">طلباتي</h1>

<?php if (empty($orders)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-6xl mb-4">📦</p>
    <p class="text-gray-500 text-xl mb-6">لا توجد طلبات بعد</p>
    <a href="<?= url('products') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg inline-block">ابدأ التسوق</a>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php 
    $statusLabels = [
        'pending' => ['text' => 'قيد الانتظار', 'class' => 'bg-yellow-100 text-yellow-800'],
        'processing' => ['text' => 'قيد المعالجة', 'class' => 'bg-blue-100 text-blue-800'],
        'shipped' => ['text' => 'تم الشحن', 'class' => 'bg-purple-100 text-purple-800'],
        'delivered' => ['text' => 'تم التوصيل', 'class' => 'bg-green-100 text-green-800'],
        'cancelled' => ['text' => 'ملغي', 'class' => 'bg-red-100 text-red-800'],
    ];
    foreach ($orders as $order): 
        $status = $statusLabels[$order['status']] ?? $statusLabels['pending'];
    ?>
    <div class="bg-white rounded-lg p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="font-bold text-lg">#<?= $order['order_number'] ?></h3>
                <p class="text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm <?= $status['class'] ?>">
                <?= $status['text'] ?>
            </span>
        </div>
        
        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t">
            <div>
                <p class="text-gray-600"><?= count($order['items'] ?? []) ?> منتج</p>
                <p class="font-bold text-blue-600"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></p>
            </div>
            <a href="<?= url('orders/' . $order['id']) ?>" class="text-blue-600 hover:underline">
                عرض التفاصيل ←
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-8">
    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
