<?php
$statusLabels = [
    'pending' => ['text' => 'قيد الانتظار', 'class' => 'bg-yellow-100 text-yellow-800'],
    'processing' => ['text' => 'قيد المعالجة', 'class' => 'bg-blue-100 text-blue-800'],
    'shipped' => ['text' => 'تم الشحن', 'class' => 'bg-purple-100 text-purple-800'],
    'delivered' => ['text' => 'تم التوصيل', 'class' => 'bg-green-100 text-green-800'],
    'cancelled' => ['text' => 'ملغي', 'class' => 'bg-red-100 text-red-800'],
];
?>

<h1 class="text-2xl font-bold mb-6">إدارة الطلبات</h1>

<!-- Filters -->
<div class="bg-white rounded-lg p-4 mb-6 flex flex-wrap gap-2">
    <a href="<?= url('admin/orders') ?>" 
       class="px-4 py-2 rounded-lg <?= empty($currentStatus) ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
        الكل
    </a>
    <?php foreach ($statusLabels as $key => $label): ?>
    <a href="<?= url('admin/orders?status=' . $key) ?>" 
       class="px-4 py-2 rounded-lg <?= $currentStatus === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
        <?= $label['text'] ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">رقم الطلب</th>
                <th class="text-right px-4 py-3">العميل</th>
                <th class="text-right px-4 py-3">المبلغ</th>
                <th class="text-right px-4 py-3">الحالة</th>
                <th class="text-right px-4 py-3">التاريخ</th>
                <th class="text-right px-4 py-3">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): 
                $status = $statusLabels[$order['status']] ?? $statusLabels['pending'];
            ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#<?= $order['order_number'] ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($order['user_name'] ?? 'مجهول') ?></td>
                <td class="px-4 py-3"><?= number_format($order['total'], 2) ?> <?= CURRENCY_SYMBOL ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-sm <?= $status['class'] ?>"><?= $status['text'] ?></span>
                </td>
                <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <a href="<?= url('admin/orders/' . $order['id']) ?>" class="text-blue-600 hover:underline">عرض</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
    <a href="?page=<?= $i ?><?= $currentStatus ? '&status=' . $currentStatus : '' ?>" 
       class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
