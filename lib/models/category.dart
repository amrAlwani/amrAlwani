/// نموذج التصنيف
class Category {
  final int id;
  final String name;
  final String? slug;
  final String? description;
  final String? image;
  final int? parentId;
  final int sortOrder;
  final int productsCount;
  final bool isActive;
  final DateTime createdAt;

  Category({
    required this.id,
    required this.name,
    this.slug,
    this.description,
    this.image,
    this.parentId,
    this.sortOrder = 0,
    this.productsCount = 0,
    this.isActive = true,
    required this.createdAt,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: _parseInt(json['id']),
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
      description: json['description']?.toString(),
      image: json['image']?.toString(),
      parentId: json['parent_id'] != null ? _parseInt(json['parent_id']) : null,
      sortOrder: _parseInt(json['sort_order']),
      productsCount: _parseInt(json['products_count']),
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'slug': slug,
      'description': description,
      'image': image,
      'parent_id': parentId,
      'sort_order': sortOrder,
      'products_count': productsCount,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
