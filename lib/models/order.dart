import 'cart_item.dart';

/// نموذج الطلب
/// تم التصحيح: مطابقة حقل shipping_address مع PHP (JSON)
class Order {
  final int id;
  final String? orderNumber;
  final int userId;
  final List<CartItem> items;
  final double subtotal;
  final double shipping;
  final double tax;
  final double discount;
  final double total;
  final String status;
  final String paymentStatus;
  final ShippingAddress? shippingAddress; // تم التصحيح: كائن بدلاً من حقول منفصلة
  final String? paymentMethod;
  final String? notes;
  final String? couponCode;
  final DateTime createdAt;
  final DateTime? shippedAt;
  final DateTime? deliveredAt;
  final DateTime? updatedAt;

  Order({
    required this.id,
    this.orderNumber,
    required this.userId,
    this.items = const [],
    required this.subtotal,
    this.shipping = 0,
    this.tax = 0,
    this.discount = 0,
    required this.total,
    required this.status,
    this.paymentStatus = 'pending',
    this.shippingAddress,
    this.paymentMethod,
    this.notes,
    this.couponCode,
    required this.createdAt,
    this.shippedAt,
    this.deliveredAt,
    this.updatedAt,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    List<CartItem> orderItems = [];
    if (json['items'] != null && json['items'] is List) {
      orderItems = (json['items'] as List)
          .map((item) => CartItem.fromJson(item))
          .toList();
    }

    // تم التصحيح: قراءة shipping_address ككائن JSON
    ShippingAddress? address;
    if (json['shipping_address'] != null) {
      if (json['shipping_address'] is Map) {
        address = ShippingAddress.fromJson(json['shipping_address']);
      } else if (json['shipping_address'] is String) {
        // محاولة تحويل النص إلى JSON
        try {
          final decoded = json['shipping_address'];
          if (decoded is Map) {
            address = ShippingAddress.fromJson(decoded as Map<String, dynamic>);
          }
        } catch (e) {
          // تجاهل الخطأ
        }
      }
    }

    return Order(
      id: _parseInt(json['id']),
      orderNumber: json['order_number']?.toString(),
      userId: _parseInt(json['user_id']),
      items: orderItems,
      subtotal: _parseDouble(json['subtotal']),
      shipping: _parseDouble(json['shipping']),
      tax: _parseDouble(json['tax']),
      discount: _parseDouble(json['discount']),
      total: _parseDouble(json['total']),
      status: json['status']?.toString() ?? 'pending',
      paymentStatus: json['payment_status']?.toString() ?? 'pending',
      shippingAddress: address,
      paymentMethod: json['payment_method']?.toString(),
      notes: json['notes']?.toString(),
      couponCode: json['coupon_code']?.toString(),
      createdAt: _parseDateTime(json['created_at']),
      shippedAt: json['shipped_at'] != null ? _parseDateTime(json['shipped_at']) : null,
      deliveredAt: json['delivered_at'] != null ? _parseDateTime(json['delivered_at']) : null,
      updatedAt: json['updated_at'] != null ? _parseDateTime(json['updated_at']) : null,
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

  static DateTime _parseDateTime(dynamic value) {
    if (value == null) return DateTime.now();
    if (value is DateTime) return value;
    if (value is String) return DateTime.tryParse(value) ?? DateTime.now();
    return DateTime.now();
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_number': orderNumber,
      'user_id': userId,
      'items': items.map((item) => item.toJson()).toList(),
      'subtotal': subtotal,
      'shipping': shipping,
      'tax': tax,
      'discount': discount,
      'total': total,
      'status': status,
      'payment_status': paymentStatus,
      'shipping_address': shippingAddress?.toJson(),
      'payment_method': paymentMethod,
      'notes': notes,
      'coupon_code': couponCode,
      'created_at': createdAt.toIso8601String(),
      'shipped_at': shippedAt?.toIso8601String(),
      'delivered_at': deliveredAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  Order copyWith({
    int? id,
    String? orderNumber,
    int? userId,
    List<CartItem>? items,
    double? subtotal,
    double? shipping,
    double? tax,
    double? discount,
    double? total,
    String? status,
    String? paymentStatus,
    ShippingAddress? shippingAddress,
    String? paymentMethod,
    String? notes,
    String? couponCode,
    DateTime? createdAt,
    DateTime? shippedAt,
    DateTime? deliveredAt,
    DateTime? updatedAt,
  }) {
    return Order(
      id: id ?? this.id,
      orderNumber: orderNumber ?? this.orderNumber,
      userId: userId ?? this.userId,
      items: items ?? this.items,
      subtotal: subtotal ?? this.subtotal,
      shipping: shipping ?? this.shipping,
      tax: tax ?? this.tax,
      discount: discount ?? this.discount,
      total: total ?? this.total,
      status: status ?? this.status,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      shippingAddress: shippingAddress ?? this.shippingAddress,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      notes: notes ?? this.notes,
      couponCode: couponCode ?? this.couponCode,
      createdAt: createdAt ?? this.createdAt,
      shippedAt: shippedAt ?? this.shippedAt,
      deliveredAt: deliveredAt ?? this.deliveredAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  String get statusText {
    switch (status) {
      case 'pending':
        return 'قيد الانتظار';
      case 'processing':
        return 'قيد المعالجة';
      case 'shipped':
        return 'تم الشحن';
      case 'delivered':
        return 'تم التوصيل';
      case 'cancelled':
        return 'ملغي';
      default:
        return status;
    }
  }

  String get paymentStatusText {
    switch (paymentStatus) {
      case 'pending':
        return 'في انتظار الدفع';
      case 'paid':
        return 'تم الدفع';
      case 'failed':
        return 'فشل الدفع';
      case 'refunded':
        return 'تم الاسترداد';
      default:
        return paymentStatus;
    }
  }
}

/// نموذج عنوان الشحن
class ShippingAddress {
  final String name;
  final String phone;
  final String address;
  final String? city;
  final String? state;
  final String? postalCode;
  final String? country;

  ShippingAddress({
    required this.name,
    required this.phone,
    required this.address,
    this.city,
    this.state,
    this.postalCode,
    this.country,
  });

  factory ShippingAddress.fromJson(Map<String, dynamic> json) {
    return ShippingAddress(
      name: json['name']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      address: json['address']?.toString() ?? '',
      city: json['city']?.toString(),
      state: json['state']?.toString(),
      postalCode: json['postal_code']?.toString(),
      country: json['country']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'phone': phone,
      'address': address,
      'city': city,
      'state': state,
      'postal_code': postalCode,
      'country': country,
    };
  }
}
