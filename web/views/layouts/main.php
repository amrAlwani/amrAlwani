<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($description ?? APP_NAME . ' - تسوق أفضل المنتجات') ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Security Headers via Meta -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?= CSRF::getMetaTag() ?>
    
    <title><?= htmlspecialchars($title ?? APP_NAME) ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Skip to Content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:right-0 bg-primary-600 text-white p-2">
        تخطي إلى المحتوى
    </a>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top Bar -->
            <div class="hidden md:flex items-center justify-between py-2 text-sm text-gray-600 border-b">
                <div class="flex items-center gap-4">
                    <span>📞 920000000</span>
                    <span>✉️ support@swiftcart.com</span>
                </div>
                <div class="flex items-center gap-4">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="<?= url('notifications') ?>" class="hover:text-primary-600">🔔 الإشعارات</a>
                        <a href="<?= url('wishlist') ?>" class="hover:text-primary-600">❤️ المفضلة</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Main Nav -->
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="<?= url('/') ?>" class="text-2xl font-bold text-primary-600 flex items-center gap-2">
                    <span>🛒</span>
                    <span><?= APP_NAME ?></span>
                </a>
                
                <!-- Search -->
                <form action="<?= url('search') ?>" method="GET" class="hidden md:flex flex-1 max-w-xl mx-8">
                    <div class="relative w-full">
                        <input type="search" name="q" placeholder="ابحث عن منتجات..." 
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                               class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               autocomplete="off">
                        <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-600">
                            🔍
                        </button>
                    </div>
                </form>
                
                <!-- Navigation -->
                <nav class="hidden lg:flex items-center gap-6">
                    <a href="<?= url('/') ?>" class="hover:text-primary-600 transition">الرئيسية</a>
                    <a href="<?= url('products') ?>" class="hover:text-primary-600 transition">المنتجات</a>
                    <a href="<?= url('categories') ?>" class="hover:text-primary-600 transition">التصنيفات</a>
                </nav>
                
                <!-- User Actions -->
                <div class="flex items-center gap-4">
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Cart -->
                        <a href="<?= url('cart') ?>" class="relative hover:text-primary-600 transition">
                            <span class="text-2xl">🛒</span>
                            <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                <?= min($_SESSION['cart_count'], 99) ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- User Menu -->
                        <div class="relative group">
                            <button class="flex items-center gap-2 hover:text-primary-600 transition">
                                <span class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 font-bold">
                                    <?= mb_substr($_SESSION['user']['name'], 0, 1) ?>
                                </span>
                                <span class="hidden md:inline"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                            </button>
                            
                            <!-- Dropdown -->
                            <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <a href="<?= url('admin') ?>" class="block px-4 py-2 hover:bg-gray-50">⚙️ لوحة التحكم</a>
                                <hr>
                                <?php endif; ?>
                                <a href="<?= url('dashboard') ?>" class="block px-4 py-2 hover:bg-gray-50">📊 حسابي</a>
                                <a href="<?= url('orders') ?>" class="block px-4 py-2 hover:bg-gray-50">📦 طلباتي</a>
                                <a href="<?= url('wishlist') ?>" class="block px-4 py-2 hover:bg-gray-50">❤️ المفضلة</a>
                                <a href="<?= url('profile') ?>" class="block px-4 py-2 hover:bg-gray-50">👤 الملف الشخصي</a>
                                <hr>
                                <a href="<?= url('logout') ?>" class="block px-4 py-2 text-red-600 hover:bg-red-50">🚪 تسجيل خروج</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login') ?>" class="text-gray-700 hover:text-primary-600 transition">دخول</a>
                        <a href="<?= url('register') ?>" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                            تسجيل
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button id="mobile-menu-btn" class="lg:hidden text-2xl">☰</button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <!-- Mobile Search -->
                <form action="<?= url('search') ?>" method="GET">
                    <input type="search" name="q" placeholder="ابحث..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </form>
                
                <!-- Mobile Nav -->
                <nav class="space-y-2">
                    <a href="<?= url('/') ?>" class="block py-2 hover:text-primary-600">الرئيسية</a>
                    <a href="<?= url('products') ?>" class="block py-2 hover:text-primary-600">المنتجات</a>
                    <a href="<?= url('categories') ?>" class="block py-2 hover:text-primary-600">التصنيفات</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="container mx-auto px-4 mt-4">
        <?php View::flash(); ?>
    </div>

    <!-- Main Content -->
    <main id="main-content" class="flex-grow container mx-auto px-4 py-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4">
            <!-- Footer Top -->
            <div class="grid md:grid-cols-4 gap-8 py-12">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-bold mb-4"><?= APP_NAME ?></h3>
                    <p class="text-gray-400 mb-4">متجرك الإلكتروني الموثوق للتسوق بأفضل الأسعار وأعلى الجودة.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">📘</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">📷</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">🐦</a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?= url('/') ?>" class="hover:text-white transition">الرئيسية</a></li>
                        <li><a href="<?= url('products') ?>" class="hover:text-white transition">المنتجات</a></li>
                        <li><a href="<?= url('categories') ?>" class="hover:text-white transition">التصنيفات</a></li>
                        <li><a href="#" class="hover:text-white transition">من نحن</a></li>
                    </ul>
                </div>
                
                <!-- Help -->
                <div>
                    <h4 class="font-bold mb-4">المساعدة</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">الأسئلة الشائعة</a></li>
                        <li><a href="#" class="hover:text-white transition">سياسة الشحن</a></li>
                        <li><a href="#" class="hover:text-white transition">سياسة الإرجاع</a></li>
                        <li><a href="#" class="hover:text-white transition">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="font-bold mb-4">تواصل معنا</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📍 الرياض، المملكة العربية السعودية</li>
                        <li>📞 920000000</li>
                        <li>✉️ support@swiftcart.com</li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="border-t border-gray-700 py-6 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. جميع الحقوق محفوظة.</p>
                <div class="mt-2 flex justify-center gap-4 text-sm">
                    <a href="#" class="hover:text-white transition">سياسة الخصوصية</a>
                    <a href="#" class="hover:text-white transition">شروط الاستخدام</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
