<?php
/**
 * CSRF Protection - حماية من هجمات Cross-Site Request Forgery
 * محسّن للأمان مع دعم كامل للتحقق
 */

class CSRF {
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_LIFETIME = 3600; // ساعة واحدة
    private const TOKEN_LENGTH = 32; // 32 bytes = 64 hex chars

    /**
     * إنشاء توكن جديد
     */
    public static function generateToken(): string {
        self::ensureSession();
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'created_at' => time()
        ];
        
        return $token;
    }

    /**
     * الحصول على التوكن الحالي أو إنشاء جديد
     */
    public static function getToken(): string {
        self::ensureSession();
        
        // التحقق من وجود توكن صالح
        if (isset($_SESSION[self::TOKEN_NAME])) {
            $stored = $_SESSION[self::TOKEN_NAME];
            
            // التحقق من عدم انتهاء الصلاحية
            if (time() - $stored['created_at'] < self::TOKEN_LIFETIME) {
                return $stored['token'];
            }
        }
        
        // إنشاء توكن جديد إذا لم يوجد أو انتهت صلاحيته
        return self::generateToken();
    }

    /**
     * التحقق من صحة التوكن
     */
    public static function verifyToken(string $token): bool {
        self::ensureSession();
        
        if (empty($token) || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        $stored = $_SESSION[self::TOKEN_NAME];
        
        // التحقق من انتهاء الصلاحية
        if (time() - $stored['created_at'] >= self::TOKEN_LIFETIME) {
            self::regenerate();
            return false;
        }
        
        // مقارنة آمنة لمنع timing attacks
        return hash_equals($stored['token'], $token);
    }

    /**
     * التحقق من صحة الطلب الحالي
     */
    public static function verifyRequest(): bool {
        // الحصول على التوكن من POST أو Header
        $token = $_POST[self::TOKEN_NAME] ?? 
                 $_POST['csrf_token'] ?? 
                 $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                 '';
        
        return self::verifyToken($token);
    }

    /**
     * التحقق من الطلب مع الاسم المختصر (للاستخدام في Controllers)
     * @alias لـ verifyToken
     */
    public static function validate(string $token): bool {
        return self::verifyToken($token);
    }

    /**
     * التحقق التلقائي مع رمي استثناء أو إعادة توجيه
     */
    public static function check(): void {
        if (!self::verifyRequest()) {
            // تحديد نوع الرد
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
            
            if ($isAjax || $isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'انتهت صلاحية النموذج، يرجى تحديث الصفحة'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'انتهت صلاحية النموذج، حاول مرة أخرى'
                ];
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
                exit;
            }
        }
    }

    /**
     * إنشاء حقل HTML مخفي للنماذج
     */
    public static function getInputField(): string {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * الحصول على التوكن كـ meta tag للاستخدام مع JavaScript
     */
    public static function getMetaTag(): string {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * تجديد التوكن (بعد تسجيل الدخول مثلاً)
     */
    public static function regenerate(): string {
        self::ensureSession();
        unset($_SESSION[self::TOKEN_NAME]);
        return self::generateToken();
    }

    /**
     * إزالة التوكن (عند تسجيل الخروج)
     */
    public static function destroy(): void {
        self::ensureSession();
        unset($_SESSION[self::TOKEN_NAME]);
    }

    /**
     * التأكد من بدء الجلسة
     */
    private static function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // إعدادات الجلسة الآمنة
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // HTTPS فقط في الإنتاج
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
        }
    }

    /**
     * الحصول على اسم الحقل
     */
    public static function getTokenName(): string {
        return self::TOKEN_NAME;
    }
}
