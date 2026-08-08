import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;
  ApiClient._internal();

  final _storage = const FlutterSecureStorage();
  String? _token;
  String? _baseUrl;

  String get baseUrl => _baseUrl ?? 'https://api.oruzone.hrtime.in/api/v1';

  Future<void> setBaseUrl(String url) async {
    _baseUrl = url;
  }

  Future<void> setToken(String token) async {
    _token = token;
    await _storage.write(key: 'auth_token', value: token);
  }

  Future<String?> getToken() async {
    if (_token != null) return _token;
    _token = await _storage.read(key: 'auth_token');
    return _token;
  }

  Future<void> clearToken() async {
    _token = null;
    await _storage.delete(key: 'auth_token');
  }

  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  Future<Map<String, String>> _getHeaders() async {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    final token = await getToken();
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future<ApiResponse> get(String path, {Map<String, String>? queryParams}) async {
    try {
      final uri = Uri.parse('$baseUrl$path').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: await _getHeaders());
      return _handleResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  Future<ApiResponse> post(String path, {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.post(
        uri,
        headers: await _getHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _handleResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  Future<ApiResponse> put(String path, {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.put(
        uri,
        headers: await _getHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _handleResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  Future<ApiResponse> delete(String path, {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.delete(
        uri,
        headers: await _getHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _handleResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  ApiResponse _handleResponse(http.Response response) {
    try {
      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return ApiResponse(
          success: true,
          data: data,
          message: data['message'] as String?,
        );
      } else {
        return ApiResponse(
          success: false,
          message: data['message'] as String? ?? 'Request failed',
          data: data,
        );
      }
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Failed to parse response: ${e.toString()}',
      );
    }
  }

  // Auth endpoints
  Future<ApiResponse> sendOtp(String phone, {String countryCode = '+91'}) async {
    return post('/auth/send-otp', body: {
      'phone': phone,
      'country_code': countryCode,
    });
  }

  Future<ApiResponse> verifyOtp(String phone, String otp, {String countryCode = '+91'}) async {
    return post('/auth/verify-otp', body: {
      'phone': phone,
      'otp': otp,
      'country_code': countryCode,
    });
  }

  Future<ApiResponse> getProfile() async {
    return get('/profile');
  }

  Future<ApiResponse> updateProfile({
    String? name,
    String? bio,
    String? city,
  }) async {
    return put('/profile', body: {
      if (name != null) 'name': name,
      if (bio != null) 'bio': bio,
      if (city != null) 'city': city,
    });
  }

  // Trip endpoints
  Future<ApiResponse> searchTrips({
    required String origin,
    required String destination,
    String? date,
    int? seats,
  }) async {
    return get('/trips/search', queryParams: {
      'origin': origin,
      'destination': destination,
      if (date != null) 'date': date,
      if (seats != null) 'seats': seats.toString(),
    });
  }

  Future<ApiResponse> getTripDetails(String tripId) async {
    return get('/trips/$tripId');
  }

  Future<ApiResponse> bookTrip(String tripId, int seats) async {
    return post('/trips/$tripId/book', body: {
      'seats': seats,
    });
  }

  Future<ApiResponse> getRecentTrips() async {
    return get('/trips/recent');
  }
}

class ApiResponse {
  final bool success;
  final String? message;
  final dynamic data;

  ApiResponse({
    required this.success,
    this.message,
    this.data,
  });

  Map<String, dynamic> get asMap => data is Map<String, dynamic> ? data : {};
  List get asList => data is List ? data : [];
}
