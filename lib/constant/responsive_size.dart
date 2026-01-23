/// ملف الأحجام المتجاوبة
/// تم التصحيح: إصلاح اسم المجلد من "conestant" إلى "constant"
import 'package:flutter/material.dart';

const double designWidth = 375.0;
const double designHeight = 812.0;

extension ResponsiveSize on num {
  /// تحويل العرض للشاشة الحالية
  double w(BuildContext context) {
    final double screenWidth = MediaQuery.sizeOf(context).width;
    return (this / designWidth) * screenWidth;
  }

  /// تحويل الارتفاع للشاشة الحالية
  double h(BuildContext context) {
    final double screenHeight = MediaQuery.sizeOf(context).height;
    return (this / designHeight) * screenHeight;
  }

  /// تحويل حجم الخط للشاشة الحالية
  double sp(BuildContext context) {
    final double screenWidth = MediaQuery.sizeOf(context).width;
    // تم التصحيح: إضافة حد أقصى لحجم الخط لتجنب الخطوط الكبيرة جداً
    final double scaledSize = (this / designWidth) * screenWidth;
    return scaledSize.clamp(this * 0.8, this * 1.5);
  }
}
