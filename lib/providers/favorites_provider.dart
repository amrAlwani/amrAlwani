import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../services/database_helper.dart';

/// مزود المفضلة
class FavoritesProvider with ChangeNotifier {
  List<Product> _favorites = [];
  Set<int> _favoriteIds = {};
  bool _isLoading = false;

  List<Product> get favorites => _favorites;
  bool get isLoading => _isLoading;
  int get count => _favorites.length;

  final DatabaseHelper _dbHelper = DatabaseHelper();

  FavoritesProvider() {
    loadFavorites();
  }

  /// تحميل المفضلة
  Future<void> loadFavorites() async {
    _isLoading = true;
    notifyListeners();

    try {
      _favorites = await _dbHelper.getFavorites();
      _favoriteIds = _favorites.map((p) => p.id).toSet();
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
  Future<void> toggleFavorite(Product product) async {
    if (isFavorite(product.id)) {
      await removeFromFavorites(product.id);
    } else {
      await addToFavorites(product);
    }
  }

  /// إضافة للمفضلة
  Future<void> addToFavorites(Product product) async {
    try {
      await _dbHelper.addToFavorites(product);
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
    for (final product in _favorites) {
      await _dbHelper.removeFromFavorites(product.id);
    }
    _favorites = [];
    _favoriteIds = {};
    notifyListeners();
  }
}
