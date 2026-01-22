<?php
/**
 * API UploadController
 */

namespace Api;

class UploadController extends \Controller
{
    /**
     * رفع ملف
     */
    public function store(): void
    {
        $user = \Auth::requireAuth();
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'حجم الملف أكبر من الحد المسموح',
                UPLOAD_ERR_FORM_SIZE => 'حجم الملف أكبر من الحد المسموح',
                UPLOAD_ERR_PARTIAL => 'لم يتم رفع الملف بالكامل',
                UPLOAD_ERR_NO_FILE => 'لم يتم اختيار ملف',
                UPLOAD_ERR_NO_TMP_DIR => 'خطأ في السيرفر',
                UPLOAD_ERR_CANT_WRITE => 'فشل حفظ الملف',
            ];
            $error = $errors[$_FILES['file']['error'] ?? 0] ?? 'خطأ غير معروف';
            \Response::error($error, [], 400);
        }
        
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        
        // التحقق من الامتداد
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            \Response::error('نوع الملف غير مسموح', [], 400);
        }
        
        // التحقق من الحجم
        if ($fileSize > MAX_FILE_SIZE) {
            \Response::error('حجم الملف أكبر من الحد المسموح', [], 400);
        }
        
        // التحقق من الصورة
        $imageInfo = @getimagesize($fileTmp);
        if (!$imageInfo) {
            \Response::error('الملف ليس صورة صالحة', [], 400);
        }
        
        // إنشاء مجلد الرفع
        $uploadType = $_POST['type'] ?? 'general';
        $uploadDir = UPLOAD_DIR . $uploadType . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // إنشاء اسم فريد
        $newFileName = uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (!move_uploaded_file($fileTmp, $uploadPath)) {
            \Response::error('فشل رفع الملف', [], 500);
        }
        
        $fileUrl = url('uploads/' . $uploadType . '/' . $newFileName);
        
        \Response::success([
            'file_name' => $newFileName,
            'original_name' => $fileName,
            'url' => $fileUrl,
            'size' => $fileSize,
            'type' => $imageInfo['mime']
        ], 'تم رفع الملف بنجاح');
    }
}
