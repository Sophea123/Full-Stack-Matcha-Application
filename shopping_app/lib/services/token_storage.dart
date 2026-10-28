import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Where the Sanctum token is persisted.
abstract class TokenStorage {
  Future<String?> read();
  Future<void> write(String token);
  Future<void> delete();
}

class SecureTokenStorage implements TokenStorage {
  SecureTokenStorage({FlutterSecureStorage? storage})
    : _storage = storage ?? _defaultStorage();

  static const _key = 'brewly_auth_token';

  final FlutterSecureStorage _storage;

  /// macOS uses the legacy Keychain unless the app has a provisioning profile
  /// with Keychain Sharing enabled.
  static FlutterSecureStorage _defaultStorage() {
    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.macOS) {
      return const FlutterSecureStorage(
        mOptions: MacOsOptions(usesDataProtectionKeychain: false),
      );
    }
    return const FlutterSecureStorage();
  }

  @override
  Future<String?> read() => _storage.read(key: _key);

  @override
  Future<void> write(String token) => _storage.write(key: _key, value: token);

  @override
  Future<void> delete() => _storage.delete(key: _key);
}
