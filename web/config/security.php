<?php
/**
 * إعدادات الأمان المتقدمة
 */
return [
    // تكلفة bcrypt (كلما زادت، زاد الأمان والبطء)
    'bcrypt_cost' => 12,

    // مدة صلاحية التوكن (بالثواني)
    'token_expiry' => 3600, // ساعة واحدة

    // مدة صلاحية الجلسة (بالثواني)
    'session_timeout' => 86400, // 24 ساعة

    // الحد الأقصى لمحاولات تسجيل الدخول
    'max_login_attempts' => 5,

    // مدة قفل الحساب (بالثواني)
    'lockout_time' => 900, // 15 دقيقة

    // الحد الأدنى لطول كلمة المرور
    'min_password_length' => 8,

    // إجبار MFA للمدراء
    'require_mfa_admin' => true,

    // إعدادات JWT
    'jwt' => [
        'secret' => 'your_jwt_secret_key_change_in_production_!@#$%',
        'algorithm' => 'HS256',
        'expiry' => 86400, // 24 ساعة
    ],

    // إعدادات CSRF
    'csrf' => [
        'enabled' => true,
        'token_length' => 32,
        'token_expiry' => 3600,
    ],

    // إعدادات Rate Limiting
    'rate_limit' => [
        'enabled' => true,
        'max_requests' => 100,
        'window' => 60, // دقيقة واحدة
    ],

    // كلمات المرور المحظورة
    'banned_passwords' => [
        'password', '123456', 'qwerty', 'password123',
        'admin123', 'letmein', 'welcome', 'monkey',
        '1234567890', 'abc123', '111111', 'admin'
    ],

    // سياسات كلمات المرور حسب الدور
    'password_policies' => [
        'user' => [
            'min_length' => 8,
            'require_uppercase' => false,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => false,
        ],
        'vendor' => [
            'min_length' => 10,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => false,
        ],
        'admin' => [
            'min_length' => 12,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => true,
        ],
    ],
];
