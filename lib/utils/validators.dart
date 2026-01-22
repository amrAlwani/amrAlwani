import 'dart:io';
import 'dart:typed_data';

/// دوال التحقق من صحة المدخلات - الإصدار 4.0
/// تم التحسين: إضافة تحقق شامل لأنواع البيانات + منع تغيير الأنواع + أمان كامل
class Validators {
  // ==================== أنماط الأحرف الخطيرة ====================
  
  /// أحرف خطيرة يجب منعها
  static final RegExp _dangerousChars = RegExp(r'[<>"\x27;\\`]');
  
  /// كلمات SQL خطيرة
  static final RegExp _sqlKeywords = RegExp(
    r'\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|SCRIPT|ALTER|CREATE|TRUNCATE|GRANT|REVOKE)\b',
    caseSensitive: false,
  );
  
  /// رموز خاصة غير مسموحة في الأسماء
  static final RegExp _invalidNameChars = RegExp(r'[(){}[\]*!@#$%^&=+|~`<>?/\\;:,]');
  
  /// أنماط XSS الخطيرة
  static final RegExp _xssPatterns = RegExp(
    r'(javascript:|data:|vbscript:|on\w+\s*=|<script|<\/script|<iframe|<object|<embed)',
    caseSensitive: false,
  );

  // ==================== التحقق من أنواع البيانات ====================

  /// تصنيفات أنواع الحقول المدعومة
  static const List<String> supportedFieldTypes = [
    'email',
    'password', 
    'name',
    'phone',
    'text',
    'number',
    'url',
    'file',
    'image',
    'date',
    'address',
  ];

  /// التحقق من أن القيمة تطابق النوع المتوقع
  static ValidationResult validateType(dynamic value, String expectedType) {
    // التحقق من null
    if (value == null) {
      return ValidationResult(
        isValid: false,
        error: 'القيمة مطلوبة',
        expectedType: expectedType,
        actualType: 'null',
      );
    }

    switch (expectedType.toLowerCase()) {
      case 'email':
        return _validateEmailType(value);
      case 'password':
        return _validatePasswordType(value);
      case 'name':
        return _validateNameType(value);
      case 'phone':
        return _validatePhoneType(value);
      case 'text':
        return _validateTextType(value);
      case 'number':
        return _validateNumberType(value);
      case 'url':
        return _validateUrlType(value);
      case 'file':
        return _validateFileType(value);
      case 'image':
        return _validateImageType(value);
      case 'date':
        return _validateDateType(value);
      case 'address':
        return _validateAddressType(value);
      default:
        return ValidationResult(
          isValid: false,
          error: 'نوع الحقل غير معروف: $expectedType',
          expectedType: expectedType,
          actualType: value.runtimeType.toString(),
        );
    }
  }

  /// التحقق من نوع البريد الإلكتروني
  static ValidationResult _validateEmailType(dynamic value) {
    // يجب أن يكون String
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'البريد الإلكتروني يجب أن يكون نصاً',
        expectedType: 'String (email)',
        actualType: value.runtimeType.toString(),
      );
    }
    
    // لا يمكن أن يكون ملف أو بيانات ثنائية
    if (_looksLikeFileData(value)) {
      return ValidationResult(
        isValid: false,
        error: 'البريد الإلكتروني لا يمكن أن يكون ملفاً',
        expectedType: 'String (email)',
        actualType: 'File data',
      );
    }

    final emailError = validateEmail(value);
    return ValidationResult(
      isValid: emailError == null,
      error: emailError,
      expectedType: 'String (email)',
      actualType: 'String',
    );
  }

  /// التحقق من نوع كلمة المرور
  static ValidationResult _validatePasswordType(dynamic value) {
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'كلمة المرور يجب أن تكون نصاً',
        expectedType: 'String (password)',
        actualType: value.runtimeType.toString(),
      );
    }

    final passwordError = validatePassword(value);
    return ValidationResult(
      isValid: passwordError == null,
      error: passwordError,
      expectedType: 'String (password)',
      actualType: 'String',
    );
  }

  /// التحقق من نوع الاسم
  static ValidationResult _validateNameType(dynamic value) {
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'الاسم يجب أن يكون نصاً',
        expectedType: 'String (name)',
        actualType: value.runtimeType.toString(),
      );
    }

    if (_looksLikeFileData(value)) {
      return ValidationResult(
        isValid: false,
        error: 'الاسم لا يمكن أن يكون ملفاً',
        expectedType: 'String (name)',
        actualType: 'File data',
      );
    }

    final nameError = validateName(value);
    return ValidationResult(
      isValid: nameError == null,
      error: nameError,
      expectedType: 'String (name)',
      actualType: 'String',
    );
  }

  /// التحقق من نوع الهاتف
  static ValidationResult _validatePhoneType(dynamic value) {
    if (value is! String && value is! int) {
      return ValidationResult(
        isValid: false,
        error: 'رقم الهاتف يجب أن يكون نصاً أو رقماً',
        expectedType: 'String|int (phone)',
        actualType: value.runtimeType.toString(),
      );
    }

    final phoneError = validatePhone(value.toString());
    return ValidationResult(
      isValid: phoneError == null,
      error: phoneError,
      expectedType: 'String|int (phone)',
      actualType: value.runtimeType.toString(),
    );
  }

  /// التحقق من نوع النص العادي
  static ValidationResult _validateTextType(dynamic value) {
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'القيمة يجب أن تكون نصاً',
        expectedType: 'String (text)',
        actualType: value.runtimeType.toString(),
      );
    }

    // التحقق من أنه ليس ملف
    if (_looksLikeFileData(value)) {
      return ValidationResult(
        isValid: false,
        error: 'النص لا يمكن أن يكون ملفاً',
        expectedType: 'String (text)',
        actualType: 'File data',
      );
    }

    final textError = validateSafeText(value);
    return ValidationResult(
      isValid: textError == null,
      error: textError,
      expectedType: 'String (text)',
      actualType: 'String',
    );
  }

  /// التحقق من نوع الرقم
  static ValidationResult _validateNumberType(dynamic value) {
    if (value is! num && value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'القيمة يجب أن تكون رقماً',
        expectedType: 'num (number)',
        actualType: value.runtimeType.toString(),
      );
    }

    // إذا كان نصاً، تحقق من أنه قابل للتحويل إلى رقم
    if (value is String) {
      if (double.tryParse(value) == null) {
        return ValidationResult(
          isValid: false,
          error: 'القيمة يجب أن تكون رقماً صالحاً',
          expectedType: 'num (number)',
          actualType: 'String (non-numeric)',
        );
      }
    }

    return ValidationResult(
      isValid: true,
      error: null,
      expectedType: 'num (number)',
      actualType: value.runtimeType.toString(),
    );
  }

  /// التحقق من نوع URL
  static ValidationResult _validateUrlType(dynamic value) {
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'الرابط يجب أن يكون نصاً',
        expectedType: 'String (url)',
        actualType: value.runtimeType.toString(),
      );
    }

    final urlError = validateUrl(value, required: true);
    return ValidationResult(
      isValid: urlError == null,
      error: urlError,
      expectedType: 'String (url)',
      actualType: 'String',
    );
  }

  /// التحقق من نوع الملف
  static ValidationResult _validateFileType(dynamic value) {
    // يقبل File أو Uint8List أو String (مسار الملف)
    if (value is File) {
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'File',
        actualType: 'File',
      );
    }
    
    if (value is Uint8List) {
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'File',
        actualType: 'Uint8List',
      );
    }

    if (value is String) {
      // تحقق من أنه مسار ملف صالح
      if (value.isEmpty) {
        return ValidationResult(
          isValid: false,
          error: 'مسار الملف فارغ',
          expectedType: 'File',
          actualType: 'String (empty)',
        );
      }
      // تحقق من المسار الخطير
      if (_hasPathTraversal(value)) {
        return ValidationResult(
          isValid: false,
          error: 'مسار الملف غير آمن',
          expectedType: 'File',
          actualType: 'String (unsafe path)',
        );
      }
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'File',
        actualType: 'String (path)',
      );
    }

    return ValidationResult(
      isValid: false,
      error: 'نوع الملف غير مدعوم',
      expectedType: 'File|Uint8List|String',
      actualType: value.runtimeType.toString(),
    );
  }

  /// التحقق من نوع الصورة
  static ValidationResult _validateImageType(dynamic value) {
    final fileResult = _validateFileType(value);
    if (!fileResult.isValid) {
      return fileResult;
    }

    // تحقق إضافي لامتداد الصورة إذا كان مسار
    if (value is String) {
      final validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp'];
      final lowerPath = value.toLowerCase();
      final hasValidExtension = validExtensions.any((ext) => lowerPath.endsWith(ext));
      
      if (!hasValidExtension && !lowerPath.startsWith('data:image/')) {
        return ValidationResult(
          isValid: false,
          error: 'امتداد الصورة غير مدعوم',
          expectedType: 'Image (jpg, png, gif, webp)',
          actualType: 'String (invalid extension)',
        );
      }
    }

    return ValidationResult(
      isValid: true,
      error: null,
      expectedType: 'Image',
      actualType: value.runtimeType.toString(),
    );
  }

  /// التحقق من نوع التاريخ
  static ValidationResult _validateDateType(dynamic value) {
    if (value is DateTime) {
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'DateTime',
        actualType: 'DateTime',
      );
    }

    if (value is String) {
      final parsed = DateTime.tryParse(value);
      if (parsed == null) {
        return ValidationResult(
          isValid: false,
          error: 'صيغة التاريخ غير صالحة',
          expectedType: 'DateTime|String (ISO 8601)',
          actualType: 'String (invalid date)',
        );
      }
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'DateTime|String',
        actualType: 'String (valid date)',
      );
    }

    if (value is int) {
      // Unix timestamp
      return ValidationResult(
        isValid: true,
        error: null,
        expectedType: 'DateTime|int (timestamp)',
        actualType: 'int (timestamp)',
      );
    }

    return ValidationResult(
      isValid: false,
      error: 'نوع التاريخ غير مدعوم',
      expectedType: 'DateTime|String|int',
      actualType: value.runtimeType.toString(),
    );
  }

  /// التحقق من نوع العنوان
  static ValidationResult _validateAddressType(dynamic value) {
    if (value is! String) {
      return ValidationResult(
        isValid: false,
        error: 'العنوان يجب أن يكون نصاً',
        expectedType: 'String (address)',
        actualType: value.runtimeType.toString(),
      );
    }

    final addressError = validateAddress(value);
    return ValidationResult(
      isValid: addressError == null,
      error: addressError,
      expectedType: 'String (address)',
      actualType: 'String',
    );
  }

  // ==================== مساعدات اكتشاف التغيير ====================

  /// التحقق من أن النص يبدو كبيانات ملف
  static bool _looksLikeFileData(String value) {
    // تحقق من base64 للصور
    if (value.startsWith('data:') || value.startsWith('base64,')) {
      return true;
    }
    // تحقق من binary data patterns
    if (value.contains('\x00') || value.contains('\xff\xd8')) {
      return true;
    }
    // تحقق من PDF header
    if (value.startsWith('%PDF')) {
      return true;
    }
    return false;
  }

  /// التحقق من Path Traversal
  static bool _hasPathTraversal(String path) {
    return path.contains('..') || 
           path.contains('~') || 
           path.startsWith('/') ||
           path.contains('\\');
  }

  // ==================== التحقق من الملفات ====================

  /// أنواع MIME المسموحة للصور
  static const List<String> allowedImageMimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/bmp',
  ];

  /// أنواع MIME المسموحة للملفات
  static const List<String> allowedFileMimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
  ];

  /// الحد الأقصى لحجم الملف (5 ميجابايت)
  static const int maxFileSize = 5 * 1024 * 1024;

  /// الحد الأقصى لحجم الصورة (2 ميجابايت)
  static const int maxImageSize = 2 * 1024 * 1024;

  /// التحقق من صحة الملف المرفوع
  static FileValidationResult validateUploadedFile({
    required String fileName,
    required int fileSize,
    required String? mimeType,
    List<String>? allowedMimes,
    int? maxSize,
  }) {
    final errors = <String>[];

    // التحقق من اسم الملف
    if (fileName.isEmpty) {
      errors.add('اسم الملف مطلوب');
    } else {
      // التحقق من الأحرف الخطيرة في اسم الملف
      if (_hasPathTraversal(fileName)) {
        errors.add('اسم الملف يحتوي على أحرف غير مسموحة');
      }
      if (_dangerousChars.hasMatch(fileName)) {
        errors.add('اسم الملف يحتوي على رموز خطيرة');
      }
      // التحقق من الامتدادات الخطيرة
      final dangerousExtensions = ['.exe', '.bat', '.cmd', '.sh', '.php', '.js', '.py'];
      final lowerName = fileName.toLowerCase();
      for (final ext in dangerousExtensions) {
        if (lowerName.endsWith(ext)) {
          errors.add('امتداد الملف غير مسموح');
          break;
        }
      }
    }

    // التحقق من حجم الملف
    final effectiveMaxSize = maxSize ?? maxFileSize;
    if (fileSize > effectiveMaxSize) {
      final maxMB = (effectiveMaxSize / (1024 * 1024)).toStringAsFixed(1);
      errors.add('حجم الملف يتجاوز الحد المسموح ($maxMB MB)');
    }
    if (fileSize == 0) {
      errors.add('الملف فارغ');
    }

    // التحقق من نوع MIME
    final effectiveAllowedMimes = allowedMimes ?? allowedFileMimes;
    if (mimeType == null || mimeType.isEmpty) {
      errors.add('نوع الملف غير محدد');
    } else if (!effectiveAllowedMimes.contains(mimeType.toLowerCase())) {
      errors.add('نوع الملف غير مدعوم');
    }

    return FileValidationResult(
      isValid: errors.isEmpty,
      errors: errors,
      fileName: fileName,
      fileSize: fileSize,
      mimeType: mimeType,
    );
  }

  /// التحقق من صحة الصورة المرفوعة
  static FileValidationResult validateUploadedImage({
    required String fileName,
    required int fileSize,
    required String? mimeType,
  }) {
    return validateUploadedFile(
      fileName: fileName,
      fileSize: fileSize,
      mimeType: mimeType,
      allowedMimes: allowedImageMimes,
      maxSize: maxImageSize,
    );
  }

  /// التحقق من bytes الصورة الفعلية (magic bytes)
  static bool isValidImageBytes(Uint8List bytes) {
    if (bytes.length < 4) return false;

    // JPEG: FF D8 FF
    if (bytes[0] == 0xFF && bytes[1] == 0xD8 && bytes[2] == 0xFF) {
      return true;
    }
    // PNG: 89 50 4E 47
    if (bytes[0] == 0x89 && bytes[1] == 0x50 && bytes[2] == 0x4E && bytes[3] == 0x47) {
      return true;
    }
    // GIF: 47 49 46
    if (bytes[0] == 0x47 && bytes[1] == 0x49 && bytes[2] == 0x46) {
      return true;
    }
    // WEBP: 52 49 46 46 ... 57 45 42 50
    if (bytes[0] == 0x52 && bytes[1] == 0x49 && bytes[2] == 0x46 && bytes[3] == 0x46 &&
        bytes.length > 11 && bytes[8] == 0x57 && bytes[9] == 0x45 && bytes[10] == 0x42 && bytes[11] == 0x50) {
      return true;
    }
    // BMP: 42 4D
    if (bytes[0] == 0x42 && bytes[1] == 0x4D) {
      return true;
    }

    return false;
  }

  // ==================== تنظيف النصوص ====================

  /// تنظيف النص من الأحرف الخطيرة
  static String sanitize(String value) {
    return value
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#x27;')
        .replaceAll('`', '&#x60;')
        .trim();
  }

  /// تنظيف النص للعرض الآمن
  static String sanitizeForDisplay(String value) {
    return value
        .replaceAll(RegExp(r'<[^>]*>'), '') // إزالة HTML tags
        .replaceAll(RegExp(r'javascript:', caseSensitive: false), '')
        .replaceAll(RegExp(r'on\w+=', caseSensitive: false), '')
        .trim();
  }

  /// التحقق من وجود أحرف خطيرة
  static bool _hasDangerousContent(String value) {
    return _dangerousChars.hasMatch(value) || 
           _sqlKeywords.hasMatch(value) ||
           _xssPatterns.hasMatch(value);
  }

  // ==================== التحقق من الحقول الأساسية ====================

  static String? validateEmail(String? value) {
    if (value == null || value.isEmpty) {
      return 'البريد الإلكتروني مطلوب';
    }

    final trimmed = value.trim();
    
    // التحقق من الطول
    if (trimmed.length > 255) {
      return 'البريد الإلكتروني طويل جداً';
    }

    // التحقق من أنه ليس ملف
    if (_looksLikeFileData(trimmed)) {
      return 'البريد الإلكتروني غير صالح';
    }

    final emailRegex = RegExp(
      r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
    );

    if (!emailRegex.hasMatch(trimmed)) {
      return 'البريد الإلكتروني غير صالح';
    }

    return null;
  }

  static String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'كلمة المرور مطلوبة';
    }

    if (value.length < 6) {
      return 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }

    if (value.length > 128) {
      return 'كلمة المرور طويلة جداً';
    }

    return null;
  }

  /// التحقق من قوة كلمة المرور
  static String? validateStrongPassword(String? value) {
    final basicError = validatePassword(value);
    if (basicError != null) return basicError;

    if (!RegExp(r'[A-Z]').hasMatch(value!)) {
      return 'يجب أن تحتوي على حرف كبير واحد على الأقل';
    }

    if (!RegExp(r'[a-z]').hasMatch(value)) {
      return 'يجب أن تحتوي على حرف صغير واحد على الأقل';
    }

    if (!RegExp(r'[0-9]').hasMatch(value)) {
      return 'يجب أن تحتوي على رقم واحد على الأقل';
    }

    return null;
  }

  static String? validateConfirmPassword(String? value, String password) {
    if (value == null || value.isEmpty) {
      return 'تأكيد كلمة المرور مطلوب';
    }

    if (value != password) {
      return 'كلمتا المرور غير متطابقتين';
    }

    return null;
  }

  static String? validateName(String? value) {
    if (value == null || value.isEmpty) {
      return 'الاسم مطلوب';
    }

    final trimmed = value.trim();

    if (trimmed.length < 2) {
      return 'الاسم يجب أن يكون حرفين على الأقل';
    }

    if (trimmed.length > 100) {
      return 'الاسم طويل جداً';
    }

    // التحقق من الرموز غير المسموحة
    if (_invalidNameChars.hasMatch(trimmed)) {
      return 'الاسم يحتوي على رموز غير مسموحة';
    }

    // التحقق من الأحرف الخطيرة
    if (_hasDangerousContent(trimmed)) {
      return 'الاسم يحتوي على أحرف غير مسموحة';
    }

    return null;
  }

  static String? validatePhone(String? value) {
    if (value == null || value.isEmpty) {
      return 'رقم الهاتف مطلوب';
    }

    final cleaned = value.replaceAll(RegExp(r'[\s\-()]'), '');
    final phoneRegex = RegExp(r'^[0-9+]{9,15}$');

    if (!phoneRegex.hasMatch(cleaned)) {
      return 'رقم الهاتف غير صالح';
    }

    return null;
  }

  static String? validatePhoneOptional(String? value) {
    if (value == null || value.isEmpty) {
      return null;
    }
    return validatePhone(value);
  }

  /// التحقق من النص الآمن
  static String? validateSafeText(String? value, {int? minLength, int? maxLength}) {
    if (value == null || value.isEmpty) {
      return 'هذا الحقل مطلوب';
    }

    final trimmed = value.trim();

    if (minLength != null && trimmed.length < minLength) {
      return 'يجب أن يكون $minLength أحرف على الأقل';
    }

    if (maxLength != null && trimmed.length > maxLength) {
      return 'يجب ألا يتجاوز $maxLength حرف';
    }

    if (_hasDangerousContent(trimmed)) {
      return 'يحتوي على أحرف غير مسموحة';
    }

    return null;
  }

  static String? validateRequired(String? value, String fieldName) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName مطلوب';
    }
    return null;
  }

  static String? validateMinLength(String? value, int minLength, String fieldName) {
    if (value == null || value.isEmpty) {
      return '$fieldName مطلوب';
    }

    if (value.trim().length < minLength) {
      return '$fieldName يجب أن يكون $minLength أحرف على الأقل';
    }

    return null;
  }

  static String? validateMaxLength(String? value, int maxLength, String fieldName) {
    if (value != null && value.length > maxLength) {
      return '$fieldName يجب ألا يتجاوز $maxLength حرف';
    }
    return null;
  }

  static String? validateNumber(String? value, String fieldName) {
    if (value == null || value.isEmpty) {
      return '$fieldName مطلوب';
    }

    if (double.tryParse(value) == null) {
      return '$fieldName يجب أن يكون رقماً';
    }

    return null;
  }

  static String? validatePositiveNumber(String? value, String fieldName) {
    final numberError = validateNumber(value, fieldName);
    if (numberError != null) return numberError;

    if (double.parse(value!) <= 0) {
      return '$fieldName يجب أن يكون أكبر من صفر';
    }

    return null;
  }

  static String? validateAddress(String? value) {
    if (value == null || value.isEmpty) {
      return 'العنوان مطلوب';
    }

    final trimmed = value.trim();

    if (trimmed.length < 10) {
      return 'يرجى إدخال عنوان تفصيلي أكثر';
    }

    if (trimmed.length > 500) {
      return 'العنوان طويل جداً';
    }

    if (_hasDangerousContent(trimmed)) {
      return 'العنوان يحتوي على أحرف غير مسموحة';
    }

    return null;
  }

  static String? validateUrl(String? value, {bool required = false}) {
    if (value == null || value.isEmpty) {
      return required ? 'الرابط مطلوب' : null;
    }

    final urlRegex = RegExp(
      r'^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$',
    );

    if (!urlRegex.hasMatch(value)) {
      return 'الرابط غير صالح';
    }

    // تحقق من XSS في URL
    if (_xssPatterns.hasMatch(value.toLowerCase())) {
      return 'الرابط يحتوي على محتوى غير آمن';
    }

    return null;
  }

  /// التحقق من الكمية
  static String? validateQuantity(String? value) {
    if (value == null || value.isEmpty) {
      return 'الكمية مطلوبة';
    }

    final qty = int.tryParse(value);
    if (qty == null) {
      return 'الكمية يجب أن تكون رقماً صحيحاً';
    }

    if (qty < 1) {
      return 'الكمية يجب أن تكون 1 على الأقل';
    }

    if (qty > 999) {
      return 'الكمية كبيرة جداً';
    }

    return null;
  }

  /// التحقق من السعر
  static String? validatePrice(String? value) {
    if (value == null || value.isEmpty) {
      return 'السعر مطلوب';
    }

    final price = double.tryParse(value);
    if (price == null) {
      return 'السعر يجب أن يكون رقماً';
    }

    if (price < 0) {
      return 'السعر لا يمكن أن يكون سالباً';
    }

    if (price > 999999.99) {
      return 'السعر كبير جداً';
    }

    return null;
  }
}

// ==================== نتائج التحقق ====================

/// نتيجة التحقق من النوع
class ValidationResult {
  final bool isValid;
  final String? error;
  final String expectedType;
  final String actualType;

  ValidationResult({
    required this.isValid,
    this.error,
    required this.expectedType,
    required this.actualType,
  });

  @override
  String toString() {
    if (isValid) {
      return 'Valid: $actualType matches $expectedType';
    }
    return 'Invalid: Expected $expectedType but got $actualType. Error: $error';
  }
}

/// نتيجة التحقق من الملف
class FileValidationResult {
  final bool isValid;
  final List<String> errors;
  final String fileName;
  final int fileSize;
  final String? mimeType;

  FileValidationResult({
    required this.isValid,
    required this.errors,
    required this.fileName,
    required this.fileSize,
    this.mimeType,
  });

  String get errorMessage => errors.join(', ');

  @override
  String toString() {
    if (isValid) {
      return 'Valid file: $fileName ($fileSize bytes, $mimeType)';
    }
    return 'Invalid file: $errorMessage';
  }
}
