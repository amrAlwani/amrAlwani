import 'package:flutter/material.dart';
import '../config/app_config.dart';

/// ألوان التطبيق
class AppColors {
  // Primary Colors
  static const Color primary = Color(AppConfig.primaryColorValue);
  static const Color secondary = Color(AppConfig.secondaryColorValue);
  static const Color error = Color(AppConfig.errorColorValue);
  
  // Neutral Colors
  static const Color white = Colors.white;
  static const Color black = Colors.black;
  static const Color grey = Color(0xFF9E9E9E);
  static const Color lightGrey = Color(0xFFF5F5F5);
  static const Color darkGrey = Color(0xFF424242);
  
  // Status Colors
  static const Color success = Color(0xFF4CAF50);
  static const Color warning = Color(0xFFFFC107);
  static const Color info = Color(0xFF2196F3);
  
  // Text Colors
  static const Color textPrimary = Color(0xFF212121);
  static const Color textSecondary = Color(0xFF757575);
  static const Color textHint = Color(0xFFBDBDBD);
  static const Color textMuted = Color(0xFFBDBDBD); // Same as hint for now

  
  // Background Colors
  static const Color background = Color(0xFFFAFAFA);
  static const Color surface = Colors.white;
  static const Color card = Colors.white;
  
  // Border Color
  static const Color border = Color(0xFFE0E0E0);

  // Gradient
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFF2196F3),
      Color(0xFF1976D2),
    ],
  );
  
  static const LinearGradient secondaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFF03DAC6),
      Color(0xFF00BCD4),
    ],
  );
  
  // Order Status Colors
  static Color getOrderStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return warning;
      case 'processing':
        return info;
      case 'shipped':
        return secondary;
      case 'delivered':
        return success;
      case 'cancelled':
        return error;
      default:
        return grey;
    }
  }
}
