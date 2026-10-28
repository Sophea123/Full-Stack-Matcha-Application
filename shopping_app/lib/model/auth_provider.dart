import 'package:flutter/foundation.dart';

import '../services/api_client.dart';
import '../services/token_storage.dart';

/// Holds the signed-in user and the Sanctum token.
class AuthProvider extends ChangeNotifier {
  AuthProvider(this._api, {TokenStorage? storage})
    : _storage = storage ?? SecureTokenStorage();

  final ApiClient _api;
  final TokenStorage _storage;

  bool _isRestoring = true;
  bool _isSubmitting = false;
  bool _isGuest = false;
  String? _token;
  Map<String, dynamic>? _user;
  String? _error;
  Map<String, List<String>> _validationErrors = const {};

  bool get isRestoring => _isRestoring;
  bool get isSubmitting => _isSubmitting;
  bool get isAuthenticated => _token != null;
  bool get isGuest => _isGuest;
  bool get canUseApp => isAuthenticated || _isGuest;

  Map<String, dynamic>? get user => _user;
  String get displayName {
    final name = _user?['name'];
    return name is String && name.trim().isNotEmpty ? name.trim() : 'Guest';
  }

  String? get error => _error;
  Map<String, List<String>> get validationErrors => _validationErrors;
  String? errorFor(String field) => _validationErrors[field]?.first;

  /// Restores a previously saved token and verifies it against `/api/user`.
  Future<void> restoreSession() async {
    try {
      final stored = await _storage.read();
      if (stored == null || stored.isEmpty) return;

      _api.token = stored;
      final response = await _api.get('/user');
      _token = stored;
      _user = _userFrom(response);
      notifyListeners();
    } on ApiException catch (exception) {
      if (exception.isUnauthorized) {
        await _clearSession();
      } else if (exception.isNetworkError) {
        // Keep the saved token so the user can retry once the server is back.
        _token = _api.token;
      }
    } catch (_) {
      // Secure storage can be unavailable; fall back to the sign-in screen.
    } finally {
      _isRestoring = false;
      notifyListeners();
    }
  }

  Future<bool> login({required String email, required String password}) async {
    return _submit(() async {
      final response = await _api.post('/login', {
        'email': email.trim().toLowerCase(),
        'password': password,
      });
      await _applyAuth(response);
    });
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    return _submit(() async {
      final response = await _api.post('/register', {
        'name': name.trim(),
        'email': email.trim().toLowerCase(),
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
      await _applyAuth(response);
    });
  }

  Future<bool> _submit(Future<void> Function() action) async {
    _isSubmitting = true;
    _error = null;
    _validationErrors = const {};
    notifyListeners();

    try {
      await action();
      _isGuest = false;
      return true;
    } on ApiException catch (exception) {
      _error = exception.message;
      _validationErrors = exception.errors;
      return false;
    } catch (_) {
      _error = 'Something went wrong. Please try again.';
      return false;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  Future<void> _applyAuth(dynamic response) async {
    final data = response is Map ? response['data'] : null;
    if (data is! Map) {
      throw ApiException(0, 'The server returned an unexpected response.');
    }

    final token = data['token'];
    if (token is! String || token.isEmpty) {
      throw ApiException(0, 'The server did not return a token.');
    }

    _token = token;
    _user = data['user'] is Map
        ? Map<String, dynamic>.from(data['user'] as Map)
        : null;
    _api.token = token;
    await _storage.write(token);
  }

  Map<String, dynamic>? _userFrom(dynamic response) {
    final data = response is Map ? response['data'] : null;
    return data is Map ? Map<String, dynamic>.from(data) : null;
  }

  Future<void> logout() async {
    if (_token != null) {
      try {
        await _api.post('/logout');
      } on ApiException catch (exception) {
        debugPrint('Logout request failed: ${exception.message}');
      }
    }

    await _clearSession();
    _isGuest = false;
    notifyListeners();
  }

  void continueAsGuest() {
    _isGuest = true;
    _error = null;
    notifyListeners();
  }

  void leaveGuestMode() {
    _isGuest = false;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    _validationErrors = const {};
    notifyListeners();
  }

  Future<void> _clearSession() async {
    _token = null;
    _user = null;
    _api.token = null;
    try {
      await _storage.delete();
    } catch (_) {
      // Nothing else to do if secure storage is unavailable.
    }
  }
}
