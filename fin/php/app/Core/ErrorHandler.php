<?php

namespace App\Core;

/**
 * فئة ErrorHandler - معالجة الأخطاء والاستثناءات
 */
class ErrorHandler
{
    /**
     * تسجيل معالج الأخطاء
     */
    public static function register(): void
    {
        $debug = Config::get('app.debug', false);
        
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * معالجة الاستثناءات
     */
    public static function handleException(\Throwable $e): void
    {
        $debug = Config::get('app.debug', false);
        
        // تسجيل الخطأ
        self::logError($e);

        http_response_code(500);

        // التحقق من نوع الطلب
        if (self::isJsonRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            
            $response = [
                'success' => false,
                'message' => $debug ? $e->getMessage() : 'حدث خطأ داخلي في الخادم'
            ];
            
            if ($debug) {
                $response['error'] = [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($debug) {
            echo "<style>
                body { font-family: Arial, sans-serif; padding: 20px; direction: rtl; }
                .error-container { background: #fee; border: 1px solid #f00; padding: 20px; border-radius: 5px; }
                .error-title { color: #c00; margin-bottom: 10px; }
                .error-message { background: #fff; padding: 10px; margin: 10px 0; }
                pre { background: #333; color: #0f0; padding: 15px; overflow-x: auto; direction: ltr; text-align: left; }
            </style>";
            echo "<div class='error-container'>";
            echo "<h2 class='error-title'>خطأ: " . get_class($e) . "</h2>";
            echo "<div class='error-message'><strong>الرسالة:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='error-message'><strong>الملف:</strong> " . $e->getFile() . " (السطر: " . $e->getLine() . ")</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            echo "</div>";
        } else {
            $errorView = Path::views('errors/500.php');
            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo "<h1>خطأ 500</h1><p>حدث خطأ داخلي في الخادم</p>";
            }
        }
        
        exit;
    }

    /**
     * معالجة الأخطاء التقليدية
     */
    public static function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * معالجة أخطاء الإيقاف المفاجئ
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * تسجيل الخطأ في ملف
     */
    private static function logError(\Throwable $e): void
    {
        $logPath = Path::storage('logs');
        
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }

        $logFile = $logPath . '/error-' . date('Y-m-d') . '.log';
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        error_log($logMessage, 3, $logFile);
    }

    /**
     * التحقق من كون الطلب JSON
     */
    private static function isJsonRequest(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return strpos($contentType, 'application/json') !== false
            || strpos($accept, 'application/json') !== false
            || strpos($uri, '/api/') !== false;
    }
}
