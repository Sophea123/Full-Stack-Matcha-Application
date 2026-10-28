import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/component/product_image.dart';
import 'package:shopping_app/model/cart_item.dart';
import 'package:shopping_app/model/cart_provider.dart';
import 'package:shopping_app/model/product.dart';

class Detailproductpage extends StatefulWidget {
  const Detailproductpage({super.key, required this.product, this.cartIndex});

  final Product product;
  final int? cartIndex;

  @override
  State<Detailproductpage> createState() => _DetailproductpageState();
}

class _DetailproductpageState extends State<Detailproductpage> {
  late CartItem _item;

  bool get _isEditing => widget.cartIndex != null;

  @override
  void initState() {
    super.initState();
    _item = CartItem.fromProduct(widget.product);
  }

  void _save() {
    final item = _item;
    final cart = context.read<CartProvider>();
    if (_isEditing) {
      cart.updateCartItem(widget.cartIndex!, item);
      Navigator.pop(context);
    } else {
      cart.addToCart(item);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Added to cart')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Customize order' : 'Item details'),
        backgroundColor: const Color.fromARGB(255, 0, 106, 48),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: ProductImage(
                path: _item.productImage,
                height: 220,
                fit: BoxFit.contain,
                borderRadius: BorderRadius.circular(16),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              _item.productName,
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            Text(
              '\$${_item.unitPrice.toStringAsFixed(2)}',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(_item.description),
            const SizedBox(height: 20),
            const Text(
              'Size',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
            ),
            Wrap(
              spacing: 8,
              children: _item.availableSizes
                  .map(
                    (size) => ChoiceChip(
                      label: Text(size),
                      selected: _item.size == size,
                      onSelected: (_) =>
                          setState(() => _item = _item.copyWith(size: size)),
                    ),
                  )
                  .toList(),
            ),
            if (!_item.isDessert) ...[
              const SizedBox(height: 20),
              Text(
                'Sugar level: ${_item.sugarLevel!.round()}%',
                style: const TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Slider(
                value: _item.sugarLevel!,
                min: 0,
                max: 100,
                divisions: 4,
                label: '${_item.sugarLevel!.round()}%',
                onChanged: (value) =>
                    setState(() => _item = _item.copyWith(sugarLevel: value)),
              ),
              const Text(
                'Ice level',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
              Wrap(
                spacing: 8,
                children: ['No Ice', 'Less Ice', 'Normal Ice', 'More Ice']
                    .map(
                      (ice) => ChoiceChip(
                        label: Text(ice),
                        selected: _item.iceLevel == ice,
                        onSelected: (_) => setState(
                          () => _item = _item.copyWith(iceLevel: ice),
                        ),
                      ),
                    )
                    .toList(),
              ),
            ],
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _save,
                child: Text(_isEditing ? 'SAVE CHANGES' : 'ADD TO CART'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
