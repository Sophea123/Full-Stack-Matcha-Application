import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/component/product_cart.dart';
import 'package:shopping_app/model/product.dart';
import 'package:shopping_app/model/product_provider.dart';

class MenuPage extends StatefulWidget {
  const MenuPage({super.key});

  @override
  State<MenuPage> createState() => _MenuPageState();
}

class _MenuPageState extends State<MenuPage> {
  static const _allCategory = 'All';

  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = _allCategory;
  String _search = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ProductProvider>();
    final categories = [_allCategory, ...provider.categories];
    final selected = categories.contains(_selectedCategory)
        ? _selectedCategory
        : _allCategory;
    final products = provider.filtered(category: selected, search: _search);

    return Scaffold(
      backgroundColor: const Color.fromARGB(255, 248, 243, 232),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Text(
                'Our Menu',
                style: TextStyle(fontSize: 36, fontWeight: FontWeight.bold),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: TextField(
                controller: _searchController,
                textInputAction: TextInputAction.search,
                onChanged: (value) => setState(() => _search = value),
                decoration: InputDecoration(
                  hintText: 'Search matcha, coffee, cake...',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _search.isEmpty
                      ? null
                      : IconButton(
                          tooltip: 'Clear search',
                          icon: const Icon(Icons.close),
                          onPressed: () {
                            _searchController.clear();
                            setState(() => _search = '');
                          },
                        ),
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
            ),
            SizedBox(
              height: 64,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 8),
                itemCount: categories.length,
                itemBuilder: (context, index) {
                  final category = categories[index];
                  final isSelected = selected == category;
                  return Padding(
                    padding: const EdgeInsets.all(8),
                    child: ChoiceChip(
                      showCheckmark: false,
                      side: BorderSide.none,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                      selected: isSelected,
                      selectedColor: Theme.of(context).colorScheme.primary,
                      backgroundColor: const Color.fromARGB(255, 224, 221, 214),
                      label: Text(
                        category,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: isSelected ? Colors.white : Colors.black,
                        ),
                      ),
                      onSelected: (_) =>
                          setState(() => _selectedCategory = category),
                    ),
                  );
                },
              ),
            ),
            Expanded(
              child: _MenuBody(
                provider: provider,
                products: products,
                search: _search,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MenuBody extends StatelessWidget {
  const _MenuBody({
    required this.provider,
    required this.products,
    required this.search,
  });

  final ProductProvider provider;
  final List<Product> products;
  final String search;

  @override
  Widget build(BuildContext context) {
    if (provider.isLoading && provider.products.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (provider.error != null && provider.products.isEmpty) {
      return _Message(
        icon: Icons.cloud_off_outlined,
        title: 'Could not load the menu',
        message: provider.error!,
        actionLabel: 'Try again',
        onAction: () => context.read<ProductProvider>().load(refresh: true),
      );
    }

    if (products.isEmpty) {
      return _Message(
        icon: Icons.search_off,
        title: 'Nothing matches',
        message: search.trim().isEmpty
            ? 'There are no items in this category yet.'
            : 'Try a different search or category.',
      );
    }

    return RefreshIndicator(
      onRefresh: () => context.read<ProductProvider>().load(refresh: true),
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(12, 4, 12, 90),
        itemCount: products.length,
        separatorBuilder: (_, _) => const SizedBox(height: 10),
        itemBuilder: (context, index) => ProductCart(product: products[index]),
      ),
    );
  }
}

class _Message extends StatelessWidget {
  const _Message({
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 56, color: Colors.black38),
            const SizedBox(height: 12),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(message, textAlign: TextAlign.center),
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(height: 16),
              ElevatedButton(onPressed: onAction, child: Text(actionLabel!)),
            ],
          ],
        ),
      ),
    );
  }
}
