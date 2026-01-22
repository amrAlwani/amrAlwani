<?php

namespace App\Core;

/**
 * فئة Path - مسؤولة عن إدارة المسارات والروابط في المشروع
 */
class Path
{
    /**
     * الحصول على المسار الجذري للمشروع
     */
    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * الحصول على مسار مجلد الإعدادات (config)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function config(string $file = ''): string
    {
        return self::root() . '/config' . ($file ? '/' . $file : '');
    }

    /**
     * الحصول على مسار مجلد العروض (views)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function views(string $file = ''): string
    {
        return self::root() . '/app/Views' . ($file ? '/' . $file : '');
    }

    /**
     * الحصول على مسار المجلد العام (public)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function public(string $file = ''): string
    {
        return self::root() . '/public' . ($file ? '/' . $file : '');
    }

    /**
     * الحصول على مسار مجلد المسارات (routes)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function routes(string $file = ''): string
    {
        return self::root() . '/routes' . ($file ? '/' . $file : '');
    }

    /**
     * الحصول على مسار مجلد التخزين (storage)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function storage(string $file = ''): string
    {
        return self::root() . '/storage' . ($file ? '/' . $file : '');
    }

    /**
     * الحصول على مسار مجلد التحميلات (uploads)
     * @param string $file اسم الملف المطلوب (اختياري)
     */
    public static function uploads(string $file = ''): string
    {
        return self::root() . '/public/uploads' . ($file ? '/' . $file : '');
    }

    /**
     * إنشاء رابط المشروع الأساسي (Base URL) ديناميكياً
     */
    public static function baseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = dirname($scriptName);

        $projectFolder = preg_replace('#/public$#', '', $scriptDir);

        if ($projectFolder === '/' || $projectFolder === '\\') {
            $projectFolder = '';
        }

        return rtrim($protocol . '://' . $host . $projectFolder, '/');
    }

    /**
     * توليد رابط كامل لأي مسار داخل المشروع
     * @param string $path المسار النسبي
     */
    public static function url(string $path = ''): string
    {
        $baseUrl = self::baseUrl();
        $path = ltrim($path, '/');
        return $baseUrl . ($path ? '/' . $path : '');
    }

    /**
     * رابط الأصول (assets)
     * @param string $path المسار
     */
    public static function asset(string $path): string
    {
        return self::url('assets/' . ltrim($path, '/'));
    }
}
