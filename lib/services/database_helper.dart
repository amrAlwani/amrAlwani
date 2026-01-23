import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/product.dart';
import '../models/cart_item.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('swiftcart.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
      onUpgrade: _upgradeDB,
    );
  }

  Future<void> _createDB(Database db, int version) async {
    // Cart Table
    await db.execute('''
      CREATE TABLE cart (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        product_image TEXT,
        price REAL NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
      )
    ''');

    // Favorites Table
    await db.execute('''
      CREATE TABLE favorites (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL UNIQUE,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
      )
    ''');

    // Search History Table
    await db.execute('''
      CREATE TABLE search_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        query TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
      )
    ''');

    // Recently Viewed Table
    await db.execute('''
      CREATE TABLE recently_viewed (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL UNIQUE,
        viewed_at TEXT DEFAULT CURRENT_TIMESTAMP
      )
    ''');

    // User Settings Table
    await db.execute('''
      CREATE TABLE settings (
        key TEXT PRIMARY KEY,
        value TEXT
      )
    ''');
  }

  Future<void> _upgradeDB(Database db, int oldVersion, int newVersion) async {
    // Handle database migrations here
  }

  // ==================== CART OPERATIONS ====================

  Future<List<Map<String, dynamic>>> getCartItems() async {
    final db = await database;
    return await db.query('cart', orderBy: 'created_at DESC');
  }

  Future<int> addToCart(int productId, String name, String? image, double price, int quantity) async {
    final db = await database;
    
    // Check if item exists
    final existing = await db.query(
      'cart',
      where: 'product_id = ?',
      whereArgs: [productId],
    );

    if (existing.isNotEmpty) {
      // Update quantity
      final currentQty = existing.first['quantity'] as int;
      return await db.update(
        'cart',
        {'quantity': currentQty + quantity},
        where: 'product_id = ?',
        whereArgs: [productId],
      );
    }

    // Insert new item
    return await db.insert('cart', {
      'product_id': productId,
      'product_name': name,
      'product_image': image,
      'price': price,
      'quantity': quantity,
    });
  }

  Future<int> updateCartQuantity(int productId, int quantity) async {
    final db = await database;
    
    if (quantity <= 0) {
      return await removeFromCart(productId);
    }

    return await db.update(
      'cart',
      {'quantity': quantity},
      where: 'product_id = ?',
      whereArgs: [productId],
    );
  }

  Future<int> removeFromCart(int productId) async {
    final db = await database;
    return await db.delete(
      'cart',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
  }

  Future<int> clearCart() async {
    final db = await database;
    return await db.delete('cart');
  }

  Future<int> getCartCount() async {
    final db = await database;
    final result = await db.rawQuery('SELECT SUM(quantity) as count FROM cart');
    return result.first['count'] as int? ?? 0;
  }

  // ==================== FAVORITES OPERATIONS ====================

  Future<List<int>> getFavoriteIds() async {
    final db = await database;
    final result = await db.query('favorites');
    return result.map((e) => e['product_id'] as int).toList();
  }

  Future<bool> isFavorite(int productId) async {
    final db = await database;
    final result = await db.query(
      'favorites',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
    return result.isNotEmpty;
  }

  Future<int> addToFavorites(int productId) async {
    final db = await database;
    try {
      return await db.insert('favorites', {'product_id': productId});
    } catch (e) {
      // Already exists
      return 0;
    }
  }

  Future<int> removeFromFavorites(int productId) async {
    final db = await database;
    return await db.delete(
      'favorites',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
  }

  Future<int> clearFavorites() async {
    final db = await database;
    return await db.delete('favorites');
  }

  // ==================== SEARCH HISTORY ====================

  Future<List<String>> getSearchHistory({int limit = 10}) async {
    final db = await database;
    final result = await db.query(
      'search_history',
      orderBy: 'created_at DESC',
      limit: limit,
    );
    return result.map((e) => e['query'] as String).toList();
  }

  Future<int> addSearchQuery(String query) async {
    final db = await database;
    
    // Remove if exists (to update timestamp)
    await db.delete(
      'search_history',
      where: 'query = ?',
      whereArgs: [query],
    );

    return await db.insert('search_history', {'query': query});
  }

  Future<int> clearSearchHistory() async {
    final db = await database;
    return await db.delete('search_history');
  }

  // ==================== RECENTLY VIEWED ====================

  Future<List<int>> getRecentlyViewedIds({int limit = 20}) async {
    final db = await database;
    final result = await db.query(
      'recently_viewed',
      orderBy: 'viewed_at DESC',
      limit: limit,
    );
    return result.map((e) => e['product_id'] as int).toList();
  }

  Future<int> addToRecentlyViewed(int productId) async {
    final db = await database;
    
    // Remove if exists (to update timestamp)
    await db.delete(
      'recently_viewed',
      where: 'product_id = ?',
      whereArgs: [productId],
    );

    return await db.insert('recently_viewed', {
      'product_id': productId,
      'viewed_at': DateTime.now().toIso8601String(),
    });
  }

  Future<int> clearRecentlyViewed() async {
    final db = await database;
    return await db.delete('recently_viewed');
  }

  // ==================== SETTINGS ====================

  Future<String?> getSetting(String key) async {
    final db = await database;
    final result = await db.query(
      'settings',
      where: 'key = ?',
      whereArgs: [key],
    );
    if (result.isEmpty) return null;
    return result.first['value'] as String?;
  }

  Future<int> setSetting(String key, String value) async {
    final db = await database;
    return await db.insert(
      'settings',
      {'key': key, 'value': value},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<int> deleteSetting(String key) async {
    final db = await database;
    return await db.delete(
      'settings',
      where: 'key = ?',
      whereArgs: [key],
    );
  }

  // ==================== UTILITY ====================

  Future<void> close() async {
    final db = await database;
    await db.close();
    _database = null;
  }

  Future<void> deleteDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'swiftcart.db');
    await databaseFactory.deleteDatabase(path);
    _database = null;
  }
}
