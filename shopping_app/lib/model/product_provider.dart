import 'package:flutter/foundation.dart';

import '../services/api_client.dart';
import 'product.dart';

/// Loads the catalog from `GET /api/products` and exposes it to the UI.
class ProductProvider extends ChangeNotifier {
  ProductProvider(this._api);

  final ApiClient _api;

  List<Product> _products = const [];
  bool _isLoading = false;
  bool _hasLoaded = false;
  String? _error;

  List<Product> get products => List.unmodifiable(_products);
  bool get isLoading => _isLoading;
  bool get hasLoaded => _hasLoaded;
  String? get error => _error;

  List<String> get categories {
    final values = <String>{};
    for (final product in _products) {
      if (product.category.isNotEmpty) values.add(product.category);
    }
    final sorted = values.toList()
      ..sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return sorted;
  }

  Future<void> load({bool refresh = false}) async {
    if (_isLoading) return;
    if (_hasLoaded && !refresh) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get('/products', query: {'per_page': '100'});
      final data = response is Map ? response['data'] : null;
      final items = data is List ? data : const [];
      _products = items
          .whereType<Map>()
          .map((item) => Product.fromJson(Map<String, dynamic>.from(item)))
          .toList();
      _hasLoaded = true;
    } on ApiException catch (exception) {
      _error = exception.message;
    } catch (_) {
      _error = 'Could not load the menu. Please try again.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  List<Product> filtered({String? category, String? search}) {
    final query = search?.trim().toLowerCase() ?? '';
    return _products.where((product) {
      final matchesCategory =
          category == null ||
          category == 'All' ||
          product.category.toLowerCase() == category.toLowerCase();
      final matchesSearch =
          query.isEmpty ||
          product.title.toLowerCase().contains(query) ||
          product.description.toLowerCase().contains(query);
      return matchesCategory && matchesSearch;
    }).toList();
  }
}
