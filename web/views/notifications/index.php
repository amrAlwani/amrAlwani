<h1 class="text-2xl font-bold mb-6">الإشعارات</h1>

<?php if (empty($notifications)): ?>
<div class="bg-white rounded-lg p-12 text-center">
    <p class="text-6xl mb-4">🔔</p>
    <p class="text-gray-500 text-xl">لا توجد إشعارات</p>
</div>
<?php else: ?>
<div class="space-y-2">
    <?php foreach ($notifications as $notification): ?>
    <div class="bg-white rounded-lg p-4 flex gap-4 <?= $notification['is_read'] ? 'opacity-60' : '' ?>">
        <div class="flex-1">
            <h3 class="font-medium"><?= htmlspecialchars($notification['title']) ?></h3>
            <p class="text-gray-600 text-sm"><?= htmlspecialchars($notification['body'] ?? '') ?></p>
            <p class="text-gray-400 text-xs mt-1"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></p>
        </div>
        <?php if (!$notification['is_read']): ?>
        <a href="<?= url('notifications/' . $notification['id'] . '/read') ?>" class="text-blue-600 text-sm">
            تعيين كمقروء
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
