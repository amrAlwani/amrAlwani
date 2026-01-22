<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">إدارة المستخدمين</h1>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg p-4 mb-6 flex flex-wrap gap-4">
    <form class="flex flex-1 gap-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="بحث بالاسم أو البريد..."
               class="flex-1 px-4 py-2 border rounded-lg">
        <select name="role" class="px-4 py-2 border rounded-lg">
            <option value="">كل الأدوار</option>
            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>مستخدم</option>
            <option value="vendor" <?= $role === 'vendor' ? 'selected' : '' ?>>بائع</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>مدير</option>
        </select>
        <button type="submit" class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300">بحث</button>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">المستخدم</th>
                <th class="text-right px-4 py-3">البريد</th>
                <th class="text-right px-4 py-3">الهاتف</th>
                <th class="text-right px-4 py-3">الدور</th>
                <th class="text-right px-4 py-3">الحالة</th>
                <th class="text-right px-4 py-3">تاريخ التسجيل</th>
                <th class="text-right px-4 py-3">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $roleLabels = ['user' => 'مستخدم', 'vendor' => 'بائع', 'admin' => 'مدير'];
            foreach ($users as $user): 
            ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                            <?= mb_substr($user['name'], 0, 1) ?>
                        </div>
                        <span class="font-medium"><?= htmlspecialchars($user['name']) ?></span>
                    </div>
                </td>
                <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                <td class="px-4 py-3"><?= $roleLabels[$user['role']] ?? $user['role'] ?></td>
                <td class="px-4 py-3">
                    <?php if ($user['is_active']): ?>
                    <span class="text-green-600">نشط</span>
                    <?php else: ?>
                    <span class="text-red-600">معطل</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                    <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/toggle') ?>" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="<?= $user['is_active'] ? 'text-red-600' : 'text-green-600' ?> hover:underline">
                            <?= $user['is_active'] ? 'تعطيل' : 'تفعيل' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($lastPage > 1): ?>
<div class="flex justify-center gap-2 mt-6">
    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
    <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
