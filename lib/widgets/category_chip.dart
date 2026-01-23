import 'package:flutter/material.dart';
import '../models/category.dart';
import '../constants/app_colors.dart';

class CategoryChip extends StatelessWidget {
  final Category? category;
  final bool isSelected;
  final VoidCallback? onTap;
  final double? width;
  final double height;
  final String? label;

  const CategoryChip({
    Key? key,
    this.category,
    this.isSelected = false,
    this.onTap,
    this.width,
    this.height = 40,
    this.label,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final categoryName = label ?? category?.name ?? '';
    final productsCount = category?.productsCount ?? 0;

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: width,
        height: height,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Theme.of(context).cardColor,
          borderRadius: BorderRadius.circular(height / 2),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.border,
            width: 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              categoryName,
              style: TextStyle(
                fontSize: 14,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                color: isSelected ? Colors.white : AppColors.textPrimary,
              ),
            ),
            if (productsCount > 0) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: isSelected
                      ? Colors.white.withOpacity(0.2)
                      : AppColors.background,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$productsCount',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isSelected ? Colors.white : AppColors.textMuted,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
