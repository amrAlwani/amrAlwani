<?php
/**
 * SwiftCart - نقطة الدخول الرئيسية
 * Entry Point for the MVC Application
 */

// تحديد المسار الأساسي للتطبيق
define('BASEPATH', __DIR__);

// تحميل autoloader
require_once BASEPATH . '/app/Core/Autoloader.php';

// تهيئة التحميل التلقائي
Autoloader::register();

// تشغيل التطبيق
$app = new App\Core\Application();
$app->run();
