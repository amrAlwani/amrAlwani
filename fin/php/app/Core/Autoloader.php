<?php
/**
 * فئة Autoloader - مسؤولة عن التحميل التلقائي للكلاسات
 */
class Autoloader
{
    /**
     * تسجيل الـ autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * تحميل الكلاس تلقائياً
     * @param string $className اسم الكلاس الكامل
     */
    public static function load(string $className): void
    {
        // تحويل namespace إلى مسار ملف
        // مثال: App\Core\Router -> app/Core/Router.php
        $file = str_replace('\\', '/', $className) . '.php';
        
        // تحويل App إلى app
        $file = preg_replace('/^App\//', 'app/', $file);
        
        // المسار الكامل
        $fullPath = dirname(__DIR__) . '/' . $file;
        
        // التحقق من وجود الملف وتحميله
        if (file_exists($fullPath)) {
            require_once $fullPath;
        }
    }
}
