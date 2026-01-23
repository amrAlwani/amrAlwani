import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../services/database_helper.dart';
import '../config/api_endpoints.dart';


class FavoritesProvider with ChangeNotifier {
  List<Product> _favorites = [];
  Set<int> _favoriteIds = {};
  bool _isLoading = false;

  List<Product> get favorites => _favorites;
  bool get isLoading => _isLoading;
  int get count => _favorites.length;

  final DatabaseHelper _dbHelper = DatabaseHelper.instance;
  final ApiService _apiService = ApiService();
  
  bool _isAuthenticated = false;

  FavoritesProvider() {
    _loadLocalFavoriteIds();
  }

  Future<void> _loadLocalFavoriteIds() async {
    try {
      _favoriteIds = (await _dbHelper.getFavoriteIds()).toSet();
      notifyListeners();
    } catch (e) {
      debugPrint('Error loading favorite IDs: $e');
    }
  }

  void setAuthState(bool isAuthenticated) {
    _isAuthenticated = isAuthenticated;
    loadFavorites();
  }

  Future<void> loadFavorites() async {
    _isLoading = true;
    notifyListeners();

    try {
      if (_isAuthenticated) {
        final response = await _apiService.get(ApiEndpoints.wishlist);
        if (response['success'] == true) {
          final data = response['data'];
          if (data is List) {
            _favorites = data
                .map((p) => Product.fromJson(p as Map<String, dynamic>))
                .toList();
            _favoriteIds = _favorites.map((p) => p.id).toSet();
          }
        }
      } else {
        // للمستخدمين غير المسجلين، نحمل فقط الـ IDs محلياً
        _favoriteIds = (await _dbHelper.getFavoriteIds()).toSet();
        _favorites = []; // سيتم تحميل المنتجات عند الحاجة
      }
    } catch (e) {
      debugPrint('Error loading favorites: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  /// التحقق إذا كان المنتج مفضل
  bool isFavorite(int productId) {
    return _favoriteIds.contains(productId);
  }

  /// تبديل حالة المفضلة
  /// POST /api/wishlist
  Future<void> toggleFavorite(Product product) async {
    try {
      if (_isAuthenticated) {
        final response = await _apiService.post(ApiEndpoints.wishlistToggle, {
          'product_id': product.id,
        });

        if (response['success'] == true) {
          if (isFavorite(product.id)) {
            _favorites.removeWhere((p) => p.id == product.id);
            _favoriteIds.remove(product.id);
          } else {
            _favorites.insert(0, product);
            _favoriteIds.add(product.id);
          }
          notifyListeners();
        }
      } else {
        if (isFavorite(product.id)) {
          await removeFromFavorites(product.id);
        } else {
          await addToFavorites(product);
        }
      }
    } catch (e) {
      debugPrint('Error toggling favorite: $e');
    }
  }

  /// إضافة للمفضلة
  Future<void> addToFavorites(Product product) async {
    try {
      await _dbHelper.addToFavorites(product.id);
      _favorites.insert(0, product);
      _favoriteIds.add(product.id);
      notifyListeners();
    } catch (e) {
      debugPrint('Error adding to favorites: $e');
    }
  }

  /// حذف من المفضلة
  Future<void> removeFromFavorites(int productId) async {
    try {
      await _dbHelper.removeFromFavorites(productId);
      _favorites.removeWhere((p) => p.id == productId);
      _favoriteIds.remove(productId);
      notifyListeners();
    } catch (e) {
      debugPrint('Error removing from favorites: $e');
    }
  }

  /// مسح كل المفضلة
  Future<void> clearFavorites() async {
    try {
      await _dbHelper.clearFavorites();
      _favorites = [];
      _favoriteIds = {};
      notifyListeners();
    } catch (e) {
      debugPrint('Error clearing favorites: $e');
    }
  }
}
