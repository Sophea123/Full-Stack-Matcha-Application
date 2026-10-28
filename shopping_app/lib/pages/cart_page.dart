import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/component/product_image.dart';
import 'package:shopping_app/model/cart_provider.dart';
import 'package:shopping_app/model/cart_item.dart';
import 'package:shopping_app/model/order_provider.dart';

class CartPage extends StatelessWidget {
  const CartPage({super.key, this.onOrderPlaced});

  final VoidCallback? onOrderPlaced;

  Future<void> _placeOrder(BuildContext context) async {
    final cart = context.read<CartProvider>();
    final orderProvider = context.read<OrderProvider>();
    try {
      final order = await orderProvider.createOrder(cart.items);
      cart.clearCart();
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Order ${order.receiptId} placed successfully')),
      );
      onOrderPlaced?.call();
    } catch (error, stackTrace) {
      debugPrint('Unable to save order: $error\n$stackTrace');
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Could not save your order. Please try again.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    return Scaffold(
      backgroundColor: const Color.fromARGB(255, 248, 243, 232),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'MY CART',
                    style: TextStyle(fontSize: 40, fontWeight: FontWeight.bold),
                  ),
                  if (cart.items.isNotEmpty)
                    Text(
                      '${cart.totalQuantity} items\n\$${cart.total.toStringAsFixed(2)}',
                      textAlign: TextAlign.right,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                ],
              ),
            ),
            Expanded(
              child: cart.items.isEmpty
                  ? const Center(child: Text('Your cart is empty.'))
                  : ListView.builder(
                      itemCount: cart.items.length,
                      itemBuilder: (context, index) {
                        final item = cart.items[index];
                        return Card(
                          margin: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Row(
                              children: [
                                ClipOval(
                                  child: ProductImage(
                                    path: item.productImage,
                                    width: 56,
                                    height: 56,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        item.productName,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: Theme.of(
                                          context,
                                        ).textTheme.bodySmall,
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        _configurationText(item),
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 4),
                                SizedBox(
                                  width: 100,
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        '\$${item.totalPrice.toStringAsFixed(2)}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        children: [
                                          _QuantityButton(
                                            icon: Icons.remove,
                                            onPressed: () => context
                                                .read<CartProvider>()
                                                .decreaseQuantity(index),
                                          ),
                                          Padding(
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 4,
                                            ),
                                            child: Text(
                                              '${item.quantity}',
                                              style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                          _QuantityButton(
                                            icon: Icons.add,
                                            onPressed: () => context
                                                .read<CartProvider>()
                                                .increaseQuantity(index),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
            if (cart.items.isNotEmpty)
              Padding(
                padding: const EdgeInsets.all(16),
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => _placeOrder(context),
                    child: const Text('PLACE ORDER'),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  String _configurationText(CartItem item) {
    final lines = ['${item.size} × ${item.quantity}'];
    if (!item.isDessert) {
      lines.add('${item.sugarLevel!.round()}% sugar • ${item.iceLevel}');
    }

    return lines.join('\n');
  }
}

class _QuantityButton extends StatelessWidget {
  const _QuantityButton({required this.icon, required this.onPressed});
  final IconData icon;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) => IconButton(
    onPressed: onPressed,
    icon: Icon(icon, size: 18),
    padding: EdgeInsets.zero,
    constraints: const BoxConstraints(minWidth: 22, minHeight: 28),
    visualDensity: VisualDensity.compact,
  );
}
