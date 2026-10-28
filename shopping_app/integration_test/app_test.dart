import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:shopping_app/main.dart' as app;

/// Runs the real app against the locally running Laravel API:
///   php artisan serve --port=8000
///   flutter test integration_test/app_test.dart -d macos
void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  Future<void> waitFor(WidgetTester tester, Finder finder) async {
    for (var attempt = 0; attempt < 40; attempt++) {
      if (finder.evaluate().isNotEmpty) return;
      await tester.pump(const Duration(milliseconds: 250));
    }
    fail('Timed out waiting for $finder');
  }

  testWidgets('browses the live catalog and filters it', (tester) async {
    app.main();
    await tester.pumpAndSettle();

    // No saved token on a fresh run, so the sign-in screen is shown first.
    if (find.text('CONTINUE AS GUEST').evaluate().isNotEmpty) {
      await tester.tap(find.text('CONTINUE AS GUEST'));
      await tester.pumpAndSettle();
    }

    expect(find.text('Welcome to Brewly!'), findsOneWidget);

    await waitFor(tester, find.text('Lime Cheesecake'));
    expect(find.text('Lime Cheesecake'), findsWidgets);

    await tester.tap(find.text('Menu').last);
    await tester.pumpAndSettle();
    expect(find.text('Our Menu'), findsOneWidget);

    // The newest catalog rows (Lime Cheesecake, Cream Choco, ...) are first.
    await waitFor(tester, find.text('Lime Cheesecake'));
    expect(find.text('Lime Cheesecake'), findsWidgets);

    // Filter chips must actually filter the list.
    await tester.tap(find.text('Dessert'));
    await tester.pumpAndSettle();
    expect(find.text('Lime Cheesecake'), findsWidgets);
    expect(find.text('Cream Choco'), findsWidgets);
    expect(find.text('Iced Latte'), findsNothing);

    await tester.tap(find.text('All'));
    await tester.pumpAndSettle();

    await tester.enterText(find.byType(TextField).first, 'latte');
    await tester.pumpAndSettle();
    expect(find.text('Iced Latte'), findsOneWidget);
    expect(find.text('Cappuccino'), findsNothing);

    // Product management lives in the Laravel admin dashboard, not the app.
    expect(find.text('Add product'), findsNothing);
  });
}
