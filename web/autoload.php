<?php
/**
 * SwiftCart - AutoLoader
 * تحميل تلقائي للكلاسات
 * تم التحديث: إزالة الـ Namespaces غير المستخدمة
 */

spl_autoload_register(function ($className) {
    // مسارات البحث عن الكلاسات
    $paths = [
        BASEPATH . '/core/',
        BASEPATH . '/models/',
        BASEPATH . '/controllers/',
        BASEPATH . '/controllers/Api/',
        BASEPATH . '/utils/',
    ];
    
    // معالجة Namespace للـ API Controllers
    if (strpos($className, 'Api\\') === 0) {
        $relativeClass = substr($className, 4); // إزالة 'Api\'
        $file = BASEPATH . '/controllers/Api/' . $relativeClass . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // البحث في المسارات العادية
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});
