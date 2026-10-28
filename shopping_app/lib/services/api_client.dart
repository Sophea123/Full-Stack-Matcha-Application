import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

/// An error returned by the API (or by the network layer).
class ApiException implements Exception {
  ApiException(this.statusCode, this.message, {this.errors = const {}});

  final int statusCode;
  final String message;
  final Map<String, List<String>> errors;

  bool get isUnauthorized => statusCode == 401;
  bool get isNetworkError => statusCode == 0;

  factory ApiException.fromResponse(http.Response response) {
    Map<String, dynamic> body = const {};
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) body = decoded;
    } catch (_) {
      // The body was empty or not JSON; fall back to the status message.
    }

    final errors = <String, List<String>>{};
    final rawErrors = body['errors'];
    if (rawErrors is Map) {
      rawErrors.forEach((key, value) {
        if (value is List) {
          errors[key.toString()] = value.map((e) => e.toString()).toList();
        }
      });
    }

    final message = (body['message'] as String?)?.trim();
    return ApiException(
      response.statusCode,
      message == null || message.isEmpty
          ? _fallbackMessage(response.statusCode)
          : message,
      errors: errors,
    );
  }

  static String _fallbackMessage(int status) {
    if (status == 401) return 'Your session has expired. Please sign in again.';
    if (status == 403) return 'You are not allowed to do that.';
    if (status == 404) return 'We could not find what you were looking for.';
    if (status == 422) return 'Please check the information you entered.';
    if (status == 429) return 'Too many attempts. Please wait a minute.';
    if (status >= 500) {
      return 'The server ran into a problem. Please try again.';
    }
    return 'Something went wrong. Please try again.';
  }

  @override
  String toString() => message;
}

/// Thin JSON wrapper around the Laravel API.
class ApiClient {
  ApiClient({http.Client? httpClient, String? baseUrl})
    : _http = httpClient ?? http.Client(),
      baseUrl = baseUrl ?? defaultBaseUrl;

  final http.Client _http;
  final String baseUrl;

  /// The Sanctum personal access token, when signed in.
  String? token;

  static const _override = String.fromEnvironment('API_BASE_URL');

  /// `flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api`
  static String get defaultBaseUrl {
    if (_override.isNotEmpty) return _override;
    if (kIsWeb) return 'http://127.0.0.1:8000/api';
    if (defaultTargetPlatform == TargetPlatform.android) {
      // Android emulators reach the host machine through 10.0.2.2.
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }

  Map<String, String> get _headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (token != null && token!.isNotEmpty) 'Authorization': 'Bearer $token',
  };

  Future<dynamic> get(String path, {Map<String, String>? query}) =>
      _send('GET', path, query: query);

  Future<dynamic> post(String path, [Object? body]) =>
      _send('POST', path, body: body);

  Future<dynamic> patch(String path, [Object? body]) =>
      _send('PATCH', path, body: body);

  Future<dynamic> delete(String path) => _send('DELETE', path);

  Future<dynamic> _send(
    String method,
    String path, {
    Map<String, String>? query,
    Object? body,
  }) async {
    final uri = Uri.parse(
      '$baseUrl$path',
    ).replace(queryParameters: query?.isEmpty ?? true ? null : query);
    final encodedBody = body == null ? null : jsonEncode(body);

    http.Response response;
    try {
      final request = switch (method) {
        'GET' => _http.get(uri, headers: _headers),
        'POST' => _http.post(uri, headers: _headers, body: encodedBody),
        'PATCH' => _http.patch(uri, headers: _headers, body: encodedBody),
        'DELETE' => _http.delete(uri, headers: _headers),
        _ => throw ArgumentError('Unsupported method $method'),
      };
      response = await request.timeout(const Duration(seconds: 15));
    } on TimeoutException {
      throw ApiException(0, 'The server took too long to respond.');
    } catch (_) {
      throw ApiException(
        0,
        'Cannot reach the server. Make sure the backend is running.',
      );
    }

    final decoded = _decode(response.body);

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw ApiException.fromResponse(response);
  }

  dynamic _decode(String body) {
    if (body.isEmpty) return null;
    try {
      return jsonDecode(body);
    } catch (_) {
      return body;
    }
  }
}
