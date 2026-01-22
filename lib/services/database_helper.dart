import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/product.dart';
import '../models/cart_item.dart';

/// مساعد قاعدة البيانات المحلية
/// تم التصحيح: إصلاح مشكلة قراءة بيانات المنتج المخزنة
class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  factory DatabaseHelper() => _instance;
  DatabaseHelper._internal();

  static Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'ecommerce.db');

    return await openDatabase(
      path,
      version: 2, // تم التحديث: رقم الإصدار
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // جدول المفضلة
    await db.execute('''
      CREATE TABLE favorites (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER UNIQUE,
        product_data TEXT,
        created_at TEXT
      )
    ''');

    // جدول السلة المحلية
    await db.execute('''
      CREATE TABLE cart (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER UNIQUE,
        product_data TEXT,
        quantity INTEGER DEFAULT 1,
        price REAL,
        created_at TEXT
      )
    ''');

    // جدول المنتجات المخزنة مؤقتاً
    await db.execute('''
      CREATE TABLE cached_products (
        id INTEGER PRIMARY KEY,
        data TEXT,
        cached_at TEXT
      )
    ''');
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      // إضافة عمود product_data إذا لم يكن موجوداً
      try {
        await db.execute('ALTER TABLE cart ADD COLUMN product_data TEXT');
      } catch (e) {
        // تجاهل إذا كان موجوداً بالفعل
      }
    }
  }

  // ==================== المفضلة ====================

  Future<List<Product>> getFavorites() async {
    final db = await database;
    final results = await db.query('favorites', orderBy: 'created_at DESC');

    List<Product> products = [];
    for (final row in results) {
      try {
        final productData = row['product_data'] as String?;
        if (productData != null && productData.isNotEmpty) {
          // تم التصحيح: استخدام jsonDecode بدلاً من Uri.decodeFull
          final decoded = jsonDecode(productData);
          products.add(Product.fromJson(decoded));
        }
      } catch (e) {
        debugPrint('Error parsing favorite product: $e');
      }
    }
    return products;
  }

  Future<bool> isFavorite(int productId) async {
    final db = await database;
    final results = await db.query(
      'favorites',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
    return results.isNotEmpty;
  }

  Future<void> addToFavorites(Product product) async {
    final db = await database;
    await db.insert(
      'favorites',
      {
        'product_id': product.id,
        // تم التصحيح: استخدام jsonEncode بدلاً من Uri.encodeFull
        'product_data': jsonEncode(product.toJson()),
        'created_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> removeFromFavorites(int productId) async {
    final db = await database;
    await db.delete(
      'favorites',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
  }

  // ==================== السلة ====================

  Future<List<CartItem>> getCartItems() async {
    final db = await database;
    final results = await db.query('cart', orderBy: 'created_at DESC');

    List<CartItem> items = [];
    for (final row in results) {
      try {
        Product? product;
        final productData = row['product_data'] as String?;
        if (productData != null && productData.isNotEmpty) {
          final decoded = jsonDecode(productData);
          product = Product.fromJson(decoded);
        }

        items.add(CartItem(
          id: row['id'] as int,
          productId: row['product_id'] as int,
          product: product,
          quantity: row['quantity'] as int? ?? 1,
          price: (row['price'] as num?)?.toDouble() ?? 0.0,
          createdAt: DateTime.tryParse(row['created_at'] as String? ?? '') ?? DateTime.now(),
        ));
      } catch (e) {
        debugPrint('Error parsing cart item: $e');
      }
    }
    return items;
  }

  Future<void> addToCart(int productId, double price, {int quantity = 1, Product? product}) async {
    final db = await database;

    final existing = await db.query(
      'cart',
      where: 'product_id = ?',
      whereArgs: [productId],
    );

    if (existing.isNotEmpty) {
      final currentQty = existing.first['quantity'] as int? ?? 0;
      await db.update(
        'cart',
        {'quantity': currentQty + quantity},
        where: 'product_id = ?',
        whereArgs: [productId],
      );
    } else {
      await db.insert('cart', {
        'product_id': productId,
        'product_data': product != null ? jsonEncode(product.toJson()) : null,
        'quantity': quantity,
        'price': price,
        'created_at': DateTime.now().toIso8601String(),
      });
    }
  }

  Future<void> updateCartQuantity(int productId, int quantity) async {
    final db = await database;
    if (quantity <= 0) {
      await removeFromCart(productId);
    } else {
      await db.update(
        'cart',
        {'quantity': quantity},
        where: 'product_id = ?',
        whereArgs: [productId],
      );
    }
  }

  Future<void> removeFromCart(int productId) async {
    final db = await database;
    await db.delete(
      'cart',
      where: 'product_id = ?',
      whereArgs: [productId],
    );
  }

  Future<void> clearCart() async {
    final db = await database;
    await db.delete('cart');
  }

  Future<int> getCartCount() async {
    final db = await database;
    final result = await db.rawQuery('SELECT COALESCE(SUM(quantity), 0) as count FROM cart');
    return (result.first['count'] as int?) ?? 0;
  }

  // ==================== التخزين المؤقت ====================

  Future<void> cacheProduct(Product product) async {
    final db = await database;
    await db.insert(
      'cached_products',
      {
        'id': product.id,
        'data': jsonEncode(product.toJson()),
        'cached_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<Product?> getCachedProduct(int productId) async {
    final db = await database;
    final results = await db.query(
      'cached_products',
      where: 'id = ?',
      whereArgs: [productId],
    );

    if (results.isEmpty) return null;

    try {
      final decoded = jsonDecode(results.first['data'] as String);
      return Product.fromJson(decoded);
    } catch (e) {
      return null;
    }
  }

  Future<void> clearCache() async {
    final db = await database;
    await db.delete('cached_products');
  }
}
