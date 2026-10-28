import 'package:flutter/material.dart';
import 'cart_item.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);
  int get totalQuantity => _items.fold(0, (sum, item) => sum + item.quantity);
  double get total => _items.fold(0, (sum, item) => sum + item.totalPrice);

  void addToCart(CartItem item) {
    final index = _items.indexWhere(item.hasSameConfiguration);
    if (index == -1) {
      _items.add(item);
    } else {
      _items[index] = _items[index].copyWith(
        quantity: _items[index].quantity + item.quantity,
      );
    }
    notifyListeners();
  }

  void updateCartItem(int index, CartItem updatedItem) {
    _items.removeAt(index);
    final matchingIndex = _items.indexWhere(updatedItem.hasSameConfiguration);
    if (matchingIndex == -1) {
      _items.insert(index.clamp(0, _items.length), updatedItem);
    } else {
      _items[matchingIndex] = _items[matchingIndex].copyWith(
        quantity: _items[matchingIndex].quantity + updatedItem.quantity,
      );
    }
    notifyListeners();
  }

  void increaseQuantity(int index) {
    _items[index] = _items[index].copyWith(
      quantity: _items[index].quantity + 1,
    );
    notifyListeners();
  }

  void decreaseQuantity(int index) {
    if (_items[index].quantity == 1) {
      _items.removeAt(index);
    } else {
      _items[index] = _items[index].copyWith(
        quantity: _items[index].quantity - 1,
      );
    }
    notifyListeners();
  }

  void removeFromCart(int index) {
    _items.removeAt(index);
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    notifyListeners();
  }
}
