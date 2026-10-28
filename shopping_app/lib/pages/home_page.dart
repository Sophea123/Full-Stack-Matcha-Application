import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/component/box_card_menu.dart';
import 'package:shopping_app/component/box_vertical.dart';
import 'package:shopping_app/component/list_view_container.dart';
import 'package:shopping_app/model/product_provider.dart';
import 'package:shopping_app/pages/detail.dart';
import 'package:shopping_app/pages/menu_page.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key, this.onSeeMenu});

  final VoidCallback? onSeeMenu;

  void _openMenu(BuildContext context) {
    if (onSeeMenu != null) {
      onSeeMenu!();
      return;
    }
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const MenuPage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color.fromARGB(255, 246, 234, 210),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(12),
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 8),
              const Text(
                'Welcome to Brewly!',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 3),
              const Text(
                'Discover our products ❤️',
                style: TextStyle(fontSize: 17),
              ),
              const SizedBox(height: 10),
              const MatchaCardwide(
                title: 'Matcha Latte',
                description: 'A creamy blend of matcha and milk.',
                badge: 'Best Seller',
                price: '\$4.99',
                imageUrl: 'matcha1.jpeg',
              ),
              const SizedBox(height: 15),
              SeasonalSpecialCard(
                badge: 'SEASONAL SPECIAL',
                title: 'Pumpkin\nSpice\nLatte',
                description:
                    'A warm and cozy blend of pumpkin, spices, and espresso.',
                price: 5.50,
                imageUrl: 'matcha1.jpeg',
                onOrder: () => _openMenu(context),
              ),
              const SizedBox(height: 20),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Expanded(
                    child: Text(
                      'Best Sellers this season',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  TextButton(
                    onPressed: () => _openMenu(context),
                    child: const Text(
                      'See all',
                      style: TextStyle(
                        color: Color.fromARGB(255, 0, 106, 48),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              SizedBox(height: 290, child: const _BestSellers()),
              const SizedBox(height: 10),
            ],
          ),
        ),
      ),
    );
  }
}

class _BestSellers extends StatelessWidget {
  const _BestSellers();

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ProductProvider>();

    if (provider.isLoading && provider.products.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (provider.error != null && provider.products.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(provider.error!, textAlign: TextAlign.center),
            const SizedBox(height: 8),
            OutlinedButton(
              onPressed: () =>
                  context.read<ProductProvider>().load(refresh: true),
              child: const Text('Try again'),
            ),
          ],
        ),
      );
    }

    if (provider.products.isEmpty) {
      return const Center(child: Text('The menu is empty right now.'));
    }

    final products = provider.products.take(6).toList();

    return ListView.separated(
      scrollDirection: Axis.horizontal,
      itemCount: products.length,
      separatorBuilder: (_, _) => const SizedBox(width: 10),
      itemBuilder: (context, index) {
        final product = products[index];
        return MenuItemCard(
          product: product,
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => Detailproductpage(product: product),
            ),
          ),
        );
      },
    );
  }
}
