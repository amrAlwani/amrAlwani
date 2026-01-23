import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:cookie_jar/cookie_jar.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

/// خدمة الـ API
/// متوافقة مع SwiftCart PHP MVC Backend
class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  late Dio _dio;
  String? _token;
  final CookieJar _cookieJar = CookieJar();

  ApiService._internal() {
    _initDio();
  }

  void _initDio() {
    BaseOptions options = BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: const Duration(milliseconds: AppConfig.connectionTimeout),
      receiveTimeout: const Duration(milliseconds: AppConfig.receiveTimeout),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'User-Agent': 'SwiftCart-Flutter/2.0',
      },
    );

    _dio = Dio(options);
    _dio.interceptors.add(CookieManager(_cookieJar));

    if (kDebugMode) {
      _dio.interceptors.add(LogInterceptor(
        responseBody: true,
        requestBody: true,
        error: true,
      ));
    }
  }

  /// تهيئة الخدمة وتحميل التوكن المحفوظ
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(AppConfig.tokenKey);
    if (_token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_token';
    }
  }

  /// تعيين التوكن
  void setToken(String? token) {
    _token = token;
    if (token != null && token.isNotEmpty) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    } else {
      _dio.options.headers.remove('Authorization');
    }
  }

  /// الحصول على التوكن
  String? get token => _token;

  /// طلب GET
  Future<Map<String, dynamic>> get(String endpoint) async {
    try {
      final response = await _dio.get(endpoint);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleDioError(e);
    } catch (e) {
      return {'success': false, 'message': 'خطأ غير متوقع: $e'};
    }
  }

  /// طلب POST
  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> data) async {
    try {
      final response = await _dio.post(endpoint, data: jsonEncode(data));
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleDioError(e);
    } catch (e) {
      return {'success': false, 'message': 'خطأ غير متوقع: $e'};
    }
  }

  /// طلب PUT
  Future<Map<String, dynamic>> put(String endpoint, Map<String, dynamic> data) async {
    try {
      final response = await _dio.put(endpoint, data: jsonEncode(data));
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleDioError(e);
    } catch (e) {
      return {'success': false, 'message': 'خطأ غير متوقع: $e'};
    }
  }

  /// طلب DELETE
  Future<Map<String, dynamic>> delete(String endpoint) async {
    try {
      final response = await _dio.delete(endpoint);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleDioError(e);
    } catch (e) {
      return {'success': false, 'message': 'خطأ غير متوقع: $e'};
    }
  }

  /// رفع ملف
  Future<Map<String, dynamic>> uploadFile(String endpoint, String filePath, {String fieldName = 'file'}) async {
    try {
      FormData formData = FormData.fromMap({
        fieldName: await MultipartFile.fromFile(filePath),
      });
      final response = await _dio.post(endpoint, data: formData);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleDioError(e);
    } catch (e) {
      return {'success': false, 'message': 'خطأ في رفع الملف: $e'};
    }
  }

  /// معالجة الاستجابة
  Map<String, dynamic> _handleResponse(Response response) {
    if (response.data == null) {
      return {'success': false, 'message': 'استجابة فارغة من السيرفر'};
    }

    if (response.data is String) {
      try {
        final decoded = jsonDecode(response.data);
        if (decoded is Map<String, dynamic>) {
          return decoded;
        }
        return {'success': true, 'data': decoded};
      } catch (e) {
        return {
          'success': false,
          'message': 'فشل تحليل استجابة السيرفر',
          'raw_response': response.data,
        };
      }
    } else if (response.data is Map<String, dynamic>) {
      return response.data;
    } else {
      return {'success': false, 'message': 'نوع استجابة غير متوقع'};
    }
  }

  /// معالجة أخطاء Dio
  Map<String, dynamic> _handleDioError(DioException e) {
    String message;

    switch (e.type) {
      case DioExceptionType.connectionTimeout:
        message = 'انتهت مهلة الاتصال، تحقق من اتصالك بالإنترنت';
        break;
      case DioExceptionType.sendTimeout:
        message = 'انتهت مهلة إرسال البيانات';
        break;
      case DioExceptionType.receiveTimeout:
        message = 'انتهت مهلة استقبال البيانات';
        break;
      case DioExceptionType.connectionError:
        message = 'فشل الاتصال بالسيرفر، تحقق من اتصالك بالإنترنت';
        break;
      case DioExceptionType.badResponse:
        final responseData = e.response?.data;
        if (responseData is Map && responseData['message'] != null) {
          message = responseData['message'].toString();
        } else if (e.response?.statusCode == 401) {
          message = 'غير مصرح لك، يرجى تسجيل الدخول';
        } else if (e.response?.statusCode == 403) {
          message = 'لا تملك صلاحية للوصول';
        } else if (e.response?.statusCode == 404) {
          message = 'المورد غير موجود';
        } else if (e.response?.statusCode == 422) {
          message = 'بيانات غير صالحة';
        } else {
          message = 'خطأ في السيرفر: ${e.response?.statusCode}';
        }
        break;
      case DioExceptionType.cancel:
        message = 'تم إلغاء الطلب';
        break;
      case DioExceptionType.unknown:
      default:
        if (e.error is FormatException) {
          message = 'خطأ في تنسيق الاستجابة من السيرفر';
        } else {
          message = 'خطأ غير متوقع في الاتصال';
        }
        break;
    }

    return {
      'success': false,
      'message': message,
      'status_code': e.response?.statusCode,
    };
  }
}
