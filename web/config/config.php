<?php
/**
 * SwiftCart - Configuration File
 * ملف الإعدادات الرئيسي
 */

// ======== إعدادات التطبيق ========
define('APP_NAME', 'SwiftCart');
define('APP_VERSION', '2.0.0');

// ======== المسارات ========
// غيّر هذا المسار حسب مكان المشروع على السيرفر
// مثال: '/fin' أو '/web' أو '' للجذر
define('BASE_URL', '/web');
define('APP_URL', 'http://localhost' . BASE_URL);

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
define('TAX_RATE', 0.15);
define('SHIPPING_COST', 25.00);
define('FREE_SHIPPING_THRESHOLD', 500);
define('MIN_ORDER_VALUE', 50);

// ======== إعدادات العملة ========
define('CURRENCY', 'SAR');
define('CURRENCY_SYMBOL', 'ر.س');

// ======== إعدادات المنطقة الزمنية ========
date_default_timezone_set('Asia/Riyadh');

// ======== إعدادات JWT ========
define('JWT_SECRET', 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2');
define('JWT_EXPIRY', 604800);

// ======== إعدادات التصحيح ========
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ======== CORS Headers ========
$isApiRequest = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;

if ($isApiRequest) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// ======== Helper Functions ========
function url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path = ''): string {
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path = ''): void {
    header('Location: ' . url($path));
    exit;
}

function old(string $key, string $default = ''): string {
    return htmlspecialchars($_SESSION['old'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    return CSRF::getToken();
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}
