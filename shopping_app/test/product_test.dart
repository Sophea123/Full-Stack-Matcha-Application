import 'package:flutter_test/flutter_test.dart';
import 'package:shopping_app/model/product.dart';

void main() {
  test('parses the Laravel product contract with a string price', () {
    final product = Product.fromJson({
      'id': 7,
      'title': 'Strawberry Matcha',
      'description': 'Matcha with strawberry.',
      'image_url': 'strawberry_matcha.png',
      'price': '5.50',
      'category': 'Matcha',
    });

    expect(product.id, 7);
    expect(product.price, 5.5);
    expect(product.formattedPrice, r'$5.50');
    expect(product.imagePath, 'assets/images/strawberry_matcha.png');
    expect(product.isDessert, isFalse);
    expect(product.availableSizes, ['Small', 'Medium', 'Venti']);
  });

  test('accepts numeric prices and absolute image urls', () {
    final product = Product.fromJson({
      'id': '8',
      'title': 'Cake',
      'image_url': 'https://cdn.example.com/cake.png',
      'price': 6,
      'category': 'Dessert',
    });

    expect(product.id, 8);
    expect(product.price, 6);
    expect(product.imagePath, 'https://cdn.example.com/cake.png');
    expect(product.isNetworkImage, isTrue);
    expect(product.isDessert, isTrue);
    expect(product.availableSizes, ['Whole Cake', 'Custom Pieces']);
  });

  test('falls back when optional fields are missing', () {
    final product = Product.fromJson({'id': 9, 'title': 'Mystery'});

    expect(product.description, isEmpty);
    expect(product.imagePath, 'assets/images/matcha1.jpeg');
    expect(product.price, 0);
    expect(product.category, 'Other');
  });
}
