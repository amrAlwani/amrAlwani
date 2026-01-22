<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center py-20 px-4">
        <div class="text-8xl mb-6">🔍</div>
        <h1 class="text-6xl font-bold text-blue-600 mb-4">404</h1>
        <p class="text-xl text-gray-600 mb-8">عذراً، الصفحة التي تبحث عنها غير موجودة</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/fin' ?>/" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">
            🏠 العودة للرئيسية
        </a>
    </div>
</body>
</html>
