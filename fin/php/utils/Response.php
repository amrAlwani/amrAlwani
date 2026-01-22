<?php
/**
 * Response Helper
 * مساعد الاستجابة
 * 
 * لم تتم تغييرات كبيرة - الكود سليم
 */

class Response {
    /**
     * إرسال استجابة ناجحة
     */
    public static function success($data = null, string $message = 'تم بنجاح', int $statusCode = 200): void {
        http_response_code($statusCode);
        self::setJsonHeader();
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * إرسال استجابة خطأ
     */
    public static function error(string $message = 'حدث خطأ', array $errors = [], int $statusCode = 400): void {
        http_response_code($statusCode);
        self::setJsonHeader();
        
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * إرسال استجابة مع تصفح
     */
    public static function paginate($data, int $total, int $page, int $perPage, string $message = 'تم بنجاح'): void {
        $lastPage = (int)ceil($total / $perPage);

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
                'last_page' => $lastPage,
                'has_more' => $page < $lastPage
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * إرسال استجابة إنشاء
     */
    public static function created($data = null, string $message = 'تم الإنشاء بنجاح'): void {
        self::success($data, $message, 201);
    }

    /**
     * إرسال استجابة غير موجود
     */
    public static function notFound(string $message = 'غير موجود'): void {
        self::error($message, [], 404);
    }

    /**
     * إرسال استجابة غير مصرح
     */
    public static function unauthorized(string $message = 'غير مصرح'): void {
        self::error($message, [], 401);
    }

    /**
     * إرسال استجابة ممنوع
     */
    public static function forbidden(string $message = 'غير مسموح'): void {
        self::error($message, [], 403);
    }

    /**
     * إرسال استجابة خطأ في التحقق
     */
    public static function validationError(string $message = 'بيانات غير صالحة', array $errors = []): void {
        self::error($message, $errors, 422);
    }

    /**
     * إرسال استجابة خطأ في الخادم
     */
    public static function serverError(string $message = 'خطأ في الخادم'): void {
        self::error($message, [], 500);
    }

    /**
     * إرسال استجابة JSON خام
     */
    public static function json($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        self::setJsonHeader();
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * تعيين هيدر JSON
     */
    private static function setJsonHeader(): void {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
    }
}
