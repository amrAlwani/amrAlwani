import 'package:flutter/foundation.dart';
import '../models/cart_item.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../services/database_helper.dart';

/// مزود السلة
/// تم التصحيح: إصلاح endpoints الـ API وتحسين التزامن
class CartProvider with ChangeNotifier {
  List<CartItem> _items = [];
  bool _isLoading = false;
  String? _error;

  List<CartItem> get items => _items;
  bool get isLoading => _isLoading;
  String? get error => _error;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0, (sum, item) => sum + item.total);

  double get shipping => subtotal > 200 ? 0 : 25; // شحن مجاني فوق 200

  double get tax => subtotal * 0.15;

  double get total => subtotal + shipping + tax;

  final ApiService _apiService = ApiService();
  final DatabaseHelper _dbHelper = DatabaseHelper();

  bool _isAuthenticated = false;

  void setAuthState(bool isAuthenticated) {
    _isAuthenticated = isAuthenticated;
    loadCart();
  }

  /// تحميل السلة
  Future<void> loadCart() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      if (_isAuthenticated) {
        // تم التصحيح: تطابق endpoint مع PHP
        final response = await _apiService.get('cart.php?action=list');
        if (response['success'] == true) {
          final data = response['data'];
          if (data != null && data['items'] is List) {
            _items = (data['items'] as List)
                .map((item) => CartItem.fromJson(item))
                .toList();
          } else {
            _items = [];
          }
        }
      } else {
        _items = await _dbHelper.getCartItems();
      }
    } catch (e) {
      _error = 'حدث خطأ أثناء تحميل السلة';
      debugPrint('Error loading cart: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// إضافة منتج للسلة
  Future<bool> addToCart(Product product, {int quantity = 1}) async {
    _error = null;

    try {
      if (_isAuthenticated) {
        // تم التصحيح: استخدام action=add
        final response = await _apiService.post('cart.php?action=add', {
          'product_id': product.id,
          'quantity': quantity,
        });

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }
      } else {
        await _dbHelper.addToCart(
          product.id, 
          product.finalPrice, 
          quantity: quantity,
          product: product,
        );
      }

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
  Future<bool> updateQuantity(int productId, int quantity) async {
    _error = null;

    try {
      if (quantity <= 0) {
        return removeFromCart(productId);
      }

      if (_isAuthenticated) {
        // تم التصحيح: استخدام item_id و action=update
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

        final response = await _apiService.post('cart.php?action=update', {
          'item_id': item.id,
          'quantity': quantity,
        });

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }
      } else {
        await _dbHelper.updateCartQuantity(productId, quantity);
      }

      final index = _items.indexWhere((item) => item.productId == productId);
      if (index >= 0) {
        _items[index] = _items[index].copyWith(quantity: quantity);
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

        // تم التصحيح: استخدام action=remove و item_id
        final response = await _apiService.get('cart.php?action=remove&item_id=${item.id}');

        if (response['success'] != true) {
          _error = response['message'];
          notifyListeners();
          return false;
        }
      } else {
        await _dbHelper.removeFromCart(productId);
      }

      _items.removeWhere((item) => item.productId == productId);
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
  Future<void> clearCart() async {
    try {
      if (_isAuthenticated) {
        await _apiService.get('cart.php?action=clear');
      } else {
        await _dbHelper.clearCart();
      }
      _items = [];
      notifyListeners();
    } catch (e) {
      _error = 'حدث خطأ أثناء مسح السلة';
      debugPrint('Error clearing cart: $e');
      notifyListeners();
    }
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
}
