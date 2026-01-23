<?php
/**
 * Validator Helper - محسّن مع حماية شاملة
 * يوفر التحقق من صحة البيانات وتنظيفها من الثغرات الأمنية
 */

class Validator {
    private array $data;
    private array $errors = [];
    private array $rules = [];

    public function __construct(array $data) {
        // تنظيف البيانات المدخلة تلقائياً
        $this->data = self::sanitizeArray($data);
    }

    /**
     * التحقق من الحقل مطلوب
     */
    public function required(string $field, string $message): self {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من صحة البريد الإلكتروني
     */
    public function email(string $field, string $message): self {
        if (!empty($this->data[$field])) {
            $email = filter_var($this->data[$field], FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * التحقق من الحد الأدنى للطول
     */
    public function minLength(string $field, int $min, string $message): self {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من الحد الأقصى للطول
     */
    public function maxLength(string $field, int $max, string $message): self {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من أن القيمة رقم صحيح
     */
    public function integer(string $field, string $message): self {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من أن القيمة رقم
     */
    public function numeric(string $field, string $message): self {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من أن القيمة نص
     */
    public function isString(string $field, string $message): self {
        if (isset($this->data[$field]) && !is_string($this->data[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من الحد الأدنى للقيمة
     */
    public function minValue(string $field, $min, string $message): self {
        if (isset($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من الحد الأقصى للقيمة
     */
    public function maxValue(string $field, $max, string $message): self {
        if (isset($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من تطابق حقلين
     */
    public function match(string $field1, string $field2, string $message): self {
        if (($this->data[$field1] ?? '') !== ($this->data[$field2] ?? '')) {
            $this->errors[$field1] = $message;
        }
        return $this;
    }

    /**
     * التحقق من تأكيد الحقل (مثل password_confirmation)
     */
    public function confirmed(string $field, string $confirmField, string $message): self {
        return $this->match($field, $confirmField, $message);
    }

    /**
     * التحقق من صحة رقم الهاتف السعودي
     */
    public function saudiPhone(string $field, string $message): self {
        if (!empty($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            // أرقام سعودية: 05xxxxxxxx أو 9665xxxxxxxx
            if (!preg_match('/^(05[0-9]{8}|9665[0-9]{8}|5[0-9]{8})$/', $phone)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * التحقق من صحة رقم الهاتف العام
     */
    public function phone(string $field, string $message): self {
        if (!empty($this->data[$field])) {
            $phone = preg_replace('/[^0-9+]/', '', $this->data[$field]);
            if (strlen($phone) < 8 || strlen($phone) > 15) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * التحقق من أن القيمة ضمن قائمة محددة
     */
    public function in(string $field, array $values, string $message): self {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من صحة URL
     */
    public function url(string $field, string $message): self {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من نمط regex
     */
    public function regex(string $field, string $pattern, string $message): self {
        if (!empty($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * التحقق من قوة كلمة المرور
     */
    public function strongPassword(string $field, string $message): self {
        if (!empty($this->data[$field])) {
            $password = $this->data[$field];
            $hasLower = preg_match('/[a-z]/', $password);
            $hasUpper = preg_match('/[A-Z]/', $password);
            $hasNumber = preg_match('/[0-9]/', $password);
            
            if (!$hasLower || !$hasNumber || mb_strlen($password) < 8) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * التحقق من أن كلمة المرور ليست ضمن القائمة السوداء
     */
    public function notBannedPassword(string $field, string $message): self {
        if (!empty($this->data[$field])) {
            $banned = ['password', '123456', 'qwerty', 'password123', 'admin123', 
                       'letmein', 'welcome', 'monkey', '1234567890', 'abc123', 
                       '111111', 'admin', '12345678', 'password1'];
            if (in_array(strtolower($this->data[$field]), $banned)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * هل هناك أخطاء؟
     */
    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    /**
     * هل التحقق ناجح؟
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * هل التحقق فشل؟
     */
    public function fails(): bool {
        return !empty($this->errors);
    }

    /**
     * الحصول على الأخطاء
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * الحصول على أول خطأ
     */
    public function getFirstError(): ?string {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * الحصول على البيانات المنظفة
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * الحصول على قيمة حقل
     */
    public function get(string $field, $default = null) {
        return $this->data[$field] ?? $default;
    }

    /**
     * التحقق مع رمي استثناء API
     */
    public function validate(): void {
        if ($this->hasErrors()) {
            require_once __DIR__ . '/Response.php';
            Response::error('بيانات غير صالحة', $this->errors, 422);
        }
    }

    /**
     * تنظيف قيمة واحدة من XSS
     */
    public static function sanitize($value): string {
        if (!is_string($value)) return '';
        
        // إزالة المسافات الزائدة
        $value = trim($value);
        
        // تحويل الأحرف الخاصة
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // إزالة null bytes
        $value = str_replace(chr(0), '', $value);
        
        return $value;
    }

    /**
     * تنظيف مصفوفة من XSS
     */
    public static function sanitizeArray(array $data): array {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = self::sanitize($value);
            } elseif (is_array($value)) {
                $cleaned[$key] = self::sanitizeArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    /**
     * تنظيف للاستخدام في SQL (إضافي - PDO يتولى الأمر)
     */
    public static function sanitizeForSql(string $value): string {
        // إزالة التعليقات SQL
        $value = preg_replace('/\/\*.*?\*\//s', '', $value);
        $value = preg_replace('/--.*$/m', '', $value);
        
        // إزالة أوامر SQL الخطيرة
        $dangerous = ['DROP', 'DELETE', 'TRUNCATE', 'INSERT', 'UPDATE', 'ALTER', 'CREATE', 'EXEC', 'UNION'];
        foreach ($dangerous as $word) {
            $value = preg_replace('/\b' . $word . '\b/i', '', $value);
        }
        
        return $value;
    }

    /**
     * التحقق من أن النص آمن (لا يحتوي على كود خطير)
     */
    public static function isSafeText(string $value): bool {
        // فحص XSS
        if (preg_match('/<script|javascript:|on\w+\s*=/i', $value)) {
            return false;
        }
        
        // فحص SQL Injection
        if (preg_match('/(\bUNION\b|\bSELECT\b|\bDROP\b|\bDELETE\b)/i', $value)) {
            return false;
        }
        
        return true;
    }

    /**
     * تنظيف اسم الملف
     */
    public static function sanitizeFilename(string $filename): string {
        // إزالة الأحرف الخطيرة
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // منع التنقل في المجلدات
        $filename = str_replace(['..', '/', '\\'], '', $filename);
        
        // تحديد الطول
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }
        
        return $filename;
    }
}
