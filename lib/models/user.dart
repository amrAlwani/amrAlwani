/// نموذج المستخدم
/// تم التصحيح: إضافة التعامل الآمن مع null
class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final String? firebaseUid;
  final String role; // تم الإضافة: حقل الدور
  final DateTime createdAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    this.firebaseUid,
    this.role = 'user', // تم التصحيح: القيمة الافتراضية 'user' وليس 'customer'
    required this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: _parseInt(json['id']),
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString(),
      avatar: json['avatar']?.toString(),
      firebaseUid: json['firebase_uid']?.toString(),
      role: json['role']?.toString() ?? 'user',
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
      'email': email,
      'phone': phone,
      'avatar': avatar,
      'firebase_uid': firebaseUid,
      'role': role,
      'created_at': createdAt.toIso8601String(),
    };
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? avatar,
    String? firebaseUid,
    String? role,
    DateTime? createdAt,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatar: avatar ?? this.avatar,
      firebaseUid: firebaseUid ?? this.firebaseUid,
      role: role ?? this.role,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  /// التحقق من صلاحيات المدير
  bool get isAdmin => role == 'admin';
}
