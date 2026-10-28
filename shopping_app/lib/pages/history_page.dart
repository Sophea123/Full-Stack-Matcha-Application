import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/component/product_image.dart';
import 'package:shopping_app/model/cart_item.dart';
import 'package:shopping_app/model/order.dart';
import 'package:shopping_app/model/order_provider.dart';

class HistoryPage extends StatelessWidget {
  const HistoryPage({super.key, this.onStartOrdering});

  final VoidCallback? onStartOrdering;

  @override
  Widget build(BuildContext context) {
    final orders = context.watch<OrderProvider>();
    return Scaffold(
      backgroundColor: const Color.fromARGB(255, 248, 243, 232),
      body: SafeArea(
        child: orders.isLoading
            ? const Center(child: CircularProgressIndicator())
            : orders.orders.isEmpty
            ? _EmptyHistory(onStartOrdering: onStartOrdering)
            : ListView(
                padding: const EdgeInsets.all(12),
                children: [
                  const Text(
                    'ORDER HISTORY',
                    style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  ...orders.orders.map((order) => _OrderCard(order: order)),
                ],
              ),
      ),
    );
  }
}

class _EmptyHistory extends StatelessWidget {
  const _EmptyHistory({this.onStartOrdering});
  final VoidCallback? onStartOrdering;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.receipt_long_outlined, size: 64),
          const SizedBox(height: 16),
          const Text(
            'No orders yet',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          const Text(
            'Start ordering your favorite matcha!',
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 18),
          ElevatedButton(
            onPressed: onStartOrdering,
            child: const Text('START ORDERING'),
          ),
        ],
      ),
    ),
  );
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});
  final Order order;

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ExpansionTile(
      title: Text(
        'Receipt #${order.receiptId}',
        style: const TextStyle(fontWeight: FontWeight.bold),
      ),
      subtitle: Text(
        '${_formatDate(order.dateTime)} • ${_formatTime(order.dateTime)}\n${order.totalQuantity} items     \$${order.totalPrice.toStringAsFixed(2)}',
      ),
      childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      children: [
        const Divider(),
        ...order.items.map(_OrderItemTile.new),
        const Divider(),
        Align(
          alignment: Alignment.centerRight,
          child: Text(
            'Total Items: ${order.totalQuantity}\nTotal: \$${order.totalPrice.toStringAsFixed(2)}',
            textAlign: TextAlign.right,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ),
      ],
    ),
  );
}

class _OrderItemTile extends StatelessWidget {
  const _OrderItemTile(this.item);
  final CartItem item;

  @override
  Widget build(BuildContext context) {
    final details = <String>['${item.size} × ${item.quantity}'];
    if (!item.isDessert) {
      details.add('${item.sugarLevel!.round()}% Sugar • ${item.iceLevel}');
    }

    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: ClipOval(
        child: ProductImage(path: item.productImage, width: 40, height: 40),
      ),
      title: Text('${item.productName} × ${item.quantity}'),
      subtitle: Text(details.join('\n')),
      trailing: Text('\$${item.totalPrice.toStringAsFixed(2)}'),
    );
  }
}

String _formatDate(DateTime date) {
  const months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ];
  return '${months[date.month - 1]} ${date.day}, ${date.year}';
}

String _formatTime(DateTime time) {
  final hour = time.hour % 12 == 0 ? 12 : time.hour % 12;
  return '$hour:${time.minute.toString().padLeft(2, '0')} ${time.hour >= 12 ? 'PM' : 'AM'}';
}
