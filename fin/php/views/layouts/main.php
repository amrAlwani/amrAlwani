<?php
// تضمين CSRF
require_once __DIR__ . '/../../utils/CSRF.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SwiftCart' ?></title>
    <!-- CSRF Meta Tag for AJAX -->
    <?= CSRF::metaTag() ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="text-2xl font-bold text-indigo-600">
                    <i class="fas fa-store ml-2"></i>
                    SwiftCart
                </a>

                <!-- Search -->
                <div class="hidden md:flex flex-1 max-w-xl mx-8">
                    <form action="/products.php" method="GET" class="w-full">
                        <div class="relative">
                            <input type="text" name="search" placeholder="ابحث عن منتجات..." 
                                   class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center gap-4">
                    <a href="/products.php" class="text-gray-600 hover:text-indigo-600">المنتجات</a>
                    
                    <?php if (isset($user)): ?>
                        <a href="/cart.php" class="relative text-gray-600 hover:text-indigo-600">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center cart-count">0</span>
                        </a>
                        <div class="relative">
                            <button data-dropdown="user-menu" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span class="hidden md:inline"><?= htmlspecialchars($user['name']) ?></span>
                            </button>
                            <div id="user-menu" class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt ml-2"></i> لوحة التحكم
                                </a>
                                <a href="/orders.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-shopping-bag ml-2"></i> طلباتي
                                </a>
                                <a href="/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user ml-2"></i> الملف الشخصي
                                </a>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <hr class="my-2">
                                    <a href="/admin/dashboard.php" class="block px-4 py-2 text-indigo-600 hover:bg-gray-100">
                                        <i class="fas fa-cog ml-2"></i> لوحة الإدارة
                                    </a>
                                <?php endif; ?>
                                <hr class="my-2">
                                <a href="/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt ml-2"></i> تسجيل الخروج
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="text-gray-600 hover:text-indigo-600">تسجيل الدخول</a>
                        <a href="/register.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            إنشاء حساب
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $_SESSION['flash']['message'] ?>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="min-h-screen">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">SwiftCart</h3>
                    <p class="text-gray-400">متجرك الإلكتروني المفضل للتسوق بأفضل الأسعار وأعلى جودة.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/" class="hover:text-white">الرئيسية</a></li>
                        <li><a href="/products.php" class="hover:text-white">المنتجات</a></li>
                        <li><a href="/about.php" class="hover:text-white">من نحن</a></li>
                        <li><a href="/contact.php" class="hover:text-white">اتصل بنا</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">خدمة العملاء</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/faq.php" class="hover:text-white">الأسئلة الشائعة</a></li>
                        <li><a href="/shipping.php" class="hover:text-white">الشحن والتوصيل</a></li>
                        <li><a href="/returns.php" class="hover:text-white">الإرجاع والاستبدال</a></li>
                        <li><a href="/privacy.php" class="hover:text-white">سياسة الخصوصية</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">تواصل معنا</h4>
                    <div class="space-y-2 text-gray-400">
                        <p><i class="fas fa-phone ml-2"></i> +966 12 345 6789</p>
                        <p><i class="fas fa-envelope ml-2"></i> support@swiftcart.com</p>
                        <div class="flex gap-4 mt-4">
                            <a href="#" class="text-xl hover:text-indigo-400"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-xl hover:text-indigo-400"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-xl hover:text-indigo-400"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-8 border-gray-700">
            <p class="text-center text-gray-400">
                &copy; <?= date('Y') ?> SwiftCart. جميع الحقوق محفوظة.
            </p>
        </div>
    </footer>

    <script>
        // Dropdown toggles
        document.querySelectorAll('[data-dropdown]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = document.getElementById(btn.dataset.dropdown);
                dropdown.classList.toggle('hidden');
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
        });
    </script>
</body>
</html>
