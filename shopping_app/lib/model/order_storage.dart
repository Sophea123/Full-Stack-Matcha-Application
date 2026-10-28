import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';
import 'order.dart';

/// Replace this implementation with Firestore later without changing the UI.
abstract class OrderStorage {
  Future<List<Order>> loadOrders();
  Future<void> saveOrders(List<Order> orders);
}

class LocalOrderStorage implements OrderStorage {
  // Versioned key prevents old cart-format data from breaking order loading.
  static const _ordersKey = 'orders_v2';

  @override
  Future<List<Order>> loadOrders() async {
    final preferences = await SharedPreferences.getInstance();
    final encodedOrders = preferences.getString(_ordersKey);
    if (encodedOrders == null || encodedOrders.isEmpty) return [];
    final decoded = jsonDecode(encodedOrders) as List;
    return decoded
        .map((value) => Order.fromJson(Map<String, dynamic>.from(value as Map)))
        .toList();
  }

  @override
  Future<void> saveOrders(List<Order> orders) async {
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString(
      _ordersKey,
      jsonEncode(orders.map((order) => order.toJson()).toList()),
    );
  }
}
