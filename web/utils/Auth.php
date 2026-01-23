<?php
/**
 * JWT Authentication Helper - مصحح
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
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

        return $base64Header . '.' . $base64Payload . '.' . self::base64UrlEncode($signature);
    }

    public static function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        list($base64Header, $base64Payload, $base64Signature) = $parts;
        $secret = defined('JWT_SECRET') ? JWT_SECRET : 'default_secret_key';
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        if (!hash_equals(self::base64UrlEncode($signature), $base64Signature)) return null;

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) return null;

        return $payload;
    }

    public static function requireAuth(): array {
        $token = self::getBearerToken();
        if (!$token) {
            require_once __DIR__ . '/Response.php';
            Response::unauthorized('التوكن مطلوب');
        }

        $payload = self::verifyToken($token);
        if (!$payload) {
            require_once __DIR__ . '/Response.php';
            Response::unauthorized('التوكن غير صالح أو منتهي الصلاحية');
        }

        $userModel = new User();
        $user = $userModel->findById($payload['user_id']);
        if (!$user) {
            require_once __DIR__ . '/Response.php';
            Response::unauthorized('المستخدم غير موجود');
        }

        return $user;
    }

    public static function getBearerToken(): ?string {
        $headers = self::getAuthorizationHeader();
        if ($headers && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function getAuthorizationHeader(): ?string {
        if (isset($_SERVER['Authorization'])) return $_SERVER['Authorization'];
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) return $_SERVER['HTTP_AUTHORIZATION'];
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) return $headers['Authorization'];
        }
        return null;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
