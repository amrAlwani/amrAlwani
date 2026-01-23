/// API Endpoints
/// جميع مسارات الـ API المستخدمة في التطبيق
class ApiEndpoints {
  // Auth
  static const String login = '/api/auth/login';
  static const String register = '/api/auth/register';
  static const String socialLogin = '/api/auth/socialLogin'; // <-- تم التحديث
  static const String profile = '/api/auth/profile';
  static const String changePassword = '/api/auth/password';
  
  // Products
  static const String products = '/api/products';
  static const String productsFeatured = '/api/products/featured';
  static String productDetails(int id) => '/api/products/$id';
  
  // Categories
  static const String categories = '/api/categories';
  static const String categoriesTree = '/api/categories/tree';
  static String categoryDetails(int id) => '/api/categories/$id';
  
  // Cart
  static const String cart = '/api/cart';
  static const String cartAdd = '/api/cart/add';
  static const String cartUpdate = '/api/cart/update';
  static const String cartClear = '/api/cart/clear';
  static const String cartCoupon = '/api/cart/coupon';
  static String cartRemove(int id) => '/api/cart/$id';
  
  // Orders
  static const String orders = '/api/orders';
  static String orderDetails(int id) => '/api/orders/$id';
  static String orderCancel(int id) => '/api/orders/$id/cancel';
  static String orderTrack(String number) => '/api/orders/$number/track';
  
  // Wishlist
  static const String wishlist = '/api/wishlist';
  static const String wishlistToggle = '/api/wishlist';
  static String wishlistRemove(int id) => '/api/wishlist/$id';
  
  // Addresses
  static const String addresses = '/api/addresses';
  static String addressUpdate(int id) => '/api/addresses/$id';
  static String addressDelete(int id) => '/api/addresses/$id';
  
  // Reviews
  static const String reviews = '/api/reviews';
  static String productReviews(int productId) => '/api/reviews/product/$productId';
  
  // Notifications
  static const String notifications = '/api/notifications';
  static String notificationRead(int id) => '/api/notifications/$id/read';
  static const String notificationsReadAll = '/api/notifications/read-all';
  
  // Coupons
  static const String couponValidate = '/api/coupons/validate';
  
  // Upload
  static const String upload = '/api/upload';
}
