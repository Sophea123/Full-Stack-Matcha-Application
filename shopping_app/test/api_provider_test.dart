import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shopping_app/model/auth_provider.dart';
import 'package:shopping_app/model/product_provider.dart';
import 'package:shopping_app/services/api_client.dart';
import 'package:shopping_app/services/token_storage.dart';

class MemoryTokenStorage implements TokenStorage {
  MemoryTokenStorage([this.token]);

  String? token;

  @override
  Future<String?> read() async => token;

  @override
  Future<void> write(String value) async => token = value;

  @override
  Future<void> delete() async => token = null;
}

http.Response jsonResponse(Object body, [int status = 200]) => http.Response(
  jsonEncode(body),
  status,
  headers: {'content-type': 'application/json'},
);

ApiClient apiWith(MockClient client) =>
    ApiClient(httpClient: client, baseUrl: 'http://localhost:8000/api');

void main() {
  group('AuthProvider', () {
    test('login stores the token and exposes the user', () async {
      http.Request? captured;
      final storage = MemoryTokenStorage();
      final api = apiWith(
        MockClient((request) async {
          captured = request;
          return jsonResponse({
            'data': {
              'user': {
                'id': 1,
                'name': 'Test User',
                'email': 'test@example.com',
              },
              'token': '1|abc123',
            },
          });
        }),
      );

      final auth = AuthProvider(api, storage: storage);
      final success = await auth.login(
        email: 'Test@Example.com ',
        password: 'password123',
      );

      expect(success, isTrue);
      expect(auth.isAuthenticated, isTrue);
      expect(auth.displayName, 'Test User');
      expect(storage.token, '1|abc123');
      expect(api.token, '1|abc123');

      final body = jsonDecode(captured!.body) as Map<String, dynamic>;
      expect(body['email'], 'test@example.com');
      expect(body['password'], 'password123');
    });

    test(
      'login failure keeps the user signed out and shows the error',
      () async {
        final storage = MemoryTokenStorage();
        final api = apiWith(
          MockClient(
            (_) async =>
                jsonResponse({'message': 'Invalid email or password.'}, 401),
          ),
        );

        final auth = AuthProvider(api, storage: storage);
        final success = await auth.login(
          email: 'test@example.com',
          password: 'wrong-password',
        );

        expect(success, isFalse);
        expect(auth.isAuthenticated, isFalse);
        expect(auth.error, 'Invalid email or password.');
        expect(storage.token, isNull);
      },
    );

    test('register sends the confirmation field', () async {
      http.Request? captured;
      final api = apiWith(
        MockClient((request) async {
          captured = request;
          return jsonResponse({
            'data': {
              'user': {'id': 2, 'name': 'New User'},
              'token': '2|xyz',
            },
          }, 201);
        }),
      );

      final auth = AuthProvider(api, storage: MemoryTokenStorage());
      final success = await auth.register(
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        passwordConfirmation: 'password123',
      );

      expect(success, isTrue);
      final body = jsonDecode(captured!.body) as Map<String, dynamic>;
      expect(body['name'], 'New User');
      expect(body['password_confirmation'], 'password123');
    });

    test('restoreSession validates a saved token against /api/user', () async {
      final storage = MemoryTokenStorage('3|saved');
      final api = apiWith(
        MockClient((request) async {
          expect(request.headers['Authorization'], 'Bearer 3|saved');
          return jsonResponse({
            'data': {'id': 3, 'name': 'Saved User'},
          });
        }),
      );

      final auth = AuthProvider(api, storage: storage);
      await auth.restoreSession();

      expect(auth.isRestoring, isFalse);
      expect(auth.isAuthenticated, isTrue);
      expect(auth.displayName, 'Saved User');
    });

    test('restoreSession clears a rejected token', () async {
      final storage = MemoryTokenStorage('expired');
      final api = apiWith(
        MockClient(
          (_) async => jsonResponse({'message': 'Unauthenticated.'}, 401),
        ),
      );

      final auth = AuthProvider(api, storage: storage);
      await auth.restoreSession();

      expect(auth.isAuthenticated, isFalse);
      expect(storage.token, isNull);
      expect(api.token, isNull);
    });

    test('logout calls the API and clears local state', () async {
      final storage = MemoryTokenStorage();
      final api = apiWith(
        MockClient((request) async {
          if (request.url.path.endsWith('/login')) {
            return jsonResponse({
              'data': {
                'user': {'id': 1, 'name': 'Test User'},
                'token': '1|abc123',
              },
            });
          }
          return http.Response('', 204);
        }),
      );

      final auth = AuthProvider(api, storage: storage);
      await auth.login(email: 'test@example.com', password: 'password123');
      await auth.logout();

      expect(auth.isAuthenticated, isFalse);
      expect(storage.token, isNull);
      expect(api.token, isNull);
    });
  });

  group('ProductProvider', () {
    test('loads and filters the paginated catalog', () async {
      final api = apiWith(
        MockClient((request) async {
          expect(request.url.queryParameters['per_page'], '100');
          return jsonResponse({
            'data': [
              {
                'id': 1,
                'title': 'Strawberry Matcha',
                'description': 'Sweet strawberry matcha.',
                'image_url': 'strawberry_matcha.png',
                'price': '5.00',
                'category': 'Matcha',
              },
              {
                'id': 2,
                'title': 'Iced Latte',
                'description': 'Cold milk and espresso.',
                'image_url': 'iced_latte.png',
                'price': '3.50',
                'category': 'Coffee',
              },
            ],
            'meta': {'current_page': 1, 'per_page': 100, 'total': 2},
          });
        }),
      );

      final provider = ProductProvider(api);
      await provider.load();

      expect(provider.products, hasLength(2));
      expect(provider.categories, ['Coffee', 'Matcha']);
      expect(provider.filtered(category: 'Coffee').single.title, 'Iced Latte');
      expect(
        provider.filtered(search: 'straw').single.title,
        'Strawberry Matcha',
      );
      expect(provider.filtered(category: 'Dessert'), isEmpty);
    });

    test('surfaces a readable error when the server fails', () async {
      final api = apiWith(
        MockClient(
          (_) async => jsonResponse({'message': 'Server exploded.'}, 500),
        ),
      );

      final provider = ProductProvider(api);
      await provider.load();

      expect(provider.products, isEmpty);
      expect(provider.error, 'Server exploded.');
    });
  });
}
