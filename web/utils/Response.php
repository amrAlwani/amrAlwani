<?php
/**
 * Response Helper - مساعد الاستجابة
 */

class Response {
    public static function success($data = null, string $message = 'تم بنجاح', int $statusCode = 200): void {
        http_response_code($statusCode);
        self::setJsonHeader();
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message = 'حدث خطأ', array $errors = [], int $statusCode = 400): void {
        http_response_code($statusCode);
        self::setJsonHeader();
        echo json_encode(['success' => false, 'message' => $message, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function validationError(array $errors, string $message = 'بيانات غير صالحة'): void {
        self::error($message, $errors, 422);
    }

    public static function unauthorized(string $message = 'غير مصرح'): void {
        self::error($message, [], 401);
    }

    public static function forbidden(string $message = 'غير مصرح لك'): void {
        self::error($message, [], 403);
    }

    public static function notFound(string $message = 'غير موجود'): void {
        self::error($message, [], 404);
    }

    public static function paginate($data, int $total, int $page, int $perPage, string $message = 'تم بنجاح'): void {
        http_response_code(200);
        self::setJsonHeader();
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function setJsonHeader(): void {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
    }
}
