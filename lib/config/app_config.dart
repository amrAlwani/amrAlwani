
class AppConfig {
  static const String appName = 'SwiftCart';
  static const String appVersion = '1.0.0';

  // ⚠️ مهم: قم بتغيير هذا العنوان إلى عنوان سيرفرك الحقيقي
  // للتطوير المحلي يمكنك استخدام:
  // - Android Emulator: http://10.0.2.2/web
  // - iOS Simulator: http://localhost/web
  // - جهاز حقيقي: http://YOUR_IP_ADDRESS/web
  // ملاحظة: لا تضف /api/ هنا - يتم إضافتها في الـ endpoints
  static const String apiBaseUrl = 'http://192.168.8.120/web';

  // رابط الصور
  static const String imagesBaseUrl = 'http://192.168.8.120/web/uploads/';
  static const String placeholderImage = 'assets/images/placeholder.png';

  // العملة
  static const String currency = 'ريال سعودي';
  static const String currencyCode = 'SAR';

  // إعدادات التخزين المؤقت
  static const int cacheMaxAge = 7; // أيام
  static const int maxCacheSize = 100; // ميجابايت

  // مهلة الاتصال بالـ API
  static const int connectionTimeout = 30000; // ميلي ثانية
  static const int receiveTimeout = 30000; // ميلي ثانية

  // مفاتيح التخزين المحلي
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String themeKey = 'theme_mode';
  static const String languageKey = 'language';
  static const String introSeenKey = 'intro_seen';

  // إعدادات الصفحات
  static const int productsPerPage = 20;
  static const int reviewsPerPage = 10;

  // ألوان التطبيق
  static const int primaryColorValue = 0xFF2196F3;
  static const int secondaryColorValue = 0xFF03DAC6;
  static const int errorColorValue = 0xFFB00020;
}
