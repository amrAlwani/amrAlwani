import 'dart:convert';

/// دوال مساعدة لتحويل القيم بأمان
double _toDouble(dynamic value, [double defaultValue = 0.0]) {
  if (value == null) return defaultValue;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? defaultValue;
  return defaultValue;
}

int _toInt(dynamic value, [int defaultValue = 0]) {
  if (value == null) return defaultValue;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) return int.tryParse(value) ?? defaultValue;
  return defaultValue;
}

/// نموذج المنتج
/// تم التصحيح: مطابقة الحقول مع PHP API
class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final double? salePrice; // تم التصحيح: من discount_price إلى sale_price
  final int stockQuantity; // تم التصحيح: من stock إلى stock_quantity
  final int categoryId;
  final String? categoryName;
  final String? image;
  final List<String> images;
  final double rating;
  final int reviewCount;
  final int viewsCount; // تم التصحيح: من views إلى views_count
  final bool isActive;
  final bool isFeatured;
  final DateTime createdAt;

  Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.salePrice,
    required this.stockQuantity,
    required this.categoryId,
    this.categoryName,
    this.image,
    this.images = const [],
    this.rating = 0.0,
    this.reviewCount = 0,
    this.viewsCount = 0,
    this.isActive = true,
    this.isFeatured = false,
    required this.createdAt,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    List<String> parsedImages = [];
    if (json['images'] != null) {
      if (json['images'] is List) {
        parsedImages = List<String>.from(
          json['images'].map((item) => item.toString()),
        );
      } else if (json['images'] is String && json['images'].isNotEmpty) {
        try {
          final decoded = jsonDecode(json['images']);
          if (decoded is List) {
            parsedImages = List<String>.from(
              decoded.map((item) => item.toString()),
            );
          }
        } catch (e) {
          parsedImages = [json['images']];
        }
      }
    }

    return Product(
      id: _toInt(json['id']),
      name: json['name']?.toString() ?? '',
      description: json['description']?.toString(),
      price: _toDouble(json['price']),
      // تم التصحيح: قراءة sale_price أو discount_price
      salePrice: json['sale_price'] != null
          ? _toDouble(json['sale_price'])
          : (json['discount_price'] != null ? _toDouble(json['discount_price']) : null),
      // تم التصحيح: قراءة stock أو stock_quantity (للتوافق مع كلا الاسمين)
      stockQuantity: _toInt(json['stock_quantity'] ?? json['stock']),
      categoryId: _toInt(json['category_id']),
      categoryName: json['category_name']?.toString(),
      image: json['image']?.toString(),
      images: parsedImages,
      rating: _toDouble(json['rating']),
      reviewCount: _toInt(json['review_count']),
      viewsCount: _toInt(json['views_count']),
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      isFeatured: json['is_featured'] == 1 || json['is_featured'] == true,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'description': description,
    'price': price,
    'sale_price': salePrice,
    'stock_quantity': stockQuantity,
    'category_id': categoryId,
    'category_name': categoryName,
    'image': image,
    'images': images,
    'rating': rating,
    'review_count': reviewCount,
    'views_count': viewsCount,
    'is_active': isActive,
    'is_featured': isFeatured,
    'created_at': createdAt.toIso8601String(),
  };

  // دوال مساعدة
  double get finalPrice => salePrice ?? price;
  bool get hasDiscount => salePrice != null && salePrice! < price;
  double get discountPercentage {
    if (!hasDiscount) return 0.0;
    return ((price - salePrice!) / price) * 100;
  }

  bool get inStock => stockQuantity > 0;

  // للتوافق مع الكود القديم
  int get stock => stockQuantity;
  double? get discountPrice => salePrice;
}
