<h1 class="text-2xl font-bold mb-6">الملف الشخصي</h1>

<div class="max-w-2xl">
    <form method="POST" action="<?= url('profile') ?>" class="bg-white rounded-lg p-6">
        <?= csrf_field() ?>
        
        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 mb-2">الاسم</label>
                <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">البريد الإلكتروني</label>
                <input type="email" value="<?= htmlspecialchars($profile['email']) ?>" disabled
                       class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500">
                <p class="text-gray-500 text-sm mt-1">لا يمكن تغيير البريد الإلكتروني</p>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">رقم الهاتف</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <hr class="my-6">

        <h3 class="font-bold mb-4">تغيير كلمة المرور (اختياري)</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 mb-2">كلمة المرور الحالية</label>
                <input type="password" name="current_password"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">كلمة المرور الجديدة</label>
                <input type="password" name="new_password" minlength="8"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">تأكيد كلمة المرور الجديدة</label>
                <input type="password" name="new_password_confirmation"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg mt-6 hover:bg-blue-700 font-bold">
            حفظ التغييرات
        </button>
    </form>
</div>
