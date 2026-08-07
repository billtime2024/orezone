import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('App starts', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(
          body: Center(
            child: Text('Orozone'),
          ),
        ),
      ),
    );

    expect(find.text('Orozone'), findsOneWidget);
  });
}
