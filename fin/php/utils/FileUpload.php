<?php
/**
 * FileUpload Helper - محسّن
 * مساعد رفع الملفات مع تحقق أمني شامل
 */

class FileUpload {
    private array $allowedExtensions;
    private array $allowedMimes;
    private int $maxFileSize;
    private string $uploadDir;
    private array $errors = [];

    // الامتدادات والـ MIME types المسموحة
    private const EXTENSION_MIME_MAP = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    // أسماء ملفات ممنوعة
    private const DANGEROUS_NAMES = [
        '.htaccess', '.htpasswd', 'web.config', '.env', 
        'config.php', 'index.php', 'shell.php'
    ];

    public function __construct(array $options = []) {
        $this->allowedExtensions = $options['extensions'] ?? (defined('ALLOWED_EXTENSIONS') ? ALLOWED_EXTENSIONS : ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $this->maxFileSize = $options['max_size'] ?? (defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 5 * 1024 * 1024);
        $this->uploadDir = $options['upload_dir'] ?? (defined('UPLOAD_DIR') ? UPLOAD_DIR : dirname(__DIR__) . '/uploads/');
        
        // بناء قائمة MIME المسموحة
        $this->allowedMimes = [];
        foreach ($this->allowedExtensions as $ext) {
            if (isset(self::EXTENSION_MIME_MAP[$ext])) {
                $this->allowedMimes = array_merge($this->allowedMimes, self::EXTENSION_MIME_MAP[$ext]);
            }
        }
        $this->allowedMimes = array_unique($this->allowedMimes);
    }

    /**
     * رفع ملف واحد مع تحقق أمني شامل
     */
    public function upload(array $file, string $subDir = ''): ?string {
        $this->errors = [];

        // التحقق من وجود خطأ في الرفع
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return null;
        }

        // التحقق من حجم الملف
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = 'حجم الملف يتجاوز الحد المسموح (' . ($this->maxFileSize / 1024 / 1024) . ' MB)';
            return null;
        }

        // التحقق من حجم الملف الفعلي (منع التلاعب)
        $actualSize = filesize($file['tmp_name']);
        if ($actualSize === false || $actualSize > $this->maxFileSize) {
            $this->errors[] = 'حجم الملف غير صالح';
            return null;
        }

        // التحقق من اسم الملف
        $originalName = basename($file['name']);
        if (!$this->isValidFilename($originalName)) {
            $this->errors[] = 'اسم الملف غير صالح أو يحتوي على رموز ممنوعة';
            return null;
        }

        // التحقق من الأسماء الخطيرة
        if (in_array(strtolower($originalName), self::DANGEROUS_NAMES)) {
            $this->errors[] = 'اسم الملف غير مسموح';
            return null;
        }

        // التحقق من امتداد الملف
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = 'نوع الملف غير مسموح. الأنواع المسموحة: ' . implode(', ', $this->allowedExtensions);
            return null;
        }

        // التحقق من نوع MIME الفعلي
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedMimes)) {
            $this->errors[] = 'محتوى الملف لا يطابق نوعه المعلن';
            return null;
        }

        // للصور: التحقق من أنها صورة فعلية
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            if (!$this->isValidImage($file['tmp_name'])) {
                $this->errors[] = 'الملف ليس صورة صالحة';
                return null;
            }
        }

        // تنظيف مسار المجلد الفرعي
        $subDir = $this->sanitizePath($subDir);

        // إنشاء مجلد الرفع
        $targetDir = $this->uploadDir . ($subDir ? $subDir . '/' : '');
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $this->errors[] = 'فشل إنشاء مجلد الرفع';
                return null;
            }
        }

        // التحقق من أن المجلد قابل للكتابة
        if (!is_writable($targetDir)) {
            $this->errors[] = 'مجلد الرفع غير قابل للكتابة';
            return null;
        }

        // توليد اسم فريد وآمن
        $newFilename = $this->generateSecureFilename($extension);
        $targetPath = $targetDir . $newFilename;

        // التحقق من عدم وجود ملف بنفس الاسم
        if (file_exists($targetPath)) {
            $newFilename = $this->generateSecureFilename($extension);
            $targetPath = $targetDir . $newFilename;
        }

        // نقل الملف
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->errors[] = 'فشل رفع الملف';
            return null;
        }

        // تعيين صلاحيات الملف (للقراءة فقط)
        chmod($targetPath, 0644);

        // إرجاع المسار النسبي
        return ($subDir ? $subDir . '/' : '') . $newFilename;
    }

    /**
     * رفع ملفات متعددة
     */
    public function uploadMultiple(array $files, string $subDir = '', int $maxFiles = 10): array {
        $uploaded = [];

        // التحقق من عدد الملفات
        $fileCount = count($files['name']);
        if ($fileCount > $maxFiles) {
            $this->errors[] = "الحد الأقصى للملفات هو {$maxFiles}";
            return $uploaded;
        }

        foreach ($files['name'] as $index => $name) {
            $file = [
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index]
            ];

            $result = $this->upload($file, $subDir);
            if ($result) {
                $uploaded[] = $result;
            }
        }

        return $uploaded;
    }

    /**
     * حذف ملف بشكل آمن
     */
    public function delete(string $path): bool {
        // تنظيف المسار
        $path = $this->sanitizePath($path);
        
        $fullPath = $this->uploadDir . $path;
        
        // التحقق من أن الملف داخل مجلد الرفع
        $realPath = realpath($fullPath);
        $realUploadDir = realpath($this->uploadDir);
        
        if ($realPath === false || strpos($realPath, $realUploadDir) !== 0) {
            return false;
        }
        
        if (file_exists($realPath) && is_file($realPath)) {
            return unlink($realPath);
        }
        
        return false;
    }

    /**
     * التحقق من صحة اسم الملف
     */
    private function isValidFilename(string $filename): bool {
        // منع Path Traversal
        if (preg_match('/\.\.\/|\.\.\\\\|\.\./', $filename)) {
            return false;
        }
        
        // منع الرموز الخطيرة
        if (preg_match('/[<>:"|?*\x00-\x1f]/', $filename)) {
            return false;
        }
        
        // التحقق من الطول
        if (mb_strlen($filename) > 255 || mb_strlen($filename) < 1) {
            return false;
        }
        
        // منع الامتدادات المزدوجة الخطيرة
        if (preg_match('/\.(php|phtml|php3|php4|php5|phar|exe|sh|bat|cmd|com|msi)$/i', $filename)) {
            return false;
        }
        
        return true;
    }

    /**
     * التحقق من أن الملف صورة فعلية
     */
    private function isValidImage(string $path): bool {
        $imageInfo = @getimagesize($path);
        
        if ($imageInfo === false) {
            return false;
        }
        
        // التحقق من الأبعاد المعقولة
        if ($imageInfo[0] < 1 || $imageInfo[0] > 10000 || $imageInfo[1] < 1 || $imageInfo[1] > 10000) {
            return false;
        }
        
        // التحقق من نوع الصورة
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedTypes)) {
            return false;
        }
        
        return true;
    }

    /**
     * تنظيف المسار
     */
    private function sanitizePath(string $path): string {
        // إزالة المسارات الخطيرة
        $path = str_replace(['../', '..\\', '..'], '', $path);
        
        // إزالة الشرطات المائلة المتكررة
        $path = preg_replace('/[\/\\\\]+/', '/', $path);
        
        // إزالة الشرطة من البداية والنهاية
        return trim($path, '/\\');
    }

    /**
     * توليد اسم ملف آمن وفريد
     */
    private function generateSecureFilename(string $extension): string {
        return date('Ymd_His') . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
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
     * الحصول على الأخطاء
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * الحصول على أول خطأ
     */
    public function getFirstError(): ?string {
        return $this->errors[0] ?? null;
    }

    /**
     * التحقق من وجود أخطاء
     */
    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    /**
     * الحصول على الحد الأقصى لحجم الملف
     */
    public function getMaxFileSize(): int {
        return $this->maxFileSize;
    }

    /**
     * الحصول على الامتدادات المسموحة
     */
    public function getAllowedExtensions(): array {
        return $this->allowedExtensions;
    }
}
