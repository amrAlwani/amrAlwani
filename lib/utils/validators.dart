import '../constants/app_constants.dart';

class Validators {
  // Email Validator
  static String? validateEmail(String? value) {
    if (value == null || value.isEmpty) {
      return 'البريد الإلكتروني مطلوب';
    }
    if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
      return 'البريد الإلكتروني غير صحيح';
    }
    return null;
  }
  
  // Password Validator
  static String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'كلمة المرور مطلوبة';
    }
    if (value.length < AppConstants.minPasswordLength) {
      return 'كلمة المرور يجب أن تكون ${AppConstants.minPasswordLength} أحرف على الأقل';
    }
    if (value.length > AppConstants.maxPasswordLength) {
      return 'كلمة المرور طويلة جداً';
    }
    return null;
  }
  
  // Confirm Password Validator
  static String? validateConfirmPassword(String? value, String password) {
    if (value == null || value.isEmpty) {
      return 'تأكيد كلمة المرور مطلوب';
    }
    if (value != password) {
      return 'كلمات المرور غير متطابقة';
    }
    return null;
  }
  
  // Name Validator
  static String? validateName(String? value) {
    if (value == null || value.isEmpty) {
      return 'الاسم مطلوب';
    }
    if (value.length < 2) {
      return 'الاسم قصير جداً';
    }
    if (value.length > 50) {
      return 'الاسم طويل جداً';
    }
    return null;
  }
  
  // Phone Validator
  static String? validatePhone(String? value) {
    if (value == null || value.isEmpty) {
      return 'رقم الهاتف مطلوب';
    }
    // Saudi phone format: 05xxxxxxxx or 5xxxxxxxx
    if (!RegExp(r'^(05|5)[0-9]{8}$').hasMatch(value)) {
      return 'رقم الهاتف غير صحيح';
    }
    return null;
  }
  
  // Required Field Validator
  static String? validateRequired(String? value, String fieldName) {
    if (value == null || value.isEmpty) {
      return '$fieldName مطلوب';
    }
    return null;
  }
  
  // Address Validator
  static String? validateAddress(String? value) {
    if (value == null || value.isEmpty) {
      return 'العنوان مطلوب';
    }
    if (value.length < 10) {
      return 'العنوان قصير جداً';
    }
    return null;
  }
  
  // City Validator
  static String? validateCity(String? value) {
    if (value == null || value.isEmpty) {
      return 'المدينة مطلوبة';
    }
    return null;
  }
  
  // Postal Code Validator
  static String? validatePostalCode(String? value) {
    if (value == null || value.isEmpty) {
      return 'الرمز البريدي مطلوب';
    }
    if (!RegExp(r'^[0-9]{5}$').hasMatch(value)) {
      return 'الرمز البريدي يجب أن يكون 5 أرقام';
    }
    return null;
  }
  
  // Quantity Validator
  static String? validateQuantity(String? value, int maxStock) {
    if (value == null || value.isEmpty) {
      return 'الكمية مطلوبة';
    }
    final quantity = int.tryParse(value);
    if (quantity == null || quantity < 1) {
      return 'الكمية يجب أن تكون رقم صحيح';
    }
    if (quantity > maxStock) {
      return 'الكمية المتاحة: $maxStock';
    }
    return null;
  }
  
  // Coupon Code Validator
  static String? validateCouponCode(String? value) {
    if (value == null || value.isEmpty) {
      return 'كود الخصم مطلوب';
    }
    if (value.length < 3) {
      return 'كود الخصم غير صحيح';
    }
    return null;
  }
  
  // Review Text Validator
  static String? validateReviewText(String? value) {
    if (value == null || value.isEmpty) {
      return 'نص التقييم مطلوب';
    }
    if (value.length < 10) {
      return 'التقييم قصير جداً (10 أحرف على الأقل)';
    }
    if (value.length > 500) {
      return 'التقييم طويل جداً (500 حرف كحد أقصى)';
    }
    return null;
  }
}
