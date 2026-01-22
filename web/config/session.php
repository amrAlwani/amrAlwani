<?php
/**
 * إعدادات الجلسة الآمنة
 */
return [
    // مدة صلاحية الكوكي (بالثواني)
    'cookie_lifetime' => 86400, // 24 ساعة

    // تفعيل HTTPS للكوكيز (يجب تفعيله في الإنتاج)
    'cookie_secure' => false,

    // حماية الكوكيز من JavaScript
    'cookie_httponly' => true,

    // سياسة SameSite
    'cookie_samesite' => 'Strict',

    // اسم الجلسة
    'name' => 'SWIFTCART_SESSION',

    // مسار الكوكي
    'cookie_path' => '/',

    // فترة تجديد معرف الجلسة (بالثواني)
    'regenerate_interval' => 1800, // 30 دقيقة
];
