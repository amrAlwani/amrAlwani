<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'لوحة التحكم' ?> - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold"><?= APP_NAME ?></h1>
                <p class="text-blue-200 text-sm">لوحة التحكم</p>
            </div>
            <nav class="p-4">
                <a href="<?= url('admin') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">📊 الرئيسية</a>
                <a href="<?= url('admin/orders') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">📦 الطلبات</a>
                <a href="<?= url('admin/products') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">🏷️ المنتجات</a>
                <a href="<?= url('admin/categories') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">📁 التصنيفات</a>
                <a href="<?= url('admin/users') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">👥 المستخدمين</a>
                <a href="<?= url('admin/coupons') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">🎟️ الكوبونات</a>
                <a href="<?= url('admin/settings') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">⚙️ الإعدادات</a>
                <hr class="my-4 border-blue-700">
                <a href="<?= url('/') ?>" class="block py-2 px-4 rounded hover:bg-blue-700 mb-1">🏠 المتجر</a>
                <a href="<?= url('logout') ?>" class="block py-2 px-4 rounded hover:bg-red-600 text-red-200">🚪 خروج</a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1 mr-64 p-8">
            <?php View::flash(); ?>
            <?= $content ?>
        </main>
    </div>
</body>
</html>
