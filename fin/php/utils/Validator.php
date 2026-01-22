<?php
/**
 * Validator Helper - محسّن مع حماية من تغيير أنواع البيانات
 * مساعد التحقق من المدخلات على مستوى PHP (الخادم)
 * 
 * تم التحسين: 
 * - إضافة تحقق شامل من الرموز الخطيرة
 * - حماية من تغيير نوع البيانات (Type Switching)
 * - تحقق من أن البيانات ليست ملفات مُقنّعة
 */

class Validator {
    private array $data;
    private array $errors = [];
    
    // الرموز الخطيرة الممنوعة في الأسماء والنصوص
    private const DANGEROUS_CHARS = ['<', '>', '"', "'", '\\', '/', ';', '`', '|', '&'];
    private const DANGEROUS_NAME_CHARS = ['(', ')', '*', ',', '!', '@', '#', '$', '%', '^', '&', '*', '+', '=', '[', ']', '{', '}', '|', '\\', '/', ':', ';', '"', "'", '<', '>', ',', '.', '?', '`', '~'];
    
    // كلمات SQL الخطيرة
    private const SQL_KEYWORDS = [
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 
        'UNION', 'EXEC', 'EXECUTE', 'SCRIPT', 'ALTER', 'CREATE',
        'OR 1=1', 'OR 1 = 1', "' OR '", '" OR "', '--', '/*', '*/'
    ];
    
    // أنماط الملفات المُقنّعة
    private const FILE_SIGNATURES = [
        'data:',           // Base64 data URI
        '%PDF',            // PDF
        'PK\x03\x04',      // ZIP/DOCX/XLSX
        '\x89PNG',         // PNG
        '\xFF\xD8\xFF',    // JPEG
        'GIF8',            // GIF
        '<?php',           // PHP code
        '<?=',             // PHP short tag
        '<script',         // JavaScript
        '#!/',             // Shell script
        'MZ',              // Windows executable
    ];
    
    // أنواع البيانات المتوقعة
    public const TYPE_STRING = 'string';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_NUMBER = 'number';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAY = 'array';
    public const TYPE_FILE = 'file';

    public function __construct(?array $data = null) {
        $this->data = $data ?? [];
    }
    
    /**
     * ============================================
     * التحقق من نوع البيانات (Type Validation)
     * يمنع تغيير نوع الحقل من الفرونت إند
     * ============================================
     */
    
    /**
     * التحقق من أن القيمة نص (ليست ملف أو مصفوفة)
     */
    public function isString(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        
        if ($value !== null && $value !== '') {
            // التحقق من أن النوع فعلاً string
            if (!is_string($value)) {
                $this->addError($field, $message ?? "حقل {$field} يجب أن يكون نصاً");
                return $this;
            }
            
            // التحقق من أن النص لا يحتوي على توقيعات ملفات
            if ($this->looksLikeFile($value)) {
                $this->addError($field, $message ?? "حقل {$field} يحتوي على محتوى غير مسموح");
                return $this;
            }
        }
        
        return $this;
    }
    
    /**
     * التحقق من أن القيمة رقم (ليست نص يحتوي على أكواد)
     */
    public function isNumber(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        
        if ($value !== null && $value !== '') {
            if (!is_numeric($value)) {
                $this->addError($field, $message ?? "حقل {$field} يجب أن يكون رقماً");
                return $this;
            }
            
            // التحقق من عدم وجود رموز خطيرة
            if (preg_match('/[<>"\';`|&]/', (string)$value)) {
                $this->addError($field, $message ?? "حقل {$field} يحتوي على رموز غير مسموحة");
            }
        }
        
        return $this;
    }
    
    /**
     * التحقق من نوع البيانات المتوقع
     */
    public function expectType(string $field, string $expectedType, ?string $message = null): self {
        $value = $this->getValue($field);
        
        if ($value === null || $value === '') {
            return $this; // الحقول الفارغة تُعالج بواسطة required()
        }
        
        switch ($expectedType) {
            case self::TYPE_STRING:
                if (!is_string($value) || $this->looksLikeFile($value)) {
                    $this->addError($field, $message ?? "نوع البيانات غير صحيح لـ {$field}");
                }
                break;
                
            case self::TYPE_EMAIL:
                if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $message ?? "البريد الإلكتروني غير صالح");
                } elseif ($this->looksLikeFile($value)) {
                    $this->addError($field, $message ?? "محتوى غير مسموح في البريد الإلكتروني");
                }
                break;
                
            case self::TYPE_PHONE:
                if (!is_string($value) && !is_numeric($value)) {
                    $this->addError($field, $message ?? "رقم الهاتف غير صالح");
                }
                break;
                
            case self::TYPE_NUMBER:
                if (!is_numeric($value)) {
                    $this->addError($field, $message ?? "يجب أن يكون رقماً");
                }
                break;
                
            case self::TYPE_INTEGER:
                if (!is_int($value) && !ctype_digit((string)$value)) {
                    $this->addError($field, $message ?? "يجب أن يكون عدداً صحيحاً");
                }
                break;
                
            case self::TYPE_BOOLEAN:
                if (!is_bool($value) && !in_array($value, ['0', '1', 0, 1, 'true', 'false'], true)) {
                    $this->addError($field, $message ?? "يجب أن يكون قيمة منطقية");
                }
                break;
                
            case self::TYPE_ARRAY:
                if (!is_array($value)) {
                    $this->addError($field, $message ?? "يجب أن يكون مصفوفة");
                }
                break;
                
            case self::TYPE_FILE:
                // الملفات تُعالج بشكل منفصل
                break;
        }
        
        return $this;
    }
    
    /**
     * التحقق من أن القيمة ليست ملف مُقنّع
     */
    public function notFileContent(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        
        if ($value !== null && is_string($value) && $this->looksLikeFile($value)) {
            $this->addError($field, $message ?? "حقل {$field} يحتوي على محتوى ملف غير مسموح");
        }
        
        return $this;
    }
    
    /**
     * التحقق من أن الحقل في $_FILES وليس في $_POST
     */
    public function isUploadedFile(string $field, ?string $message = null): self {
        // إذا وُجد الحقل في POST وهو متوقع كملف، فهذا خطأ
        if (isset($this->data[$field]) && !isset($_FILES[$field])) {
            $this->addError($field, $message ?? "يجب رفع الملف بشكل صحيح");
            return $this;
        }
        
        // التحقق من أن الملف تم رفعه فعلاً عبر HTTP
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
                $this->addError($field, $message ?? "الملف غير صالح");
            }
        }
        
        return $this;
    }
    
    /**
     * التحقق من عدم تغيير نوع الحقل
     * يستخدم مع النماذج التي تحتوي على تعريف أنواع مسبق
     */
    public function validateFieldTypes(array $fieldTypes): self {
        foreach ($fieldTypes as $field => $expectedType) {
            $this->expectType($field, $expectedType);
        }
        return $this;
    }
    
    /**
     * فحص ما إذا كانت القيمة تبدو كملف
     */
    private function looksLikeFile(string $value): bool {
        // فحص البداية
        foreach (self::FILE_SIGNATURES as $signature) {
            if (strpos($value, $signature) === 0) {
                return true;
            }
        }
        
        // فحص Base64 المُقنّع
        if (preg_match('/^[a-zA-Z0-9+\/]{50,}={0,2}$/', $value)) {
            $decoded = base64_decode($value, true);
            if ($decoded !== false) {
                foreach (self::FILE_SIGNATURES as $signature) {
                    if (strpos($decoded, $signature) === 0) {
                        return true;
                    }
                }
            }
        }
        
        // فحص Data URI
        if (preg_match('/^data:[^;]+;base64,/i', $value)) {
            return true;
        }
        
        return false;
    }

    /**
     * التحقق من الحقل المطلوب
     */
    public function required(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, $message ?? "حقل {$field} مطلوب");
        }
        return $this;
    }

    /**
     * التحقق من صيغة البريد الإلكتروني
     */
    public function email(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $value = trim($value);
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError($field, $message ?? 'البريد الإلكتروني غير صالح');
            }
            // التحقق من عدم وجود رموز خطيرة
            if ($this->containsDangerousChars($value)) {
                $this->addError($field, 'البريد الإلكتروني يحتوي على رموز غير مسموحة');
            }
        }
        return $this;
    }

    /**
     * التحقق من الحد الأدنى للطول
     */
    public function min(string $field, int $length, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value) && mb_strlen((string)$value) < $length) {
            $this->addError($field, $message ?? "حقل {$field} يجب أن يكون {$length} أحرف على الأقل");
        }
        return $this;
    }

    /**
     * التحقق من الحد الأقصى للطول
     */
    public function max(string $field, int $length, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value) && mb_strlen((string)$value) > $length) {
            $this->addError($field, $message ?? "حقل {$field} يجب ألا يتجاوز {$length} حرف");
        }
        return $this;
    }

    /**
     * التحقق من اسم المستخدم/الشخص (بدون رموز خاصة)
     */
    public function safeName(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            // التحقق من الرموز الخطيرة في الأسماء
            foreach (self::DANGEROUS_NAME_CHARS as $char) {
                if (mb_strpos($value, $char) !== false) {
                    $this->addError($field, $message ?? "حقل {$field} يحتوي على رموز غير مسموحة مثل: {$char}");
                    return $this;
                }
            }
            
            // التحقق من SQL Injection
            if ($this->containsSqlKeywords($value)) {
                $this->addError($field, 'القيمة تحتوي على محتوى غير مسموح');
            }
            
            // التحقق من أن الاسم يحتوي على أحرف فقط (عربية/إنجليزية/مسافات)
            if (!preg_match('/^[\p{Arabic}a-zA-Z\s\-\.]+$/u', $value)) {
                $this->addError($field, $message ?? "حقل {$field} يجب أن يحتوي على أحرف فقط");
            }
        }
        return $this;
    }

    /**
     * التحقق من نص آمن (للوصف والتعليقات)
     */
    public function safeText(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            // التحقق من الرموز الخطيرة
            if ($this->containsDangerousChars($value)) {
                $this->addError($field, $message ?? "حقل {$field} يحتوي على رموز غير مسموحة");
                return $this;
            }
            
            // التحقق من SQL Injection
            if ($this->containsSqlKeywords($value)) {
                $this->addError($field, 'القيمة تحتوي على محتوى غير مسموح');
            }
            
            // التحقق من JavaScript
            if (preg_match('/<script|javascript:|on\w+\s*=/i', $value)) {
                $this->addError($field, 'القيمة تحتوي على محتوى غير مسموح');
            }
        }
        return $this;
    }

    /**
     * التحقق من القيمة الرقمية
     */
    public function numeric(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, $message ?? "حقل {$field} يجب أن يكون رقماً");
        }
        return $this;
    }

    /**
     * التحقق من العدد الصحيح
     */
    public function integer(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, $message ?? "حقل {$field} يجب أن يكون عدداً صحيحاً");
        }
        return $this;
    }

    /**
     * التحقق من الحد الأدنى للقيمة
     */
    public function minValue(string $field, float $min, ?string $message = null): self {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && is_numeric($value) && (float)$value < $min) {
            $this->addError($field, $message ?? "حقل {$field} يجب أن يكون {$min} على الأقل");
        }
        return $this;
    }

    /**
     * التحقق من الحد الأقصى للقيمة
     */
    public function maxValue(string $field, float $max, ?string $message = null): self {
        $value = $this->getValue($field);
        if ($value !== null && $value !== '' && is_numeric($value) && (float)$value > $max) {
            $this->addError($field, $message ?? "حقل {$field} يجب ألا يتجاوز {$max}");
        }
        return $this;
    }

    /**
     * التحقق من تطابق الحقل مع حقل آخر
     */
    public function matches(string $field, string $matchField, ?string $message = null): self {
        $value = $this->getValue($field);
        $matchValue = $this->getValue($matchField);
        if (!empty($value) && $value !== $matchValue) {
            $this->addError($field, $message ?? "حقل {$field} غير متطابق");
        }
        return $this;
    }

    /**
     * التحقق من القيمة الفريدة في قاعدة البيانات
     */
    public function unique(string $field, string $table, string $column, ?int $exceptId = null, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            try {
                // تنظيف اسم الجدول والعمود (منع SQL Injection)
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                
                $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
                $params = [$value];

                if ($exceptId) {
                    $sql .= " AND id != ?";
                    $params[] = $exceptId;
                }

                $stmt = db()->prepare($sql);
                $stmt->execute($params);

                if ($stmt->fetchColumn() > 0) {
                    $this->addError($field, $message ?? "قيمة {$field} مستخدمة مسبقاً");
                }
            } catch (PDOException $e) {
                // تجاهل خطأ قاعدة البيانات
            }
        }
        return $this;
    }

    /**
     * التحقق من وجود القيمة في قاعدة البيانات
     */
    public function exists(string $field, string $table, string $column, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            try {
                // تنظيف اسم الجدول والعمود
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                
                $stmt = db()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
                $stmt->execute([$value]);

                if ($stmt->fetchColumn() == 0) {
                    $this->addError($field, $message ?? "قيمة {$field} غير موجودة");
                }
            } catch (PDOException $e) {
                // تجاهل خطأ قاعدة البيانات
            }
        }
        return $this;
    }

    /**
     * التحقق من وجود القيمة في مصفوفة
     */
    public function in(string $field, array $values, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value) && !in_array($value, $values)) {
            $this->addError($field, $message ?? "قيمة {$field} غير صالحة");
        }
        return $this;
    }

    /**
     * التحقق من الرابط
     */
    public function url(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $this->addError($field, $message ?? "رابط غير صالح");
                return $this;
            }
            // التحقق من أن الرابط يبدأ بـ http أو https فقط
            if (!preg_match('/^https?:\/\//i', $value)) {
                $this->addError($field, 'الرابط يجب أن يبدأ بـ http:// أو https://');
            }
        }
        return $this;
    }

    /**
     * التحقق من رقم الهاتف
     */
    public function phone(string $field, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            // إزالة كل شيء ما عدا الأرقام و+
            $cleaned = preg_replace('/[^0-9+]/', '', $value);
            
            // التحقق من الطول
            if (strlen($cleaned) < 9 || strlen($cleaned) > 15) {
                $this->addError($field, $message ?? "رقم الهاتف غير صالح");
                return $this;
            }
            
            // قبول الصيغ السعودية واليمنية والدولية
            if (!preg_match('/^(\+?9665|05|5|9677|\+9677|7|00\d{1,3})\d{8,9}$/', $cleaned)) {
                $this->addError($field, $message ?? "رقم الهاتف غير صالح");
            }
        }
        return $this;
    }

    /**
     * التحقق من صيغة التاريخ
     */
    public function date(string $field, string $format = 'Y-m-d', ?string $message = null): self {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->addError($field, $message ?? "تنسيق التاريخ غير صالح");
            }
        }
        return $this;
    }

    /**
     * التحقق من الملف المرفوع
     */
    public function file(string $field, array $options = [], ?string $message = null): self {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            if (!empty($options['required'])) {
                $this->addError($field, $message ?? "الملف مطلوب");
            }
            return $this;
        }
        
        $file = $_FILES[$field];
        
        // التحقق من خطأ الرفع
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, $this->getUploadErrorMessage($file['error']));
            return $this;
        }
        
        // التحقق من الحجم
        $maxSize = $options['max_size'] ?? (5 * 1024 * 1024); // 5MB افتراضي
        if ($file['size'] > $maxSize) {
            $this->addError($field, 'حجم الملف يتجاوز الحد المسموح (' . ($maxSize / 1024 / 1024) . ' MB)');
            return $this;
        }
        
        // التحقق من الامتداد
        $allowedExtensions = $options['extensions'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $this->addError($field, 'نوع الملف غير مسموح. الأنواع المسموحة: ' . implode(', ', $allowedExtensions));
            return $this;
        }
        
        // التحقق من MIME type للصور
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp'
            ];
            if (!in_array($mimeType, $allowedMimes)) {
                $this->addError($field, 'محتوى الملف لا يطابق امتداده');
            }
        }
        
        // التحقق من اسم الملف (منع Path Traversal)
        if (preg_match('/\.\.\/|\.\.\\\\/', $file['name'])) {
            $this->addError($field, 'اسم الملف غير صالح');
        }
        
        return $this;
    }

    /**
     * قاعدة تحقق مخصصة
     */
    public function custom(string $field, callable $callback, ?string $message = null): self {
        $value = $this->getValue($field);
        if (!$callback($value, $this->data)) {
            $this->addError($field, $message ?? "حقل {$field} غير صالح");
        }
        return $this;
    }

    /**
     * التحقق من عدم وجود رموز خطيرة
     */
    private function containsDangerousChars(string $value): bool {
        foreach (self::DANGEROUS_CHARS as $char) {
            if (mb_strpos($value, $char) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * التحقق من عدم وجود كلمات SQL
     */
    private function containsSqlKeywords(string $value): bool {
        $upperValue = strtoupper($value);
        foreach (self::SQL_KEYWORDS as $keyword) {
            if (mb_strpos($upperValue, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * الحصول على رسالة خطأ الرفع
     */
    private function getUploadErrorMessage(int $errorCode): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'حجم الملف يتجاوز الحد المسموح في الخادم',
            UPLOAD_ERR_FORM_SIZE => 'حجم الملف يتجاوز الحد المسموح',
            UPLOAD_ERR_PARTIAL => 'تم رفع الملف جزئياً',
            UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
            UPLOAD_ERR_NO_TMP_DIR => 'مجلد مؤقت غير موجود',
            UPLOAD_ERR_CANT_WRITE => 'فشل كتابة الملف',
            UPLOAD_ERR_EXTENSION => 'إضافة PHP أوقفت الرفع'
        ];
        return $errors[$errorCode] ?? 'خطأ غير معروف في الرفع';
    }

    /**
     * الحصول على قيمة الحقل
     */
    private function getValue(string $field) {
        return $this->data[$field] ?? null;
    }

    /**
     * إضافة خطأ (عام للاستخدام الخارجي)
     */
    public function addError(string $field, string $message): self {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * التحقق من نجاح التحقق
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * التحقق من فشل التحقق
     */
    public function fails(): bool {
        return !$this->passes();
    }

    /**
     * الحصول على جميع الأخطاء
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * الحصول على أول رسالة خطأ
     */
    public function getFirstError(): ?string {
        foreach ($this->errors as $messages) {
            return $messages[0];
        }
        return null;
    }

    /**
     * التحقق وإرسال استجابة خطأ إذا فشل
     */
    public function validate(): void {
        if ($this->fails()) {
            require_once __DIR__ . '/Response.php';
            Response::validationError($this->getFirstError(), $this->errors);
        }
    }

    /**
     * تنظيف النص من الرموز الخطيرة
     */
    public static function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * تنظيف مصفوفة كاملة
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
}
