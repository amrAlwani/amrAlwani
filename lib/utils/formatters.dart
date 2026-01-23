import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../config/app_config.dart';

class Formatters {
  // ==================== PRICE FORMATTERS ====================
  
  /// Format price with currency symbol
  static String formatPrice(double price) {
    final formatter = NumberFormat('#,##0.00', 'ar_SA');
    return '${formatter.format(price)} ${AppConfig.currencyCode}';
  }

  /// Format price without currency symbol
  static String formatNumber(double number) {
    final formatter = NumberFormat('#,##0.00', 'ar_SA');
    return formatter.format(number);
  }

  /// Format integer number
  static String formatInt(int number) {
    final formatter = NumberFormat('#,##0', 'ar_SA');
    return formatter.format(number);
  }

  /// Format compact number (1K, 1M, etc.)
  static String formatCompact(int number) {
    final formatter = NumberFormat.compact(locale: 'ar');
    return formatter.format(number);
  }

  // ==================== DATE FORMATTERS ====================

  /// Format date as yyyy/MM/dd
  static String formatDate(DateTime date) {
    return DateFormat('yyyy/MM/dd', 'ar').format(date);
  }

  /// Format date with time
  static String formatDateTime(DateTime date) {
    return DateFormat('yyyy/MM/dd HH:mm', 'ar').format(date);
  }

  /// Format time only
  static String formatTime(DateTime date) {
    return DateFormat('HH:mm', 'ar').format(date);
  }

  /// Format date as day name + date
  static String formatDateFull(DateTime date) {
    return DateFormat('EEEE, d MMMM yyyy', 'ar').format(date);
  }

  /// Format relative date (today, yesterday, etc.)
  static String formatRelativeDate(DateTime date) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final dateOnly = DateTime(date.year, date.month, date.day);
    final difference = today.difference(dateOnly).inDays;

    if (difference == 0) {
      return 'اليوم ${formatTime(date)}';
    } else if (difference == 1) {
      return 'أمس ${formatTime(date)}';
    } else if (difference == 2) {
      return 'قبل يومين';
    } else if (difference < 7) {
      return 'قبل $difference أيام';
    } else if (difference < 30) {
      final weeks = (difference / 7).floor();
      return weeks == 1 ? 'قبل أسبوع' : 'قبل $weeks أسابيع';
    } else if (difference < 365) {
      final months = (difference / 30).floor();
      return months == 1 ? 'قبل شهر' : 'قبل $months أشهر';
    } else {
      return formatDate(date);
    }
  }

  // ==================== PHONE FORMATTERS ====================

  /// Format Saudi phone number
  static String formatPhone(String phone) {
    phone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    
    if (phone.startsWith('966')) {
      phone = phone.substring(3);
    }
    
    if (phone.length == 9 && phone.startsWith('5')) {
      return '0$phone';
    }
    
    if (phone.length == 10 && phone.startsWith('05')) {
      return '${phone.substring(0, 3)} ${phone.substring(3, 6)} ${phone.substring(6)}';
    }
    
    return phone;
  }

  /// Format phone for display with country code
  static String formatPhoneWithCode(String phone) {
    phone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    
    if (phone.startsWith('0')) {
      phone = phone.substring(1);
    }
    
    if (phone.length == 9) {
      return '+966 ${phone.substring(0, 2)} ${phone.substring(2, 5)} ${phone.substring(5)}';
    }
    
    return phone;
  }

  // ==================== TEXT FORMATTERS ====================

  /// Truncate text with ellipsis
  static String truncate(String text, int maxLength) {
    if (text.length <= maxLength) return text;
    return '${text.substring(0, maxLength)}...';
  }

  /// Capitalize first letter
  static String capitalize(String text) {
    if (text.isEmpty) return text;
    return text[0].toUpperCase() + text.substring(1);
  }

  /// Convert to title case
  static String titleCase(String text) {
    return text.split(' ').map((word) => capitalize(word)).join(' ');
  }

  // ==================== INPUT FORMATTERS ====================

  /// Phone number input formatter
  static TextInputFormatter phoneFormatter() {
    return FilteringTextInputFormatter.allow(RegExp(r'[0-9]'));
  }

  /// Price input formatter
  static TextInputFormatter priceFormatter() {
    return FilteringTextInputFormatter.allow(RegExp(r'[0-9.]'));
  }

  /// Letters only formatter
  static TextInputFormatter lettersOnlyFormatter() {
    return FilteringTextInputFormatter.allow(RegExp(r'[a-zA-Zء-ي\s]'));
  }

  /// Alphanumeric formatter
  static TextInputFormatter alphanumericFormatter() {
    return FilteringTextInputFormatter.allow(RegExp(r'[a-zA-Z0-9ء-ي\s]'));
  }

  // ==================== SIZE FORMATTERS ====================

  /// Format file size
  static String formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    if (bytes < 1024 * 1024 * 1024) {
      return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
    }
    return '${(bytes / (1024 * 1024 * 1024)).toStringAsFixed(1)} GB';
  }

  // ==================== ORDER NUMBER ====================

  /// Generate order number
  static String generateOrderNumber() {
    final now = DateTime.now();
    final random = now.millisecondsSinceEpoch.toString().substring(8);
    return 'ORD${now.year}${now.month.toString().padLeft(2, '0')}${now.day.toString().padLeft(2, '0')}$random';
  }

  /// Format order number for display
  static String formatOrderNumber(String orderNumber) {
    if (orderNumber.length > 8) {
      return '${orderNumber.substring(0, 3)}-${orderNumber.substring(3, 11)}-${orderNumber.substring(11)}';
    }
    return orderNumber;
  }
}
