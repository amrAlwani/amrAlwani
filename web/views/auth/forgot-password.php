<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">استعادة كلمة المرور</h1>
    <form method="POST" action="<?= url('forgot-password') ?>">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">البريد الإلكتروني</label>
            <input type="email" name="email" required
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                   placeholder="أدخل بريدك الإلكتروني">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
            إرسال رابط الاستعادة
        </button>
    </form>
    <p class="text-center mt-4 text-gray-600">
        تذكرت كلمة المرور؟ <a href="<?= url('login') ?>" class="text-blue-600">تسجيل الدخول</a>
    </p>
</div>
