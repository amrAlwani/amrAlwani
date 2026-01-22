<?php
/**
 * JWT Authentication Helper
 * مساعد المصادقة JWT
 * 
 * تم التصحيح: تحسين معالجة الأخطاء
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
    /**
     * توليد رمز JWT
     */
    public static function generateToken(int $userId, array $additionalData = []): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode(array_merge([
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + (defined('JWT_EXPIRY') ? JWT_EXPIRY : 604800)
        ], $additionalData));

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        $secret = defined('JWT_SECRET') ? JWT_SECRET : 'default_secret_key';
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * التحقق من رمز JWT
     */
    public static function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $secret = defined('JWT_SECRET') ? JWT_SECRET : 'default_secret_key';
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $base64Signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * الحصول على المستخدم الحالي من الطلب
     */
    public static function getUser(): ?array {
        $token = self::getBearerToken();
        if (!$token) {
            return null;
        }

        $payload = self::verifyToken($token);
        if (!$payload || empty($payload['user_id'])) {
            return null;
        }

        $userModel = new User();
        return $userModel->findById($payload['user_id']);
    }

    /**
     * طلب المصادقة (يوقف التنفيذ إذا لم يكن المستخدم مسجلاً)
     */
    public static function requireAuth(): array {
        $user = self::getUser();
        if (!$user) {
            require_once __DIR__ . '/Response.php';
            Response::unauthorized('يرجى تسجيل الدخول');
            exit; // للتأكد
        }
        return $user;
    }

    /**
     * طلب صلاحية المدير
     */
    public static function requireAdmin(): array {
        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            require_once __DIR__ . '/Response.php';
            Response::forbidden('غير مصرح لك بالوصول');
            exit;
        }
        return $user;
    }

    /**
     * الحصول على رمز Bearer من الهيدر
     */
    private static function getBearerToken(): ?string {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * الحصول على هيدر Authorization
     */
    private static function getAuthorizationHeader(): ?string {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // تحويل المفاتيح للحروف الكبيرة في البداية
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        // للخوادم التي لا تمرر Authorization header
        if (empty($headers) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }
        
        return $headers;
    }

    /**
     * Base64 URL Encode
     */
    private static function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64 URL Decode
     */
    private static function base64UrlDecode(string $data): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
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
     * توليد رمز عشوائي (لإعادة تعيين كلمة المرور)
     */
    public static function generateRandomToken(int $length = 64): string {
        return bin2hex(random_bytes($length / 2));
    }
}
