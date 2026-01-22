<?php
/**
 * SwiftCart - Configuration File
 * ملف الإعدادات الرئيسي
 * 
 * تم التصحيح: إضافة ثوابت مفقودة وتحسين الأمان
 */

// منع الوصول المباشر للملف
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__));
}

// ======== إعدادات التطبيق ========
define('APP_NAME', 'SwiftCart');
define('APP_URL', 'http://localhost/fin/php');
define('APP_VERSION', '1.0.0');

// ======== إعدادات قاعدة البيانات ========
define('DB_HOST', 'localhost');
define('DB_NAME', 'swiftcart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ======== إعدادات الرفع ========
define('UPLOAD_DIR', BASEPATH . '/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ======== إعدادات المتجر ========
define('TAX_RATE', 0.15);               // نسبة الضريبة (15%)
define('SHIPPING_COST', 25.00);         // تكلفة الشحن الثابتة
define('FREE_SHIPPING_THRESHOLD', 500); // حد الشحن المجاني
define('MIN_ORDER_VALUE', 50);          // الحد الأدنى للطلب

// ======== إعدادات العملة ========
define('CURRENCY', 'SAR');
define('CURRENCY_SYMBOL', 'ر.س');

// ======== إعدادات المنطقة الزمنية ========
date_default_timezone_set('Asia/Riyadh');

// ======== إعدادات JWT ========
// يجب تغيير هذا المفتاح في الإنتاج!
define('JWT_SECRET', 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2');
define('JWT_EXPIRY', 604800); // 7 أيام بالثواني

// ======== إعدادات التصحيح ========
// يجب تعطيلها في الإنتاج!
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ======== المسار الأساسي للتطبيق ========
// عدّل هذا حسب مسار مشروعك
define('BASE_URL', '/fin/php');

// ======== إعدادات CORS (للـ API فقط) ========
$isApiRequest = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;

if ($isApiRequest) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// ======== تعريف المتغيرات العامة ========
$GLOBALS['TAX_RATE'] = TAX_RATE;
$GLOBALS['SHIPPING_COST'] = SHIPPING_COST;
$GLOBALS['FREE_SHIPPING_THRESHOLD'] = FREE_SHIPPING_THRESHOLD;
$GLOBALS['MIN_ORDER_VALUE'] = MIN_ORDER_VALUE;
$GLOBALS['CURRENCY_SYMBOL'] = CURRENCY_SYMBOL;
