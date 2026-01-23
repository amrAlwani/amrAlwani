<?php
/**
 * SwiftCart - Database Configuration
 * إعدادات قاعدة البيانات المصححة
 *
 * تم التصحيح: 
 * - تحسين معالجة الأخطاء
 * - إضافة إعادة المحاولة
 * - Singleton pattern
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;
    private int $maxRetries = 3;

    private function __construct() {
        $this->connect();
    }

    /**
     * الاتصال بقاعدة البيانات
     */
    private function connect(): void {
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                    PDO::ATTR_PERSISTENT => false,
                ];

                $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
                return; // نجاح الاتصال

            } catch (PDOException $e) {
                $retries++;

                if ($retries >= $this->maxRetries) {
                    // سجل الخطأ بدلاً من إظهاره للمستخدم في الإنتاج
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        http_response_code(500);
                        die(json_encode([
                            'success' => false,
                            'message' => 'خطأ في الاتصال بقاعدة البيانات',
                            'error' => $e->getMessage()
                        ], JSON_UNESCAPED_UNICODE));
                    } else {
                        error_log("Database connection failed: " . $e->getMessage());
                        http_response_code(500);
                        die(json_encode([
                            'success' => false,
                            'message' => 'خطأ في الخادم. يرجى المحاولة لاحقاً.'
                        ], JSON_UNESCAPED_UNICODE));
                    }
                }

                // انتظر قبل إعادة المحاولة
                usleep(100000 * $retries); // 100ms * retry count
            }
        }
    }

    /**
     * الحصول على نسخة واحدة من الاتصال (Singleton)
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * الحصول على كائن PDO
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * منع النسخ
     */
    private function __clone() {}

    /**
     * منع إعادة الإنشاء
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * إغلاق الاتصال
     */
    public function close(): void {
        $this->conn = null;
    }
}

/**
 * دالة مساعدة للحصول على اتصال قاعدة البيانات
 */
function db(): PDO {
    return Database::getInstance()->getConnection();
}
