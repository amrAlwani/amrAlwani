<?php

namespace App\Core;

/**
 * فئة Session - إدارة الجلسات بسهولة وأمان
 */
class Session
{
    /**
     * بدء الجلسة إذا لم تكن بدأت
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // إعدادات أمان الجلسة
            $config = Config::get('session', []);
            
            ini_set('session.cookie_httponly', $config['cookie_httponly'] ?? 1);
            ini_set('session.cookie_secure', $config['cookie_secure'] ?? 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', $config['cookie_samesite'] ?? 'Strict');
            
            session_start();
            
            // تجديد معرف الجلسة كل 30 دقيقة
            if (!isset($_SESSION['_last_regenerate'])) {
                $_SESSION['_last_regenerate'] = time();
            } elseif (time() - $_SESSION['_last_regenerate'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_last_regenerate'] = time();
            }
        }
    }

    /**
     * تعيين قيمة في الجلسة
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * الحصول على قيمة من الجلسة
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * التحقق من وجود مفتاح في الجلسة
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * حذف مفتاح من الجلسة
     */
    public static function delete(string $key): void
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * تدمير الجلسة بالكامل (تسجيل الخروج)
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * تجديد معرف الجلسة
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regenerate'] = time();
    }

    /**
     * تعيين رسالة فلاش (تظهر مرة واحدة)
     */
    public static function setFlash(string $key, string $message): void
    {
        self::set('flash_' . $key, $message);
    }

    /**
     * الحصول على رسالة فلاش وحذفها
     */
    public static function getFlash(string $key): ?string
    {
        $flashKey = 'flash_' . $key;
        $message = self::get($flashKey);
        self::delete($flashKey);
        return $message;
    }

    /**
     * التحقق من وجود رسالة فلاش
     */
    public static function hasFlash(string $key): bool
    {
        return self::has('flash_' . $key);
    }

    /**
     * الحصول على جميع بيانات الجلسة
     */
    public static function all(): array
    {
        return $_SESSION;
    }

    /**
     * مسح جميع بيانات الجلسة دون تدميرها
     */
    public static function clear(): void
    {
        $_SESSION = [];
    }
}
