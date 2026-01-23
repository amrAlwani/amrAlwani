<?php
/**
 * Security Helper - أدوات الحماية المتقدمة
 * يوفر وظائف أمنية شاملة للتطبيق
 */

class Security {
    
    /**
     * التحقق من Rate Limiting
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 60, int $window = 60): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $storageKey = 'rate_limit_' . $key;
        $now = time();
        
        if (!isset($_SESSION[$storageKey])) {
            $_SESSION[$storageKey] = [
                'count' => 0,
                'reset_at' => $now + $window
            ];
        }
        
        // إعادة تعيين إذا انتهت النافذة الزمنية
        if ($_SESSION[$storageKey]['reset_at'] <= $now) {
            $_SESSION[$storageKey] = [
                'count' => 0,
                'reset_at' => $now + $window
            ];
        }
        
        $_SESSION[$storageKey]['count']++;
        
        return $_SESSION[$storageKey]['count'] <= $maxAttempts;
    }

    /**
     * تسجيل محاولة تسجيل دخول فاشلة
     */
    public static function recordFailedLogin(string $identifier): void {
        $key = 'failed_login_' . md5($identifier);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    /**
     * التحقق من قفل الحساب
     */
    public static function isAccountLocked(string $identifier, int $maxAttempts = 5, int $lockoutTime = 900): bool {
        $key = 'failed_login_' . md5($identifier);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        $data = $_SESSION[$key];
        
        // إعادة تعيين إذا مر وقت القفل
        if (isset($data['last_attempt']) && (time() - $data['last_attempt']) > $lockoutTime) {
            unset($_SESSION[$key]);
            return false;
        }
        
        return $data['count'] >= $maxAttempts;
    }

    /**
     * إعادة تعيين محاولات الدخول الفاشلة
     */
    public static function resetFailedLogins(string $identifier): void {
        $key = 'failed_login_' . md5($identifier);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[$key]);
    }

    /**
     * تشفير كلمة المرور
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * التحقق من كلمة المرور
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * إنشاء توكن عشوائي آمن
     */
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }

    /**
     * إنشاء رمز تحقق رقمي (OTP)
     */
    public static function generateOTP(int $length = 6): string {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    /**
     * تشفير البيانات
     */
    public static function encrypt(string $data, string $key = null): string {
        $key = $key ?? (defined('JWT_SECRET') ? JWT_SECRET : 'default_key');
        $key = hash('sha256', $key, true);
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * فك تشفير البيانات
     */
    public static function decrypt(string $data, string $key = null): ?string {
        $key = $key ?? (defined('JWT_SECRET') ? JWT_SECRET : 'default_key');
        $key = hash('sha256', $key, true);
        
        $data = base64_decode($data);
        if ($data === false || strlen($data) < 17) {
            return null;
        }
        
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * الحصول على IP الحقيقي للزائر
     */
    public static function getClientIP(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // إذا كان هناك عدة IPs، خذ الأول
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // التحقق من صحة IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }

    /**
     * الحصول على User Agent
     */
    public static function getUserAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * التحقق من أن الطلب HTTPS
     */
    public static function isSecure(): bool {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        
        return false;
    }

    /**
     * تنظيف وتأمين الملفات المرفوعة
     */
    public static function validateUploadedFile(array $file, array $options = []): array {
        $defaults = [
            'max_size' => 5 * 1024 * 1024, // 5MB
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
        ];
        
        $options = array_merge($defaults, $options);
        
        // التحقق من وجود خطأ
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'خطأ في رفع الملف'];
        }
        
        // التحقق من الحجم
        if ($file['size'] > $options['max_size']) {
            return ['valid' => false, 'error' => 'حجم الملف كبير جداً'];
        }
        
        // التحقق من نوع الملف
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $options['allowed_types'])) {
            return ['valid' => false, 'error' => 'نوع الملف غير مسموح'];
        }
        
        // التحقق من الامتداد
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $options['allowed_extensions'])) {
            return ['valid' => false, 'error' => 'امتداد الملف غير مسموح'];
        }
        
        // التحقق من أنه ملف حقيقي (للصور)
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['valid' => false, 'error' => 'الملف ليس صورة صالحة'];
            }
        }
        
        return [
            'valid' => true,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file['size']
        ];
    }

    /**
     * إنشاء اسم آمن للملف
     */
    public static function generateSafeFilename(string $originalName): string {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(16));
        return $safeName . '.' . $extension;
    }

    /**
     * إضافة headers الأمان
     */
    public static function setSecurityHeaders(): void {
        // منع clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // منع XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // منع MIME sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");
    }

    /**
     * تسجيل حدث أمني
     */
    public static function logSecurityEvent(string $event, array $data = []): void {
        $logFile = BASEPATH . '/logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIP(),
            'user_agent' => self::getUserAgent(),
            'data' => $data
        ];
        
        file_put_contents(
            $logFile, 
            json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL, 
            FILE_APPEND | LOCK_EX
        );
    }
}
