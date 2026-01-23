<?php
/**
 * SwiftCart - Front Controller
 * نقطة الدخول الرئيسية للتطبيق
 * محدّث مع AutoLoad و Namespaces
 */

// تعريف المسار الأساسي
define('BASEPATH', __DIR__);

// تحميل الإعدادات
require_once BASEPATH . '/config/config.php';
require_once BASEPATH . '/config/database.php';

// تحميل AutoLoader
require_once BASEPATH . '/autoload.php';

// تحميل الـ Core القديم (للتوافق)
require_once BASEPATH . '/core/Model.php';
require_once BASEPATH . '/core/Controller.php';
require_once BASEPATH . '/core/View.php';
require_once BASEPATH . '/core/Router.php';

// تحميل الـ Utils
require_once BASEPATH . '/utils/Response.php';
require_once BASEPATH . '/utils/Validator.php';
require_once BASEPATH . '/utils/Auth.php';
require_once BASEPATH . '/utils/CSRF.php';
require_once BASEPATH . '/utils/Security.php';

// إعدادات الجلسة الآمنة
$sessionConfig = require BASEPATH . '/config/session.php';

if (session_status() === PHP_SESSION_NONE) {
    // إعدادات الأمان
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', $sessionConfig['cookie_samesite'] ?? 'Strict');
    
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_name($sessionConfig['name'] ?? 'SWIFTCART_SESSION');
    session_start();
    
    // تجديد معرف الجلسة دورياً
    if (!isset($_SESSION['_last_regenerate'])) {
        $_SESSION['_last_regenerate'] = time();
    } elseif (time() - $_SESSION['_last_regenerate'] > ($sessionConfig['regenerate_interval'] ?? 1800)) {
        session_regenerate_id(true);
        $_SESSION['_last_regenerate'] = time();
    }
}

// إضافة Security Headers
if (!defined('SKIP_SECURITY_HEADERS')) {
    Security::setSecurityHeaders();
}

// تحميل المسارات
$router = new Router();
require_once BASEPATH . '/config/routes.php';

// معالجة الطلب
try {
    $router->dispatch();
} catch (Exception $e) {
    // تسجيل الخطأ
    error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    if (DEBUG_MODE) {
        echo '<div style="background:#fef2f2;border:1px solid #ef4444;padding:20px;margin:20px;border-radius:8px;font-family:Tajawal,sans-serif;direction:rtl;">';
        echo '<h2 style="color:#dc2626;margin:0 0 10px;">❌ خطأ في التطبيق</h2>';
        echo '<p><strong>الرسالة:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>الملف:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre style="background:#1f2937;color:#f3f4f6;padding:15px;border-radius:4px;overflow-x:auto;font-size:12px;">';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre></div>';
    } else {
        http_response_code(500);
        require_once BASEPATH . '/views/errors/500.php';
    }
}
