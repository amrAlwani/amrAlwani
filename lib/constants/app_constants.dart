/// ثوابت التطبيق
class AppConstants {
  static const String keyTheme = 'theme';
  // Password Constraints
  static const int minPasswordLength = 8;
  static const int maxPasswordLength = 100;
  
  // Name Constraints
  static const int minNameLength = 2;
  static const int maxNameLength = 50;
  
  // Phone Constraints
  static const int phoneLength = 10;
  
  // Address Constraints
  static const int minAddressLength = 10;
  static const int maxAddressLength = 500;
  
  // Review Constraints
  static const int minReviewLength = 10;
  static const int maxReviewLength = 500;
  
  // Pagination
  static const int defaultPageSize = 20;
  static const int maxPageSize = 100;
  
  // Cache Duration
  static const int cacheDurationMinutes = 5;
  
  // Animation Duration
  static const int animationDurationMs = 300;
  static const int splashDurationSeconds = 2;
  
  // Order Status
  static const List<String> orderStatuses = [
    'pending',
    'processing',
    'shipped',
    'delivered',
    'cancelled',
  ];
  
  // Payment Methods
  static const List<String> paymentMethods = [
    'cod', // Cash on Delivery
    'card',
    'wallet',
  ];
  
  // Shipping
  static const double freeShippingThreshold = 200.0;
  static const double shippingCost = 25.0;
  static const double taxRate = 0.15; // 15% VAT
  
  // Image Sizes
  static const double thumbnailSize = 100.0;
  static const double productImageSize = 300.0;
  static const double avatarSize = 80.0;
  
  // Regex Patterns
  static const String emailPattern = r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$';
  static const String phonePattern = r'^(05|5)[0-9]{8}$';
  static const String postalCodePattern = r'^[0-9]{5}$';
}
