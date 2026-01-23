import 'product.dart';

/// نموذج عنصر السلة
/// تم التصحيح: مطابقة الحقول مع PHP API (جدول cart_items)
class CartItem {
  final int id;
  final int productId;
  final Product? product;
  final int quantity;
  final double price;
  final int? variantId; // تم الإضافة: لدعم المتغيرات
  final DateTime createdAt;

  CartItem({
    required this.id,
    required this.productId,
    this.product,
    required this.quantity,
    required this.price,
    this.variantId,
    required this.createdAt,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      id: _parseInt(json['id']),
      productId: _parseInt(json['product_id']),
      product: json['product'] != null
          ? Product.fromJson(json['product'])
          : null,
      quantity: _parseInt(json['quantity'], 1),
      price: _parseDouble(json['price']),
      variantId: json['variant_id'] != null ? _parseInt(json['variant_id']) : null,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  static int _parseInt(dynamic value, [int defaultValue = 0]) {
    if (value == null) return defaultValue;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? defaultValue;
    return defaultValue;
  }

  static double _parseDouble(dynamic value, [double defaultValue = 0.0]) {
    if (value == null) return defaultValue;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? defaultValue;
    return defaultValue;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'product': product?.toJson(),
      'quantity': quantity,
      'price': price,
      'variant_id': variantId,
      'created_at': createdAt.toIso8601String(),
    };
  }

  double get total => price * quantity;

  CartItem copyWith({
    int? id,
    int? productId,
    Product? product,
    int? quantity,
    double? price,
    int? variantId,
    DateTime? createdAt,
  }) {
    return CartItem(
      id: id ?? this.id,
      productId: productId ?? this.productId,
      product: product ?? this.product,
      quantity: quantity ?? this.quantity,
      price: price ?? this.price,
      variantId: variantId ?? this.variantId,
      createdAt: createdAt ?? this.createdAt,
    );
  }
}
