<?php
/**
 * CSRF Protection Utility
 * =====================
 * نظام حماية CSRF شامل
 */

class CSRF {
    /**
     * اسم التوكن في الجلسة
     */
    private const TOKEN_NAME = 'csrf_token';
    
    /**
     * اسم التوكن في الـ Header
     */
    private const HEADER_NAME = 'X-CSRF-Token';
    
    /**
     * مدة صلاحية التوكن (ساعة واحدة)
     */
    private const TOKEN_LIFETIME = 3600;

    /**
     * توليد توكن جديد
     */
    public static function generateToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // توليد توكن عشوائي آمن
        $token = bin2hex(random_bytes(32));
        
        // تخزين التوكن مع وقت الإنشاء
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'created_at' => time()
        ];

        return $token;
    }

    /**
     * الحصول على التوكن الحالي أو توليد جديد
     */
    public static function getToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // التحقق من وجود توكن صالح
        if (isset($_SESSION[self::TOKEN_NAME])) {
            $tokenData = $_SESSION[self::TOKEN_NAME];
            
            // التحقق من انتهاء الصلاحية
            if (time() - $tokenData['created_at'] < self::TOKEN_LIFETIME) {
                return $tokenData['token'];
            }
        }

        // توليد توكن جديد
        return self::generateToken();
    }

    /**
     * التحقق من صحة التوكن
     */
    public static function validateToken(?string $token): bool {
        if (empty($token)) {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        $tokenData = $_SESSION[self::TOKEN_NAME];

        // التحقق من انتهاء الصلاحية
        if (time() - $tokenData['created_at'] > self::TOKEN_LIFETIME) {
            unset($_SESSION[self::TOKEN_NAME]);
            return false;
        }

        // مقارنة آمنة للتوكن
        return hash_equals($tokenData['token'], $token);
    }

    /**
     * التحقق من الطلب (POST, PUT, DELETE)
     */
    public static function verifyRequest(): bool {
        // الطلبات GET لا تحتاج تحقق
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }

        // محاولة الحصول على التوكن من مصادر متعددة
        $token = self::getTokenFromRequest();

        return self::validateToken($token);
    }

    /**
     * الحصول على التوكن من الطلب
     */
    public static function getTokenFromRequest(): ?string {
        // من POST
        if (!empty($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // من Header
        $headers = getallheaders();
        if (!empty($headers[self::HEADER_NAME])) {
            return $headers[self::HEADER_NAME];
        }

        // من Header بحروف صغيرة
        foreach ($headers as $key => $value) {
            if (strtolower($key) === strtolower(self::HEADER_NAME)) {
                return $value;
            }
        }

        // من JSON body
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            if (!empty($data['csrf_token'])) {
                return $data['csrf_token'];
            }
        }

        return null;
    }

    /**
     * إنشاء حقل input مخفي للتوكن
     */
    public static function inputField(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * إنشاء meta tag للتوكن (للاستخدام مع AJAX)
     */
    public static function metaTag(): string {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * تحديث التوكن (بعد عمليات حساسة)
     */
    public static function regenerateToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION[self::TOKEN_NAME]);
        return self::generateToken();
    }

    /**
     * Middleware للتحقق التلقائي
     */
    public static function middleware(): void {
        if (!self::verifyRequest()) {
            http_response_code(403);
            
            // التحقق من طلب API
            $isApi = strpos($_SERVER['REQUEST_URI'], '/api/') !== false ||
                     (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            
            if ($isApi) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'رمز الأمان غير صالح أو منتهي الصلاحية',
                    'code' => 'CSRF_VALIDATION_FAILED'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // إعادة التوجيه للصفحة السابقة مع رسالة خطأ
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'انتهت صلاحية الجلسة. يرجى المحاولة مرة أخرى.'
                ];
                
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                header('Location: ' . $referer);
            }
            
            exit;
        }
    }

    /**
     * دالة مساعدة للحصول على معلومات الأمان
     */
    public static function getSecurityInfo(): array {
        return [
            'token_exists' => isset($_SESSION[self::TOKEN_NAME]),
            'token_age' => isset($_SESSION[self::TOKEN_NAME]) 
                ? time() - $_SESSION[self::TOKEN_NAME]['created_at'] 
                : null,
            'token_valid' => isset($_SESSION[self::TOKEN_NAME]) 
                ? (time() - $_SESSION[self::TOKEN_NAME]['created_at'] < self::TOKEN_LIFETIME)
                : false,
            'token_lifetime' => self::TOKEN_LIFETIME
        ];
    }
}
