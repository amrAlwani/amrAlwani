import 'package:flutter/foundation.dart';
import '../models/cart_item.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../services/database_helper.dart';

/// مزود السلة
class CartProvider with ChangeNotifier {
  List<CartItem> _items = [];
  bool _isLoading = false;
  String? _error;
  double _discount = 0;
  String? _couponCode;

  List<CartItem> get items => _items;
  bool get isLoading => _isLoading;
  String? get error => _error;
  double get discount => _discount;
  String? get couponCode => _couponCode;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0, (sum, item) => sum + item.total);

  double get shipping => subtotal > 200 ? 0 : 25; // شحن مجاني فوق 200

  double get tax => subtotal * 0.15;

  double get total => subtotal + shipping + tax - _discount;

  final ApiService _apiService = ApiService();
  final DatabaseHelper _dbHelper = DatabaseHelper.instance;

  bool _isAuthenticated = false;

  void setAuthState(bool isAuthenticated) {
    _isAuthenticated = isAuthenticated;
    loadCart();
  }

  /// تحميل السلة
  /// GET /api/cart
  Future<void> loadCart() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      if (_isAuthenticated) {
        final response = await _apiService.get('/api/cart');
        if (response['success'] == true) {
          final data = response['data'];
          if (data != null && data['items'] is List) {
            _items = (data['items'] as List)
                .map((item) => CartItem.fromJson(item))
                .toList();
            _discount = (data['discount'] ?? 0).toDouble();
            _couponCode = data['coupon_code'];
          } else {
            _items = [];
          }
        }
      } else {
        final cartData = await _dbHelper.getCartItems();
        _items = cartData.map((item) => CartItem(
          id: item['id'] as int,
          productId: item['product_id'] as int,
          quantity: item['quantity'] as int,
          price: item['price'] as double,
          createdAt: DateTime.parse(item['created_at'] as String),
        )).toList();
      }
    } catch (e) {
      _error = 'حدث خطأ أثناء تحميل السلة';
      debugPrint('Error loading cart: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// إضافة منتج للسلة
  /// POST /api/cart/add
  Future<bool> addToCart(Product product, {int quantity = 1, int? variantId}) async {
    _error = null;

    try {
      if (_isAuthenticated) {
        final response = await _apiService.post('/api/cart/add', {
          'product_id': product.id,
          'quantity': quantity,
          if (variantId != null) 'variant_id': variantId,
        });

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }

        // تحديث السلة من الاستجابة
        if (response['data'] != null && response['data']['items'] is List) {
          _items = (response['data']['items'] as List)
              .map((item) => CartItem.fromJson(item))
              .toList();
        }
      } else {
        await _dbHelper.addToCart(
          product.id,
          product.name,
          product.image,
          product.finalPrice,
          quantity,
        );

        // تحديث السلة محلياً
        final existingIndex = _items.indexWhere((item) => item.productId == product.id);
        if (existingIndex >= 0) {
          _items[existingIndex] = _items[existingIndex].copyWith(
            quantity: _items[existingIndex].quantity + quantity,
          );
        } else {
          _items.add(CartItem(
            id: DateTime.now().millisecondsSinceEpoch,
            productId: product.id,
            product: product,
            quantity: quantity,
            price: product.finalPrice,
            createdAt: DateTime.now(),
          ));
        }
      }

      notifyListeners();
      return true;
    } catch (e) {
      _error = 'حدث خطأ أثناء إضافة المنتج';
      debugPrint('Error adding to cart: $e');
      notifyListeners();
      return false;
    }
  }

  /// تحديث الكمية
  /// POST /api/cart/update
  Future<bool> updateQuantity(int productId, int quantity) async {
    _error = null;

    try {
      if (quantity <= 0) {
        return removeFromCart(productId);
      }

      if (_isAuthenticated) {
        final item = _items.firstWhere(
          (item) => item.productId == productId,
          orElse: () => CartItem(
            id: 0,
            productId: 0,
            quantity: 0,
            price: 0,
            createdAt: DateTime.now(),
          ),
        );

        if (item.id == 0) return false;

        final response = await _apiService.post('/api/cart/update', {
          'item_id': item.id,
          'quantity': quantity,
        });

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }

        // تحديث السلة من الاستجابة
        if (response['data'] != null && response['data']['items'] is List) {
          _items = (response['data']['items'] as List)
              .map((item) => CartItem.fromJson(item))
              .toList();
        }
      } else {
        await _dbHelper.updateCartQuantity(productId, quantity);
        final index = _items.indexWhere((item) => item.productId == productId);
        if (index >= 0) {
          _items[index] = _items[index].copyWith(quantity: quantity);
        }
      }

      notifyListeners();
      return true;
    } catch (e) {
      _error = 'حدث خطأ أثناء تحديث الكمية';
      debugPrint('Error updating cart quantity: $e');
      notifyListeners();
      return false;
    }
  }

  /// حذف منتج من السلة
  /// DELETE /api/cart/{id}
  Future<bool> removeFromCart(int productId) async {
    _error = null;

    try {
      if (_isAuthenticated) {
        final item = _items.firstWhere(
          (item) => item.productId == productId,
          orElse: () => CartItem(
            id: 0,
            productId: 0,
            quantity: 0,
            price: 0,
            createdAt: DateTime.now(),
          ),
        );

        if (item.id == 0) return false;

        final response = await _apiService.delete('/api/cart/${item.id}');

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }

        // تحديث السلة من الاستجابة
        if (response['data'] != null && response['data']['items'] is List) {
          _items = (response['data']['items'] as List)
              .map((item) => CartItem.fromJson(item))
              .toList();
        }
      } else {
        await _dbHelper.removeFromCart(productId);
        _items.removeWhere((item) => item.productId == productId);
      }

      notifyListeners();
      return true;
    } catch (e) {
      _error = 'حدث خطأ أثناء حذف المنتج';
      debugPrint('Error removing from cart: $e');
      notifyListeners();
      return false;
    }
  }

  /// مسح السلة
  /// POST /api/cart/clear
  Future<void> clearCart() async {
    try {
      if (_isAuthenticated) {
        await _apiService.post('/api/cart/clear', {});
      } else {
        await _dbHelper.clearCart();
      }
      _items = [];
      _discount = 0;
      _couponCode = null;
      notifyListeners();
    } catch (e) {
      _error = 'حدث خطأ أثناء مسح السلة';
      debugPrint('Error clearing cart: $e');
      notifyListeners();
    }
  }

  /// تطبيق كوبون
  /// POST /api/cart/coupon
  Future<bool> applyCoupon(String code) async {
    _error = null;

    try {
      if (!_isAuthenticated) {
        _error = 'يجب تسجيل الدخول لاستخدام الكوبون';
        notifyListeners();
        return false;
      }

      final response = await _apiService.post('/api/cart/coupon', {
        'code': code.trim(),
      });

      if (response['success'] == true) {
        _couponCode = code;
        if (response['data'] != null) {
          _discount = (response['data']['discount'] ?? 0).toDouble();
          if (response['data']['items'] is List) {
            _items = (response['data']['items'] as List)
                .map((item) => CartItem.fromJson(item))
                .toList();
          }
        }
        notifyListeners();
        return true;
      } else {
        _error = response['message'] ?? 'الكوبون غير صالح';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = 'حدث خطأ أثناء تطبيق الكوبون';
      notifyListeners();
      return false;
    }
  }

  /// إزالة الكوبون
  void removeCoupon() {
    _couponCode = null;
    _discount = 0;
    notifyListeners();
  }

  /// التحقق إذا كان المنتج في السلة
  bool isInCart(int productId) {
    return _items.any((item) => item.productId == productId);
  }

  /// الحصول على كمية منتج في السلة
  int getQuantity(int productId) {
    final item = _items.firstWhere(
      (item) => item.productId == productId,
      orElse: () => CartItem(
        id: 0,
        productId: 0,
        quantity: 0,
        price: 0,
        createdAt: DateTime.now(),
      ),
    );
    return item.quantity;
  }

  /// مسح رسالة الخطأ
  void clearError() {
    _error = null;
    notifyListeners();
  }
}
