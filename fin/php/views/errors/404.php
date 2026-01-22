<?php
/**
 * صفحة 404 - غير موجود
 * 404 Not Found Page
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة | <?= APP_NAME ?? 'SwiftCart' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl mx-auto text-center">
        <!-- رقم 404 -->
        <div class="animate-float mb-8">
            <div class="relative inline-block">
                <span class="text-[180px] font-bold text-purple-200 leading-none select-none">404</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-32 h-32 text-purple-600 animate-pulse-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- العنوان والوصف -->
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            عذراً! الصفحة غير موجودة
        </h1>
        <p class="text-xl text-gray-600 mb-8 max-w-md mx-auto">
            يبدو أن الصفحة التي تبحث عنها قد انتقلت أو لم تعد موجودة أو ربما لم تكن موجودة من الأساس.
        </p>

        <!-- اقتراحات -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">ماذا يمكنك فعله؟</h2>
            <ul class="text-right text-gray-600 space-y-3">
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    تحقق من صحة الرابط الذي أدخلته
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    استخدم شريط البحث للعثور على ما تبحث عنه
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    عد إلى الصفحة الرئيسية وابدأ من جديد
                </li>
            </ul>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition transform hover:scale-105 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                الصفحة الرئيسية
            </a>
            <a href="/products.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-purple-600 border-2 border-purple-600 rounded-xl font-bold hover:bg-purple-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                تصفح المنتجات
            </a>
            <button onclick="history.back()" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                العودة للخلف
            </button>
        </div>

        <!-- معلومات إضافية -->
        <p class="mt-12 text-sm text-gray-500">
            إذا كنت تعتقد أن هذا خطأ، يرجى 
            <a href="/contact.php" class="text-purple-600 hover:underline">التواصل معنا</a>
        </p>
    </div>
</body>
</html>
