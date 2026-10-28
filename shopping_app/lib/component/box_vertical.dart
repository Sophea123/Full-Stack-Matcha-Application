import 'package:flutter/material.dart';

/// A card component showcasing a seasonal special menu item.
/// Displays a background image, badge, title, description, and an order button.
///
/// Usage:
/// ```dart
/// SeasonalSpecialCard(
///   badge: 'SEASONAL SPECIAL',
///   title: 'Pumpkin\nSpice\nLatte',
///   description:
///       'Warm spices, real pumpkin, and our signature espresso blend. '
///       'The perfect companion for crisp mornings.',
///   price: 5.50,
///   imageProvider: AssetImage('assets/images/pumpkin_spice_latte.png'),
///   onOrder: () => print('Order tapped'),
/// )
/// ```
class SeasonalSpecialCard extends StatelessWidget {
  /// Short uppercase label shown at the top of the card (e.g. 'SEASONAL SPECIAL').
  final String badge;

  /// Main product title. Use '\n' for manual line breaks if desired.
  final String title;

  /// Short body copy describing the product.
  final String description;

  /// Price in dollars shown on the order button.
  final double price;

  /// Background image for the card.
  final String imageUrl;

  /// Called when the order button is tapped.
  final VoidCallback? onOrder;

  /// Card width. Defaults to 320.
  final double width;

  /// Card height. Defaults to 460.
  final double height;
  const SeasonalSpecialCard({
    super.key,
    required this.badge,
    required this.title,
    required this.description,
    required this.price,
    required this.imageUrl,
    this.onOrder,
    this.width = double.infinity,
    this.height = 460,
  });

  // ── Design tokens ──────────────────────────────────────────────────────────
  static const _cardRadius = Radius.circular(24);
  static const _badgeBg = Color(0xFFD6E8C8); // soft sage green
  static const _badgeFg = Color(0xFF4A7C40); // deep forest green
  /*   static const _titleColor = Color(0xFF7B2B0A); // rich pumpkin-brown */
  static const _bodyColor = Color(0xFF2C1A0E); // dark espresso
  static const _buttonBg = Color(0xFF8B3318); // terracotta-rust
  static const _buttonFg = Colors.white;
  static const _overlayStart = Color(0xCCFDF6EC); // warm cream, more opaque
  static const _overlayEnd = Color(0x55FDF6EC); // fades toward the image

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      height: height,
      child: ClipRRect(
        borderRadius: const BorderRadius.all(_cardRadius),
        child: Stack(
          fit: StackFit.expand,
          children: [
            // ── Background photo ──────────────────────────────────────────
            Image.asset(
              'assets/images/$imageUrl',
              fit: BoxFit.cover,
              alignment: Alignment.centerRight,
              errorBuilder: (context, error, stackTrace) =>
                  const ColoredBox(color: Color(0xFFD6E8C8)),
            ),

            // ── Left-to-right gradient so text stays readable ─────────────
            const DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                  stops: [0.1, 0.0, 0.5],
                  colors: [_overlayStart, _overlayStart, _overlayEnd],
                ),
              ),
            ),

            // ── Content ───────────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badge
                  _Badge(label: badge),
                  const SizedBox(height: 14),

                  // Title
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 38,
                      fontWeight: FontWeight.w800,
                      color: Color.fromARGB(255, 9, 72, 5),
                      height: 1.05,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Description – constrained width so it doesn't bleed into photo
                  SizedBox(
                    width: (MediaQuery.sizeOf(context).width * 0.55).clamp(
                      150.0,
                      420.0,
                    ),
                    child: Text(
                      description,
                      maxLines: 4,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w400,
                        color: _bodyColor,
                        height: 1.5,
                      ),
                    ),
                  ),

                  const Spacer(),

                  // Order button
                  _OrderButton(price: price, onTap: onOrder),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Private sub-widgets ───────────────────────────────────────────────────────

class _Badge extends StatelessWidget {
  const _Badge({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: SeasonalSpecialCard._badgeBg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: SeasonalSpecialCard._badgeFg,
          letterSpacing: 1.2,
        ),
      ),
    );
  }
}

class _OrderButton extends StatelessWidget {
  const _OrderButton({required this.price, this.onTap});
  final double price;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final label = 'Order Now – \$${price.toStringAsFixed(2)}';

    return SizedBox(
      width: double.infinity,
      child: Material(
        color: SeasonalSpecialCard._buttonBg,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          splashColor: Colors.white24,
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 18),
            child: Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: SeasonalSpecialCard._buttonFg,
                letterSpacing: 0.2,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ── Demo entry-point (remove in production) ───────────────────────────────────

/* void main() => runApp(const _DemoApp());

class _DemoApp extends StatelessWidget { */
/*   const _DemoApp();

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Seasonal Special Demo',
      theme: ThemeData(fontFamily: 'Georgia'),
      home: Scaffold(
        backgroundColor: const Color(0xFFF5EFE6),
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  const Text(
                    'Ready for your usual?',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF2C1A0E),
                    ),
                  ),
                  const SizedBox(height: 24),
                  SeasonalSpecialCard(
                    badge: 'SEASONAL SPECIAL',
                    title: 'Pumpkin\nSpice\nLatte',
                    description:
                        'Warm spices, real pumpkin, and our signature espresso '
                        'blend. The perfect companion for crisp mornings.',
                    price: 5.50,
                    // Replace with your actual asset or network image:
                    imageProvider:
                        const NetworkImage('https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=600'),
                    onOrder: () {
                      debugPrint('Order tapped!');
                    },
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
} */
