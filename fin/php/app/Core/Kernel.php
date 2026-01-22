<?php

namespace App\Core;

/**
 * فئة Kernel - النواة الرئيسية للتطبيق
 * مسؤولة عن معالجة جميع الطلبات الواردة وتوجيهها
 */
class Kernel
{
    /**
     * معالجة الطلب الوارد (Entry Point للتطبيق)
     * هذه الدالة هي نقطة البداية لمعالجة أي طلب يصل للتطبيق
     */
    public function handle(): void
    {
        // إعداد رؤوس CORS (Cross-Origin Resource Sharing)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // معالجة طلبات Preflight (طلبات OPTIONS المسبقة)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // استخراج الـ URI (المسار المطلوب) من الطلب
        $uri = $_GET['url'] ?? '';

        // تنظيف وتأمين الـ URI
        $uri = trim(filter_var($uri, FILTER_SANITIZE_URL), '/');

        // توجيه الطلب إلى الراوتر المناسب بناءً على نوع الطلب
        if (str_starts_with($uri, 'api/')) {
            // إذا بدأ المسار بـ api/ نستخدم راوتر الـ API
            $router = require Path::routes('api.php');
        } else {
            // وإلا نستخدم راوتر الويب العادي
            $router = require Path::routes('web.php');
        }

        // توجيه الطلب إلى الوجهة النهائية
        $router->direct($uri, $_SERVER['REQUEST_METHOD']);
    }
}
