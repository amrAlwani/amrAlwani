# هيكل المشروع الكامل - SwiftCart

## المشروع يحتوي على:

### 1. PHP Backend (`php-corrected/`)
```
php-corrected/
├── api/                    # نقاط API
│   ├── auth.php
│   ├── cart.php
│   ├── categories.php
│   ├── orders.php
│   └── products.php
├── app/Core/               # نواة MVC
│   ├── Application.php
│   ├── Auth.php
│   ├── Autoloader.php
│   ├── Config.php
│   ├── Controller.php
│   ├── ErrorHandler.php
│   ├── Kernel.php
│   ├── Path.php
│   ├── Router.php
│   └── Session.php
├── config/                 # إعدادات
│   ├── app.php
│   ├── database.php
│   ├── security.php
│   └── session.php
├── includes/               # ملفات مشتركة
│   ├── header.php
│   └── footer.php
├── models/                 # النماذج
├── public/                 # نقطة الدخول
├── routes/                 # المسارات
├── utils/                  # أدوات مساعدة
├── login.php              # صفحة تسجيل الدخول
├── register.php           # صفحة التسجيل
├── dashboard.php          # لوحة التحكم
├── products.php           # صفحة المنتجات
├── logout.php             # تسجيل الخروج
└── mvc_project.sql        # قاعدة البيانات
```

### 2. Flutter App (`flutter-corrected/`)
```
flutter-corrected/lib/
├── config/
│   └── app_config.dart
├── constant/
│   └── responsive_size.dart
├── models/
│   ├── cart_item.dart
│   ├── category.dart
│   ├── order.dart
│   ├── product.dart
│   └── user.dart
├── providers/
│   ├── auth_provider.dart
│   ├── cart_provider.dart
│   ├── favorites_provider.dart
│   ├── products_provider.dart
│   └── theme_provider.dart
├── screens/
│   ├── cart_screen.dart
│   ├── home_screen.dart
│   ├── introduction_screen.dart
│   ├── login_screen.dart
│   ├── products_screen.dart
│   ├── register_screen.dart
│   └── splash_screen.dart
├── services/
│   ├── api_service.dart
│   └── database_helper.dart
├── utils/
│   ├── formatters.dart
│   └── validators.dart
├── widgets/
│   ├── category_chip.dart
│   └── product_card.dart
└── main.dart
```

## التصحيحات المطبقة:
1. ✅ مطابقة أسماء الحقول بين Flutter و PHP
2. ✅ إضافة نظام MVC كامل
3. ✅ إضافة نظام أمان متقدم (Rate Limiting, MFA)
4. ✅ واجهة PHP كاملة (login, register, dashboard, products)
5. ✅ جميع شاشات Flutter الأصلية
