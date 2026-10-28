import 'cart_item.dart';

class Order {
  const Order({
    required this.receiptId,
    required this.dateTime,
    required this.items,
    required this.totalQuantity,
    required this.totalPrice,
  });

  final String receiptId;
  final DateTime dateTime;
  final List<CartItem> items;
  final int totalQuantity;
  final double totalPrice;

  Map<String, dynamic> toJson() => {
    'receiptId': receiptId,
    'dateTime': dateTime.toIso8601String(),
    'items': items.map((item) => item.toJson()).toList(),
    'totalQuantity': totalQuantity,
    'totalPrice': totalPrice,
  };

  factory Order.fromJson(Map<String, dynamic> json) => Order(
    receiptId: json['receiptId'] as String,
    dateTime: DateTime.parse(json['dateTime'] as String).toLocal(),
    items: (json['items'] as List)
        .map(
          (item) => CartItem.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList(),
    totalQuantity: json['totalQuantity'] as int,
    totalPrice: (json['totalPrice'] as num).toDouble(),
  );
}
