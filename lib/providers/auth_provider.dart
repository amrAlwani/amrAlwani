import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:firebase_auth/firebase_auth.dart' as firebase;
import 'package:google_sign_in/google_sign_in.dart';
import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';
import '../config/api_endpoints.dart';
import '../models/user.dart' as app_user;
import '../services/api_service.dart';

/// مزود المصادقة
class AuthProvider with ChangeNotifier {
  app_user.User? _user;
  String? _token;
  bool _isLoading = false;
  String? _error;
  final firebase.FirebaseAuth _firebaseAuth = firebase.FirebaseAuth.instance;
  final ApiService _apiService = ApiService();

  app_user.User? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _token != null && _user != null;

  AuthProvider() {
    _loadUserFromStorage();
  }

  Future<void> _loadUserFromStorage() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _token = prefs.getString(AppConfig.tokenKey);
      final userDataString = prefs.getString(AppConfig.userKey);

      if (_token != null && userDataString != null) {
        try {
          _user = app_user.User.fromJson(jsonDecode(userDataString));
          _apiService.setToken(_token);
          notifyListeners();
        } catch (e) {
          debugPrint('Error loading user from storage: $e');
          await logout();
        }
      }
    } catch (e) {
      debugPrint('Error in _loadUserFromStorage: $e');
    }
  }

  /// تسجيل الدخول بالبريد وكلمة المرور
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    try {
      final response = await _apiService.post(ApiEndpoints.login, {
        'email': email.trim(),
        'password': password,
      });

      if (response['success'] == true) {
        await _handleSuccessResponse(response['data']);
        return true;
      } else {
        _error = response['message'] ?? 'فشل تسجيل الدخول';
        return false;
      }
    } catch (e) {
      _error = 'خطأ غير متوقع: ${e.toString()}';
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// إنشاء حساب جديد
  Future<bool> register(String name, String email, String password, String phone) async {
    _setLoading(true);
    try {
      final response = await _apiService.post(ApiEndpoints.register, {
        'name': name,
        'email': email.trim(),
        'password': password,
        'phone': phone,
      });

      if (response['success'] == true) {
        await _handleSuccessResponse(response['data']);
        return true;
      } else {
        _error = response['message'] ?? 'فشل إنشاء الحساب';
        return false;
      }
    } catch (e) {
      _error = 'خطأ غير متوقع: ${e.toString()}';
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// تسجيل الدخول عبر Google
  Future<bool> signInWithGoogle() async {
    _setLoading(true);
    try {
      final googleUser = await GoogleSignIn().signIn();
      if (googleUser == null) {
        _error = 'تم إلغاء تسجيل الدخول';
        return false;
      }

      final googleAuth = await googleUser.authentication;
      final credential = firebase.GoogleAuthProvider.credential(
        accessToken: googleAuth.accessToken,
        idToken: googleAuth.idToken,
      );

      final userCredential = await _firebaseAuth.signInWithCredential(credential);
      final firebaseUser = userCredential.user;

      if (firebaseUser == null) {
        _error = 'فشل الحصول على بيانات المستخدم من Firebase';
        return false;
      }

      final response = await _apiService.post(ApiEndpoints.socialLogin, {
        'email': firebaseUser.email,
        'name': firebaseUser.displayName,
        'firebase_uid': firebaseUser.uid,
        'avatar': firebaseUser.photoURL,
        'provider': 'google',
      });

      if (response['success'] == true) {
        await _handleSuccessResponse(response['data']);
        return true;
      } else {
        _error = response['message'] ?? 'فشل تسجيل الدخول عبر Google';
        return false;
      }
    } catch (e) {
      _error = 'فشل الدخول عبر Google: ${e.toString()}';
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// تسجيل الدخول عبر Facebook
  Future<bool> signInWithFacebook() async {
    _setLoading(true);
    try {
      final LoginResult result = await FacebookAuth.instance.login(
        permissions: ['email', 'public_profile'],
      );

      if (result.status != LoginStatus.success || result.accessToken == null) {
        _error = 'فشل تسجيل الدخول عبر Facebook';
        return false;
      }

      final facebookCredential = firebase.FacebookAuthProvider.credential(
        result.accessToken!.token,
      );

      final userCredential = await _firebaseAuth.signInWithCredential(facebookCredential);
      final firebaseUser = userCredential.user;
      
      if (firebaseUser == null) {
        _error = 'فشل الحصول على بيانات المستخدم من Firebase';
        return false;
      }
      
      final response = await _apiService.post(ApiEndpoints.socialLogin, {
        'email': firebaseUser.email,
        'name': firebaseUser.displayName,
        'firebase_uid': firebaseUser.uid,
        'avatar': firebaseUser.photoURL,
        'provider': 'facebook',
      });

      if (response['success'] == true) {
        await _handleSuccessResponse(response['data']);
        return true;
      } else {
        _error = response['message'] ?? 'فشل تسجيل الدخول عبر Facebook';
        return false;
      }
    } catch (e) {
      _error = 'فشل الدخول عبر Facebook: ${e.toString()}';
      return false;
    } finally {
      _setLoading(false);
    }
  }


  Future<void> _handleSuccessResponse(Map<String, dynamic> data) async {
    _token = data['token'];
    _user = app_user.User.fromJson(data['user']);
    _apiService.setToken(_token);
    await _saveUserToStorage();
    notifyListeners();
  }

  Future<void> _saveUserToStorage() async {
    final prefs = await SharedPreferences.getInstance();
    if (_token != null) {
      await prefs.setString(AppConfig.tokenKey, _token!);
    }
    if (_user != null) {
      await prefs.setString(AppConfig.userKey, jsonEncode(_user!.toJson()));
    }
  }

  /// تسجيل الخروج
  Future<void> logout() async {
    try {
      await _firebaseAuth.signOut();
      await GoogleSignIn().signOut();
      await FacebookAuth.instance.logOut();
    } catch (e) {
      debugPrint('Error during logout: $e');
    }

    _user = null;
    _token = null;
    _apiService.setToken(null);

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConfig.tokenKey);
    await prefs.remove(AppConfig.userKey);
    notifyListeners();
  }

  void _setLoading(bool value) {
    _isLoading = value;
    if (value) _error = null;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  String _mapFirebaseError(firebase.FirebaseAuthException e) {
    switch (e.code) {
      case 'user-not-found':
        return 'الحساب غير موجود';
      case 'wrong-password':
        return 'كلمة المرور خاطئة';
      case 'email-already-in-use':
        return 'البريد مستخدم بالفعل';
      case 'invalid-email':
        return 'البريد الإلكتروني غير صالح';
      case 'weak-password':
        return 'كلمة المرور ضعيفة جداً';
      case 'too-many-requests':
        return 'محاولات كثيرة، حاول لاحقاً';
      case 'network-request-failed':
        return 'فشل الاتصال بالشبكة';
      default:
        return 'خطأ في المصادقة: ${e.message}';
    }
  }
}
