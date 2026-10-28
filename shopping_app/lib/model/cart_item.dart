import 'product.dart';

class CartItem {
  const CartItem({
    required this.productId,
    required this.productName,
    required this.productImage,
    required this.description,
    required this.category,
    required this.availableSizes,
    required this.unitPrice,
    required this.size,

    this.sugarLevel,
    this.iceLevel,
    this.quantity = 1,
  });

  final String productId;
  final String productName;
  final String productImage;
  final String description;
  final String category;
  final List<String> availableSizes;
  final double unitPrice;
  final String size;
  final double? sugarLevel;
  final String? iceLevel;

  final int quantity;

  factory CartItem.fromProduct(Product product) => CartItem(
    productId: product.id.toString(),
    productName: product.title,
    productImage: product.imagePath,
    description: product.description.isEmpty
        ? 'Freshly made just for you.'
        : product.description,
    category: product.category,
    availableSizes: product.availableSizes,
    unitPrice: product.price,
    size: product.availableSizes.first,
    sugarLevel: product.isDessert ? null : 50,
    iceLevel: product.isDessert ? null : 'Normal Ice',
  );

  bool get isDessert => category.toLowerCase() == 'dessert';
  double get totalPrice => unitPrice * quantity;

  /// Products with the same configuration share one quantity control.
  bool hasSameConfiguration(CartItem other) =>
      productId == other.productId &&
      size == other.size &&
      sugarLevel == other.sugarLevel &&
      iceLevel == other.iceLevel;
  CartItem copyWith({
    String? size,
    double? sugarLevel,
    String? iceLevel,
    int? quantity,
  }) => CartItem(
    productId: productId,
    productName: productName,
    productImage: productImage,
    description: description,
    category: category,
    availableSizes: availableSizes,
    unitPrice: unitPrice,
    size: size ?? this.size,
    sugarLevel: isDessert ? null : (sugarLevel ?? this.sugarLevel),
    iceLevel: isDessert ? null : (iceLevel ?? this.iceLevel),

    quantity: quantity ?? this.quantity,
  );

  Map<String, dynamic> toJson() => {
    'productId': productId,
    'productName': productName,
    'productImage': productImage,
    'description': description,
    'category': category,
    'availableSizes': availableSizes,
    'unitPrice': unitPrice,
    'size': size,
    'sugarLevel': sugarLevel,
    'iceLevel': iceLevel,

    'quantity': quantity,
  };

  factory CartItem.fromJson(Map<String, dynamic> json) => CartItem(
    productId: json['productId'] as String,
    productName: json['productName'] as String,
    productImage: json['productImage'] as String,
    description: json['description'] as String,
    category: json['category'] as String,
    availableSizes: List<String>.from(json['availableSizes'] as List),
    unitPrice: (json['unitPrice'] as num).toDouble(),
    size: json['size'] as String,
    sugarLevel: (json['sugarLevel'] as num?)?.toDouble(),
    iceLevel: json['iceLevel'] as String?,

    quantity: json['quantity'] as int? ?? 1,
  );
}
