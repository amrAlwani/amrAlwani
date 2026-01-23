<?php
/**
 * Routes - تعريف مسارات التطبيق
 * يدعم Controllers الجديدة مع Namespaces والقديمة للتوافق
 */

// ===== الصفحات العامة =====
// استخدام App\Controllers الجديدة
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/products/{slug}', 'ProductController@show');
$router->get('/categories', 'CategoryController@index');
$router->get('/categories/{slug}', 'CategoryController@show');
$router->get('/search', 'ProductController@search');

// ===== صفحات الضيوف =====
$router->group('', function($router) {
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/register', 'AuthController@showRegister');
    $router->post('/register', 'AuthController@register');
    $router->get('/forgot-password', 'AuthController@showForgotPassword');
    $router->post('/forgot-password', 'AuthController@forgotPassword');
}, ['guest']);

// ===== صفحات المستخدم المسجل =====
$router->group('', function($router) {
    $router->get('/logout', 'AuthController@logout');
    $router->get('/dashboard', 'UserController@dashboard');
    $router->get('/profile', 'UserController@profile');
    $router->post('/profile', 'UserController@updateProfile');
    $router->get('/orders', 'OrderController@index');
    $router->get('/orders/{id}', 'OrderController@show');
    $router->get('/cart', 'CartController@index');
    $router->post('/cart/add', 'CartController@add');
    $router->post('/cart/update', 'CartController@update');
    $router->get('/cart/remove/{id}', 'CartController@remove');
    $router->get('/checkout', 'CheckoutController@index');
    $router->post('/checkout', 'CheckoutController@process');
    $router->get('/order-success/{id}', 'CheckoutController@success');
    $router->get('/wishlist', 'WishlistController@index');
    $router->post('/wishlist/toggle', 'WishlistController@toggle');
    $router->get('/addresses', 'AddressController@index');
    $router->post('/addresses', 'AddressController@store');
    $router->post('/addresses/{id}', 'AddressController@update');
    $router->get('/addresses/{id}/delete', 'AddressController@delete');
    $router->get('/notifications', 'NotificationController@index');
    $router->get('/notifications/{id}/read', 'NotificationController@markRead');
}, ['auth']);

// ===== لوحة التحكم =====
$router->group('/admin', function($router) {
    $router->get('', 'AdminController@dashboard');
    $router->get('/orders', 'AdminController@orders');
    $router->get('/orders/{id}', 'AdminController@orderDetails');
    $router->post('/orders/{id}/status', 'AdminController@updateOrderStatus');
    $router->get('/products', 'AdminController@products');
    $router->get('/products/create', 'AdminController@createProduct');
    $router->post('/products/create', 'AdminController@storeProduct');
    $router->get('/products/{id}/edit', 'AdminController@editProduct');
    $router->post('/products/{id}/edit', 'AdminController@updateProduct');
    $router->get('/products/{id}/delete', 'AdminController@deleteProduct');
    $router->get('/categories', 'AdminController@categories');
    $router->post('/categories', 'AdminController@storeCategory');
    $router->get('/users', 'AdminController@users');
    $router->post('/users/{id}/toggle', 'AdminController@toggleUser');
    $router->get('/coupons', 'AdminController@coupons');
    $router->post('/coupons', 'AdminController@storeCoupon');
    $router->get('/settings', 'AdminController@settings');
    $router->post('/settings', 'AdminController@updateSettings');
}, ['admin']);

// ===== API Routes =====
$router->group('/api', function($router) {
    // Auth API
    $router->post('/auth/login', 'Api\AuthController@login');
    $router->post('/auth/register', 'Api\AuthController@register');
    $router->get('/auth/profile', 'Api\AuthController@profile');
    $router->post('/auth/profile', 'Api\AuthController@updateProfile');
    $router->post('/auth/password', 'Api\AuthController@changePassword');
    
    // Products API
    $router->get('/products', 'Api\ProductController@index');
    $router->get('/products/featured', 'Api\ProductController@featured');
    $router->get('/products/{id}', 'Api\ProductController@show');
    
    // Categories API
    $router->get('/categories', 'Api\CategoryController@index');
    $router->get('/categories/tree', 'Api\CategoryController@tree');
    $router->get('/categories/{id}', 'Api\CategoryController@show');
    
    // Cart API (auth required)
    $router->get('/cart', 'Api\CartController@index');
    $router->post('/cart/add', 'Api\CartController@add');
    $router->post('/cart/update', 'Api\CartController@update');
    $router->delete('/cart/{id}', 'Api\CartController@remove');
    $router->post('/cart/clear', 'Api\CartController@clear');
    $router->post('/cart/coupon', 'Api\CartController@applyCoupon');
    
    // Orders API (auth required)
    $router->get('/orders', 'Api\OrderController@index');
    $router->get('/orders/{id}', 'Api\OrderController@show');
    $router->post('/orders', 'Api\OrderController@store');
    $router->post('/orders/{id}/cancel', 'Api\OrderController@cancel');
    $router->get('/orders/{number}/track', 'Api\OrderController@track');
    
    // Wishlist API (auth required)
    $router->get('/wishlist', 'Api\WishlistController@index');
    $router->post('/wishlist', 'Api\WishlistController@toggle');
    $router->delete('/wishlist/{id}', 'Api\WishlistController@remove');
    
    // Addresses API (auth required)
    $router->get('/addresses', 'Api\AddressController@index');
    $router->post('/addresses', 'Api\AddressController@store');
    $router->put('/addresses/{id}', 'Api\AddressController@update');
    $router->delete('/addresses/{id}', 'Api\AddressController@delete');
    
    // Reviews API
    $router->get('/reviews/product/{id}', 'Api\ReviewController@byProduct');
    $router->post('/reviews', 'Api\ReviewController@store');
    
    // Notifications API (auth required)
    $router->get('/notifications', 'Api\NotificationController@index');
    $router->post('/notifications/{id}/read', 'Api\NotificationController@markRead');
    $router->post('/notifications/read-all', 'Api\NotificationController@markAllRead');
    
    // Coupons API
    $router->post('/coupons/validate', 'Api\CouponController@validate');
    
    // Upload API (auth required)
    $router->post('/upload', 'Api\UploadController@store');
    
    // Admin API
    $router->get('/admin/dashboard', 'Api\AdminController@dashboard');
    $router->get('/admin/orders', 'Api\AdminController@orders');
    $router->post('/admin/orders/{id}/status', 'Api\AdminController@updateOrderStatus');
    $router->get('/admin/products', 'Api\AdminController@products');
    $router->post('/admin/products', 'Api\AdminController@storeProduct');
    $router->put('/admin/products/{id}', 'Api\AdminController@updateProduct');
    $router->delete('/admin/products/{id}', 'Api\AdminController@deleteProduct');
    $router->get('/admin/users', 'Api\AdminController@users');
    $router->post('/admin/users/{id}/toggle', 'Api\AdminController@toggleUser');
    $router->get('/admin/stats', 'Api\AdminController@stats');
}, ['api']);
