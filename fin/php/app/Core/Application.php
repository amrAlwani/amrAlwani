<?php

namespace App\Core;

/**
 * فئة Application - نقطة الدخول الرئيسية للتطبيق
 * مسؤولة عن تهيئة وإدارة دورة حياة التطبيق بالكامل
 */
class Application
{
    /**
     * @var Kernel نواة التطبيق المسؤولة عن معالجة الطلبات
     */
    protected Kernel $kernel;

    /**
     * مُنشئ الفئة - يُهيئ التطبيق عند إنشاء كائن Application
     */
    public function __construct()
    {
        // 1. تهيئة المكونات الأساسية للتطبيق
        $this->bootstrap();

        // 2. إنشاء كائن Kernel لمعالجة الطلبات
        $this->kernel = new Kernel();
    }

    /**
     * تهيئة وإعداد المكونات الأساسية للتطبيق
     * هذه الدالة تُنفّذ قبل أي معالجة للطلبات
     */
    protected function bootstrap(): void
    {
        // 1. تحميل الإعدادات من ملفات config/
        Config::load();

        // 2. تسجيل معالج الأخطاء والاستثناءات
        ErrorHandler::register();

        // 3. بدء جلسة PHP (Session)
        Session::init();
    }

    /**
     * تشغيل التطبيق - نقطة الدخول الرئيسية
     * تُستدعى هذه الدالة من index.php لبدء معالجة الطلب
     */
    public function run(): void
    {
        // تفويض معالجة الطلب إلى Kernel
        $this->kernel->handle();
    }
}
