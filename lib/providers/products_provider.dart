import 'package:flutter/foundation.dart' hide Category;
import '../models/product.dart';
import '../models/category.dart';
import '../services/api_service.dart';
import '../config/app_config.dart';

class ProductsProvider with ChangeNotifier {
  List<Product> _products = [];
  List<Product> _featuredProducts = [];
  List<Category> _categories = [];
  Product? _selectedProduct;

  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  bool _hasMore = true;
  int? _selectedCategoryId;
  String _searchQuery = '';
  String _sortBy = 'newest';

  // --- GETTERS ---
  List<Product> get products => _products;
  List<Product> get featuredProducts => _featuredProducts;
  List<Category> get categories => _categories;
  Product? get selectedProduct => _selectedProduct;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMore => _hasMore;
  int? get selectedCategoryId => _selectedCategoryId;
  String get searchQuery => _searchQuery;
  String get sortBy => _sortBy;

  final ApiService _apiService = ApiService();

  // --- DATA FETCHING METHODS ---

  /// جلب المنتجات مع الفلاتر والترقيم
  /// GET /api/products
  Future<void> fetchProducts({bool refresh = false}) async {
    if (_isLoading && !refresh) return;

    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
      _products = [];
    }

    if (!_hasMore) return;

    _isLoading = true;
    _error = null;
    if (refresh) notifyListeners();

    try {
      String endpoint = '/api/products'
          '?page=$_currentPage'
          '&per_page=${AppConfig.productsPerPage}'
          '&sort=$_sortBy';

      if (_selectedCategoryId != null) {
        endpoint += '&category=$_selectedCategoryId';
      }
      if (_searchQuery.isNotEmpty) {
        endpoint += '&search=${Uri.encodeComponent(_searchQuery)}';
      }

      final response = await _apiService.get(endpoint);

      if (response['success'] == true) {
        final data = response['data'];
        final List<Product> newProducts;

        if (data is List) {
          newProducts = data
              .map((p) => Product.fromJson(p as Map<String, dynamic>))
              .toList();
        } else if (data is Map && data['products'] is List) {
          newProducts = (data['products'] as List)
              .map((p) => Product.fromJson(p as Map<String, dynamic>))
              .toList();
        } else {
          newProducts = [];
        }

        if (refresh) {
          _products = newProducts;
        } else {
          _products.addAll(newProducts);
        }

        _hasMore = newProducts.length >= AppConfig.productsPerPage;
        if (_hasMore) _currentPage++;
      } else {
        _error = response['message'] ?? 'فشل جلب المنتجات';
        _hasMore = false;
      }
    } catch (e) {
      _error = 'حدث خطأ أثناء جلب المنتجات: $e';
      debugPrint('Error fetching products: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// جلب المنتجات المميزة
  /// GET /api/products/featured
  Future<void> fetchFeaturedProducts() async {
    try {
      final response = await _apiService.get('/api/products/featured?limit=10');

      if (response['success'] == true) {
        final data = response['data'];
        if (data is List) {
          _featuredProducts = data
              .map((p) => Product.fromJson(p as Map<String, dynamic>))
              .toList();
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error fetching featured products: $e');
    }
  }

  /// جلب التصنيفات
  /// GET /api/categories
  Future<void> fetchCategories() async {
    try {
      final response = await _apiService.get('/api/categories');

      if (response['success'] == true) {
        final data = response['data'];
        if (data is List) {
          _categories = data
              .map((c) => Category.fromJson(c as Map<String, dynamic>))
              .toList();
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error fetching categories: $e');
    }
  }

  /// جلب تفاصيل منتج محدد
  /// GET /api/products/{id}
  Future<void> fetchProductDetails(int productId) async {
    _isLoading = true;
    _selectedProduct = null;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get('/api/products/$productId');

      if (response['success'] == true && response['data'] != null) {
        _selectedProduct = Product.fromJson(response['data']);
      } else {
        _error = response['message'] ?? 'فشل جلب تفاصيل المنتج';
      }
    } catch (e) {
      _error = 'حدث خطأ أثناء جلب تفاصيل المنتج: $e';
      debugPrint('Error fetching product details: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // --- ACTION METHODS ---

  void setCategory(int? categoryId) {
    if (_selectedCategoryId == categoryId) return;
    _selectedCategoryId = categoryId;
    _searchQuery = '';
    fetchProducts(refresh: true);
  }

  void setSearchQuery(String query) {
    if (_searchQuery == query) return;
    _searchQuery = query.trim();
    fetchProducts(refresh: true);
  }

  void setSortBy(String sort) {
    if (_sortBy == sort) return;
    _sortBy = sort;
    fetchProducts(refresh: true);
  }

  void clearFilters() {
    _selectedCategoryId = null;
    _searchQuery = '';
    _sortBy = 'newest';
    fetchProducts(refresh: true);
  }

  void clearSelectedProduct() {
    _selectedProduct = null;
    notifyListeners();
  }
}
