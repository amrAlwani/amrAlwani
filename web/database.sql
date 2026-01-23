-- SwiftCart Database Schema - Full Version
-- قاعدة بيانات متكاملة مع جميع الجداول

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ======== جدول المستخدمين ========
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'vendor', 'admin') DEFAULT 'user',
    `avatar` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `email_verified_at` TIMESTAMP NULL,
    `remember_token` VARCHAR(100) NULL,
    `mfa_enabled` BOOLEAN DEFAULT FALSE,
    `mfa_secret` VARCHAR(32) DEFAULT NULL,
    `mfa_backup_codes` JSON DEFAULT NULL,
    `failed_login_attempts` INT DEFAULT 0,
    `account_locked_until` DATETIME DEFAULT NULL,
    `last_login_at` DATETIME DEFAULT NULL,
    `last_password_change` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phone`),
    INDEX `idx_role` (`role`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول الجلسات الآمنة ========
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `session_token` CHAR(64) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `device_fingerprint` VARCHAR(64),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL,
    `is_revoked` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`session_token`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول محاولات الدخول ========
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `attempt_status` ENUM('success', 'wrong_password', 'account_locked', 'mfa_failed') DEFAULT NULL,
    `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول إعادة تعيين كلمة المرور ========
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `reset_token` CHAR(64) NOT NULL UNIQUE,
    `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME DEFAULT NULL,
    `ip_address` VARCHAR(45),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول سجل الأحداث الأمنية ========
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action_type` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action_type`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول التصنيفات ========
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `icon` VARCHAR(50) NULL,
    `parent_id` INT UNSIGNED NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول المنتجات ========
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `short_description` VARCHAR(500) NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `sale_price` DECIMAL(10,2) NULL,
    `cost_price` DECIMAL(10,2) NULL,
    `sku` VARCHAR(50) NULL UNIQUE,
    `barcode` VARCHAR(50) NULL,
    `stock_quantity` INT DEFAULT 0,
    `low_stock_threshold` INT DEFAULT 5,
    `image` VARCHAR(255) NULL,
    `images` JSON NULL,
    `weight` DECIMAL(8,2) NULL,
    `dimensions` JSON NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `views_count` INT DEFAULT 0,
    `sales_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_sku` (`sku`),
    INDEX `idx_featured` (`is_featured`),
    FULLTEXT INDEX `idx_search` (`name`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول متغيرات المنتج ========
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `sku` VARCHAR(50) NULL,
    `price` DECIMAL(10,2) NULL,
    `stock_quantity` INT DEFAULT 0,
    `attributes` JSON NULL,
    `image` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول العناوين ========
CREATE TABLE IF NOT EXISTS `addresses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NULL,
    `street` VARCHAR(255) NOT NULL,
    `building` VARCHAR(50) NULL,
    `floor` VARCHAR(20) NULL,
    `apartment` VARCHAR(20) NULL,
    `notes` TEXT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول سلة التسوق ========
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_cart_item` (`user_id`, `product_id`, `variant_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول المفضلة ========
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_wishlist` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول الكوبونات ========
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('fixed', 'percentage') DEFAULT 'fixed',
    `value` DECIMAL(10,2) NOT NULL,
    `min_order_amount` DECIMAL(10,2) NULL,
    `max_discount` DECIMAL(10,2) NULL,
    `max_uses` INT NULL,
    `used_count` INT DEFAULT 0,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول الطلبات ========
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    `payment_method` VARCHAR(50) NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `discount` DECIMAL(10,2) DEFAULT 0,
    `shipping_cost` DECIMAL(10,2) DEFAULT 0,
    `tax` DECIMAL(10,2) DEFAULT 0,
    `total` DECIMAL(10,2) NOT NULL,
    `coupon_code` VARCHAR(50) NULL,
    `shipping_address` JSON NOT NULL,
    `notes` TEXT NULL,
    `shipped_at` TIMESTAMP NULL,
    `delivered_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول عناصر الطلب ========
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `options` JSON NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول المراجعات ========
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title` VARCHAR(255) NULL,
    `comment` TEXT NULL,
    `is_approved` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_review` (`user_id`, `product_id`),
    INDEX `idx_product` (`product_id`),
    INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول الإشعارات ========
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT NULL,
    `type` VARCHAR(50) DEFAULT 'general',
    `data` JSON NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== جدول الإعدادات ========
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` VARCHAR(20) DEFAULT 'string',
    `group` VARCHAR(50) DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== البيانات الافتراضية ========

-- Admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('المدير', 'admin@swiftcart.com', '0500000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- الإعدادات الافتراضية
INSERT INTO `settings` (`key`, `value`, `group`) VALUES
('site_name', 'SwiftCart', 'general'),
('site_description', 'متجرك الإلكتروني الموثوق', 'general'),
('currency', 'SAR', 'general'),
('currency_symbol', 'ر.س', 'general'),
('tax_rate', '15', 'general'),
('shipping_cost', '25', 'shipping'),
('free_shipping_threshold', '500', 'shipping'),
('min_order_value', '50', 'orders')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- تصنيفات افتراضية
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
('إلكترونيات', 'electronics', 'أجهزة إلكترونية ومستلزماتها', '📱', 1),
('ملابس', 'clothing', 'ملابس رجالية ونسائية', '👔', 2),
('أحذية', 'shoes', 'أحذية متنوعة', '👟', 3),
('إكسسوارات', 'accessories', 'إكسسوارات وحقائب', '👜', 4),
('المنزل', 'home', 'مستلزمات المنزل', '🏠', 5)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

SET FOREIGN_KEY_CHECKS = 1;
