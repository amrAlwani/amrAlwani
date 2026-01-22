<?php
/**
 * صفحة المنتجات - SwiftCart
 */
session_start();
require_once 'includes/header.php';
require_once 'config/database.php';

$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'newest';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // جلب التصنيفات
    $categoriesStmt = $db->query("SELECT * FROM categories ORDER BY name");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // بناء استعلام المنتجات
    $where = ["1=1"];
    $params = [];
    
    if ($categoryId) {
        $where[] = "category_id = :category_id";
        $params['category_id'] = $categoryId;
    }
    
    if ($search) {
        $where[] = "(name LIKE :search OR description LIKE :search)";
        $params['search'] = "%{$search}%";
    }
    
    $whereClause = implode(' AND ', $where);
    
    // ترتيب
    $orderBy = match($sort) {
        'price_asc' => 'price ASC',
        'price_desc' => 'price DESC',
        'rating' => 'rating DESC',
        default => 'created_at DESC'
    };
    
    // عدد المنتجات
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE {$whereClause}");
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);
    
    // جلب المنتجات
    $sql = "SELECT * FROM products WHERE {$whereClause} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
    $productsStmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $productsStmt->bindValue(":{$key}", $value);
    }
    $productsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $productsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $productsStmt->execute();
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $categories = [];
    $products = [];
    $totalPages = 0;
}
?>

<div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="mr-2 text-xl font-bold text-gray-900">SwiftCart</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="cart.php" class="relative text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">لوحة التحكم</a>
                    <?php else: ?>
                    <a href="login.php" class="text-primary-600 hover:text-primary-700 font-medium">تسجيل الدخول</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 md:mb-0">المنتجات</h1>
            
            <!-- Search -->
            <form method="GET" class="flex items-center space-x-2 space-x-reverse">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="ابحث عن منتج...">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    بحث
                </button>
            </form>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar - Categories -->
            <div class="lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">التصنيفات</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="products.php" class="block px-3 py-2 rounded-lg <?= !$categoryId ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                                الكل
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="products.php?category=<?= $cat['id'] ?>" 
                               class="block px-3 py-2 rounded-lg <?= $categoryId == $cat['id'] ? 'bg-primary-100 text-primary-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Sort -->
                    <h3 class="font-semibold text-gray-900 mt-6 mb-4">الترتيب</h3>
                    <select onchange="window.location.href='products.php?sort='+this.value+'&category=<?= $categoryId ?>&search=<?= urlencode($search ?? '') ?>'"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>الأحدث</option>
                        <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>السعر: من الأقل</option>
                        <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>السعر: من الأعلى</option>
                        <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>التقييم</option>
                    </select>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="flex-1">
                <?php if (empty($products)): ?>
                <div class="text-center py-16">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">لا توجد منتجات</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <a href="product.php?id=<?= $product['id'] ?>">
                            <div class="aspect-square bg-gray-100 relative">
                                <?php if ($product['image']): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="w-full h-full object-cover">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                    خصم
                                </span>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-1 line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                            
                            <?php if ($product['rating'] > 0): ?>
                            <div class="flex items-center mb-2">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-sm text-gray-600 mr-1"><?= number_format($product['rating'], 1) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                                    <span class="text-sm text-gray-400 line-through"><?= number_format($product['price'], 2) ?> ر.س</span>
                                    <span class="text-lg font-bold text-primary-600"><?= number_format($product['sale_price'], 2) ?> ر.س</span>
                                    <?php else: ?>
                                    <span class="text-lg font-bold text-primary-600"><?= number_format($product['price'], 2) ?> ر.س</span>
                                    <?php endif; ?>
                                </div>
                                
                                <button onclick="addToCart(<?= $product['id'] ?>)" 
                                        class="p-2 bg-primary-100 text-primary-600 rounded-lg hover:bg-primary-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-8 space-x-2 space-x-reverse">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="products.php?page=<?= $i ?>&category=<?= $categoryId ?>&search=<?= urlencode($search ?? '') ?>&sort=<?= $sort ?>"
                       class="px-4 py-2 rounded-lg <?= $i == $page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    fetch('api/cart.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تمت الإضافة للسلة');
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
