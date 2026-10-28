/// A catalog item returned by the Laravel API.
class Product {
  const Product({
    required this.id,
    required this.title,
    required this.description,
    required this.imageUrl,
    required this.price,
    required this.category,
  });

  final int id;
  final String title;
  final String description;
  final String imageUrl;
  final double price;
  final String category;

  factory Product.fromJson(Map<String, dynamic> json) => Product(
    id: _parseId(json['id']),
    title: _parseText(json['title'], fallback: 'Untitled'),
    description: _parseText(json['description']),
    imageUrl: _parseText(json['image_url']),
    price: _parsePrice(json['price']),
    category: _parseText(json['category'], fallback: 'Other'),
  );

  static int _parseId(dynamic value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  /// Laravel serializes `decimal` columns as strings (`"4.50"`), while the
  /// client may also send a number. Handle both.
  static double _parsePrice(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  static String _parseText(dynamic value, {String fallback = ''}) {
    final text = value?.toString().trim() ?? '';
    return text.isEmpty ? fallback : text;
  }

  bool get isDessert => category.toLowerCase() == 'dessert';

  List<String> get availableSizes => isDessert
      ? const ['Whole Cake', 'Custom Pieces']
      : const ['Small', 'Medium', 'Venti'];

  /// Products from the API store a file name such as `regular_matcha.png`.
  /// Bundled assets live under `assets/images/`, and absolute URLs are kept.
  String get imagePath {
    if (imageUrl.isEmpty) return 'assets/images/matcha1.jpeg';
    if (imageUrl.startsWith('assets/') || imageUrl.startsWith('http')) {
      return imageUrl;
    }
    return 'assets/images/$imageUrl';
  }

  bool get isNetworkImage => imagePath.startsWith('http');

  String get formattedPrice => '\$${price.toStringAsFixed(2)}';
}
