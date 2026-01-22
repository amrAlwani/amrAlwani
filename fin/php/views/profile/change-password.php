<?php
/**
 * تغيير كلمة المرور
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير كلمة المرور - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="/profile.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة
        </a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">تغيير كلمة المرور</h1>
                    <p class="text-gray-500">حافظ على أمان حسابك</p>
                </div>
            </div>

            <form method="POST" action="/profile.php?action=change-password" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <!-- كلمة المرور الحالية -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الحالية</label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- كلمة المرور الجديدة -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الجديدة</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">6 أحرف على الأقل</p>
                </div>

                <!-- تأكيد كلمة المرور -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- الأزرار -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        تغيير كلمة المرور
                    </button>
                    <a href="/profile.php" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
