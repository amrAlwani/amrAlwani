<?php
/**
 * تعديل الملف الشخصي
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الملف الشخصي - <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="/profile.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة
        </a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">تعديل الملف الشخصي</h1>

            <form method="POST" action="/profile.php?action=update" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <!-- الصورة الرمزية -->
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        <?= mb_substr($user['name'] ?? 'U', 0, 1, 'UTF-8') ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الصورة الرمزية</label>
                        <input type="file" name="avatar" accept="image/*" 
                               class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100">
                    </div>
                </div>

                <!-- الاسم -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- البريد (غير قابل للتعديل) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500">
                    <p class="mt-1 text-sm text-gray-500">لا يمكن تغيير البريد الإلكتروني</p>
                </div>

                <!-- الهاتف -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" dir="ltr"
                           placeholder="05xxxxxxxx"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- الأزرار -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        حفظ التغييرات
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
