# 📝 ملاحظات التصحيح - SwiftCart PHP Backend

## 🔴 الأخطاء الرئيسية المصححة

### 1. عدم تطابق أسماء الأعمدة مع Schema
| الموقع | الخطأ | التصحيح |
|--------|-------|---------|
| Cart.php | `stock` | `stock_quantity` |
| Cart.php | `discount_price` | `sale_price` |
| Cart.php | جدول `cart` | جدول `cart_items` |
| Cart.php | `usage_limit` | `max_uses` |
| Cart.php | `usage_count` | `used_count` |
| Cart.php | `min_order_value` | `min_order_amount` |
| Product.php | `stock` | `stock_quantity` |
| Product.php | `discount_price` | `sale_price` |
| Product.php | `views` | `views_count` |
| Product.php | `vendor_id` | غير موجود - تم إزالته |
| Order.php | أعمدة shipping منفصلة | `shipping_address` JSON |
| Order.php | `product_name` | `name` |
| User.php | `role = 'customer'` | `role = 'user'` |

### 2. مشكلة LIMIT/OFFSET مع PDO
```php
// ❌ خطأ - PDO لا يقبل متغيرات في LIMIT/OFFSET بهذه الطريقة
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);

// ✅ صحيح - استخدام bindParam مع PDO::PARAM_INT
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
```

### 3. حقل phone مطلوب في Schema
```php
// ❌ Schema يتطلب phone NOT NULL
$data['phone'] // قد يكون فارغاً

// ✅ إضافة قيمة افتراضية
$values[] = $data['phone'] ?? '0000000000';
```

### 4. ملفات مفقودة
- ❌ `models/Category.php` - غير موجود
- ❌ `api/categories.php` - غير موجود
- ❌ جدول `notifications` - غير موجود في SQL

✅ تم إنشاء جميع الملفات المفقودة

---

## 📁 هيكل الملفات المصححة

```
php-corrected/
├── config/
│   ├── config.php          # إعدادات + ثوابت مفقودة
│   └── database.php         # تحسين معالجة الأخطاء
├── models/
│   ├── Cart.php             # تصحيح أسماء الأعمدة
│   ├── Product.php          # تصحيح LIMIT/OFFSET
│   ├── User.php             # تصحيح phone + role
│   ├── Order.php            # تصحيح shipping_address
│   └── Category.php         # ملف جديد
├── api/
│   ├── auth.php             # تحسين معالجة الأخطاء
│   ├── cart.php             # تحسين التحقق
│   ├── products.php         # تحسين التحقق
│   ├── orders.php           # تحسين التحقق
│   └── categories.php       # ملف جديد
├── utils/
│   ├── Auth.php             # تحسين التوافق
│   ├── Response.php         # بدون تغييرات كبيرة
│   ├── Validator.php        # تحسين التعامل مع null
│   └── FileUpload.php       # ملف جديد
└── mvc_project.sql          # إضافة جدول notifications
```

---

## ⚠️ ملاحظات مهمة

### 1. الأمان
```php
// يجب تغيير هذه القيم في الإنتاج:
define('JWT_SECRET', 'your_secure_secret_here');
define('DEBUG_MODE', false); // تعطيل في الإنتاج
```

### 2. ملفات لم تُعدل (سليمة)
- `app/Core/Application.php`
- `app/Core/Router.php`
- `app/Core/Kernel.php`
- `app/Core/Config.php`
- `app/Core/Controller.php`
- `app/Core/Path.php`
- `app/Core/ErrorHandler.php`

### 3. للتشغيل
1. استبدل الملفات القديمة بالمصححة
2. شغّل `mvc_project.sql` على قاعدة البيانات
3. تأكد من صلاحيات مجلد `uploads`
4. اختبر الـ APIs

---

## 🧪 اختبار سريع

```bash
# تسجيل مستخدم جديد
curl -X POST http://localhost/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"name":"اختبار","email":"test@test.com","password":"123456","phone":"0500000000"}'

# تسجيل الدخول
curl -X POST http://localhost/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456"}'

# جلب المنتجات
curl http://localhost/api/products.php?action=list

# جلب التصنيفات
curl http://localhost/api/categories.php?action=list
```
