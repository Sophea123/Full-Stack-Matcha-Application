import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shopping_app/model/auth_provider.dart';
import 'package:shopping_app/model/cart_provider.dart';
import 'package:shopping_app/model/product_provider.dart';
import 'package:shopping_app/pages/cart_page.dart';
import 'package:shopping_app/pages/history_page.dart';
import 'package:shopping_app/pages/home_page.dart';
import 'package:shopping_app/pages/menu_page.dart';

class BottomNavbar extends StatefulWidget {
  const BottomNavbar({super.key});

  @override
  State<BottomNavbar> createState() => _BottomNavbarState();
}

class _BottomNavbarState extends State<BottomNavbar> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) context.read<ProductProvider>().load();
    });
  }

  void _goTo(int index) => setState(() => _currentIndex = index);

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final cartCount = context.watch<CartProvider>().totalQuantity;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: () => _goTo(1),
          icon: const Icon(Icons.menu),
          tooltip: 'Menu',
        ),
        actions: [
          PopupMenuButton<String>(
            tooltip: 'Account',
            icon: const Icon(Icons.account_circle_outlined),
            onSelected: (value) async {
              if (value == 'signin') {
                context.read<AuthProvider>().leaveGuestMode();
              } else if (value == 'logout') {
                await context.read<AuthProvider>().logout();
              }
            },
            itemBuilder: (context) => [
              PopupMenuItem<String>(
                enabled: false,
                child: Text(
                  auth.isAuthenticated ? auth.displayName : 'Guest mode',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              const PopupMenuDivider(),
              if (auth.isAuthenticated)
                const PopupMenuItem<String>(
                  value: 'logout',
                  child: Text('Sign out'),
                )
              else
                const PopupMenuItem<String>(
                  value: 'signin',
                  child: Text('Sign in'),
                ),
            ],
          ),
        ],
        elevation: 4,
        backgroundColor: const Color.fromARGB(255, 0, 106, 48),
        foregroundColor: Colors.white,
        title: const Text(
          'Brewly',
          style: TextStyle(
            fontSize: 30,
            fontFamily: 'Kha',
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: [
        HomePage(onSeeMenu: () => _goTo(1)),
        const MenuPage(),
        CartPage(onOrderPlaced: () => _goTo(3)),
        HistoryPage(onStartOrdering: () => _goTo(1)),
      ][_currentIndex],
      bottomNavigationBar: NavigationBarTheme(
        data: NavigationBarThemeData(
          indicatorColor: Colors.transparent,
          overlayColor: WidgetStateProperty.all(Colors.transparent),
          labelTextStyle: WidgetStateProperty.all(
            const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
          ),
        ),
        child: NavigationBar(
          backgroundColor: Colors.transparent,
          selectedIndex: _currentIndex,
          onDestinationSelected: _goTo,
          destinations: [
            const NavigationDestination(
              icon: Icon(Icons.home_outlined, color: Colors.grey),
              selectedIcon: Icon(
                Icons.home,
                color: Color.fromARGB(255, 0, 106, 48),
              ),
              label: 'Home',
            ),
            const NavigationDestination(
              icon: Icon(Icons.menu_book_outlined, color: Colors.grey),
              selectedIcon: Icon(
                Icons.menu_book,
                color: Color.fromARGB(255, 0, 106, 48),
              ),
              label: 'Menu',
            ),
            NavigationDestination(
              icon: _CartIcon(
                icon: Icons.shopping_cart_checkout_outlined,
                count: cartCount,
              ),
              selectedIcon: _CartIcon(
                icon: Icons.shopping_cart_checkout_outlined,
                count: cartCount,
                selected: true,
              ),
              label: 'Cart',
            ),
            const NavigationDestination(
              icon: Icon(Icons.receipt_long_outlined, color: Colors.grey),
              selectedIcon: Icon(
                Icons.receipt_long,
                color: Color.fromARGB(255, 0, 106, 48),
              ),
              label: 'Orders',
            ),
          ],
        ),
      ),
    );
  }
}

class _CartIcon extends StatelessWidget {
  const _CartIcon({
    required this.icon,
    required this.count,
    this.selected = false,
  });

  final IconData icon;
  final int count;
  final bool selected;

  @override
  Widget build(BuildContext context) {
    return Badge(
      isLabelVisible: count > 0,
      label: Text('$count'),
      child: Icon(
        icon,
        color: selected ? const Color.fromARGB(255, 0, 106, 48) : Colors.grey,
      ),
    );
  }
}
