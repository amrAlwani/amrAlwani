<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'لوحة التحكم' ?> - SwiftCart Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .sidebar-link.active { background-color: rgba(99, 102, 241, 0.1); color: #6366f1; border-right: 3px solid #6366f1; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg fixed h-full">
            <div class="p-6 border-b">
                <h1 class="text-2xl font-bold text-indigo-600">
                    <i class="fas fa-store ml-2"></i>
                    SwiftCart
                </h1>
                <p class="text-sm text-gray-500 mt-1">لوحة التحكم</p>
            </div>
            
            <nav class="mt-6">
                <a href="/admin/dashboard.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="mr-3">الرئيسية</span>
                </a>
                <a href="/admin/products.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= strpos($_SERVER['REQUEST_URI'], 'products') !== false ? 'active' : '' ?>">
                    <i class="fas fa-box w-5"></i>
                    <span class="mr-3">المنتجات</span>
                </a>
                <a href="/admin/orders.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= strpos($_SERVER['REQUEST_URI'], 'orders') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="mr-3">الطلبات</span>
                </a>
                <a href="/admin/users.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users w-5"></i>
                    <span class="mr-3">المستخدمين</span>
                </a>
                <a href="/admin/categories.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= strpos($_SERVER['REQUEST_URI'], 'categories') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tags w-5"></i>
                    <span class="mr-3">التصنيفات</span>
                </a>
                
                <div class="border-t mt-4 pt-4">
                    <a href="/admin/settings.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog w-5"></i>
                        <span class="mr-3">الإعدادات</span>
                    </a>
                    <a href="/admin/logs.php" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-history w-5"></i>
                        <span class="mr-3">سجل النظام</span>
                    </a>
                </div>
            </nav>
            
            <!-- User Info -->
            <div class="absolute bottom-0 w-full p-4 border-t bg-gray-50">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white">
                        <?= mb_substr($user['name'] ?? 'A', 0, 1) ?>
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="text-sm font-medium"><?= htmlspecialchars($user['name'] ?? 'المدير') ?></p>
                        <p class="text-xs text-gray-500">مدير</p>
                    </div>
                    <a href="/logout.php" class="text-gray-400 hover:text-red-500">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 mr-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800"><?= $title ?? 'لوحة التحكم' ?></h2>
                <div class="flex items-center gap-4">
                    <a href="/" target="_blank" class="text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-external-link-alt"></i>
                        عرض الموقع
                    </a>
                    <button class="relative text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </button>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="mx-6 mt-4">
                    <div class="p-4 rounded-lg <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $_SESSION['flash']['message'] ?>
                    </div>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="p-6">
                <?= $content ?>
            </div>
        </main>
    </div>

    <script>
        // Dropdown toggles
        document.querySelectorAll('[data-dropdown]').forEach(btn => {
            btn.addEventListener('click', () => {
                const dropdown = document.getElementById(btn.dataset.dropdown);
                dropdown.classList.toggle('hidden');
            });
        });

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('[data-dropdown]')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
            }
        });
    </script>
</body>
</html>
