import 'package:flutter/material.dart';
import 'cart_item.dart';
import 'order.dart';
import 'order_storage.dart';

class OrderProvider extends ChangeNotifier {
  OrderProvider({OrderStorage? storage})
    : _storage = storage ?? LocalOrderStorage();

  final OrderStorage _storage;
  final List<Order> _orders = [];
  bool _isLoading = true;

  List<Order> get orders => List.unmodifiable(_orders);
  bool get isLoading => _isLoading;

  Future<void> loadOrders() async {
    _isLoading = true;
    notifyListeners();
    try {
      _orders
        ..clear()
        ..addAll(await _storage.loadOrders());
    } catch (error, stackTrace) {
      debugPrint('Unable to load order history: $error\n$stackTrace');
      _orders.clear();
    } finally {
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<Order> createOrder(List<CartItem> cartItems) async {
    if (cartItems.isEmpty) {
      throw StateError('Cannot create an order from an empty cart.');
    }
    final now = DateTime.now();
    final order = Order(
      receiptId: _createReceiptId(now),
      dateTime: now,
      items: List.unmodifiable(cartItems),
      totalQuantity: cartItems.fold(0, (sum, item) => sum + item.quantity),
      totalPrice: cartItems.fold(0, (sum, item) => sum + item.totalPrice),
    );
    _orders.insert(0, order);
    try {
      await _storage.saveOrders(_orders);
    } catch (_) {
      _orders.remove(order);
      rethrow;
    }
    notifyListeners();
    return order;
  }

  String _createReceiptId(DateTime time) {
    String twoDigits(int value) => value.toString().padLeft(2, '0');
    final timestamp =
        '${time.year}${twoDigits(time.month)}${twoDigits(time.day)}'
        '${twoDigits(time.hour)}${twoDigits(time.minute)}${twoDigits(time.second)}'
        '${time.millisecond.toString().padLeft(3, '0')}'
        '${time.microsecond.toString().padLeft(3, '0')}';
    return 'ORD-$timestamp';
  }
}
