import 'package:flutter_test/flutter_test.dart';
import 'package:shopping_app/model/cart_item.dart';
import 'package:shopping_app/model/cart_provider.dart';
import 'package:shopping_app/model/order.dart';
import 'package:shopping_app/model/order_provider.dart';
import 'package:shopping_app/model/order_storage.dart';

CartItem matcha({required String size, int quantity = 1}) => CartItem(
  productId: 'matcha-1',
  productName: 'Matcha Latte',
  productImage: 'image.png',
  description: 'Matcha',
  category: 'Matcha',
  availableSizes: const ['Small', 'Large'],
  unitPrice: 4,
  size: size,
  sugarLevel: 50,
  iceLevel: 'Less Ice',
  quantity: quantity,
);

class MemoryOrderStorage implements OrderStorage {
  List<Order> saved = [];
  @override
  Future<List<Order>> loadOrders() async => saved;
  @override
  Future<void> saveOrders(List<Order> orders) async => saved = List.of(orders);
}

void main() {
  test('only identical configurations share quantity', () {
    final cart = CartProvider();
    cart.addToCart(matcha(size: 'Small'));
    cart.addToCart(matcha(size: 'Small'));
    cart.addToCart(matcha(size: 'Large'));
    cart.addToCart(matcha(size: 'Small'));

    expect(cart.items, hasLength(2));
    expect(cart.items[0].quantity, 3);
    expect(cart.totalQuantity, 4);
  });

  test('order copies configuration, saves it, and calculates totals', () async {
    final storage = MemoryOrderStorage();
    final provider = OrderProvider(storage: storage);
    final order = await provider.createOrder([
      matcha(size: 'Small', quantity: 2),
      matcha(size: 'Large'),
    ]);

    expect(order.receiptId, startsWith('ORD-'));
    expect(order.items, hasLength(2));
    expect(order.totalQuantity, 3);
    expect(order.totalPrice, 12);
    expect(storage.saved.single.receiptId, order.receiptId);
  });
}
