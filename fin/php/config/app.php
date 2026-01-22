<?php
/**
 * إعدادات التطبيق الرئيسية
 */
return [
    // اسم التطبيق
    'name' => 'SwiftCart',
    
    // رابط التطبيق
    'url' => 'http://localhost/swiftcart',
    
    // وضع التصحيح
    'debug' => true,
    
    // المنطقة الزمنية
    'timezone' => 'Asia/Riyadh',
    
    // اللغة الافتراضية
    'locale' => 'ar',
    
    // المسارات
    'paths' => [
        'views' => dirname(__DIR__) . '/app/Views',
        'storage' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/public/uploads',
    ],
    
    // إعدادات الصفحات
    'pagination' => [
        'per_page' => 20,
    ],
    
    // إعدادات الرفع
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_files' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    ],
    
    // إعدادات المتجر
    'store' => [
        'currency' => 'SAR',
        'currency_symbol' => 'ر.س',
        'tax_rate' => 0.15, // 15%
        'shipping_cost' => 25.00,
        'free_shipping_threshold' => 200.00,
        'min_order_value' => 50.00,
    ],
];
