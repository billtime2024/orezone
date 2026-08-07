import 'package:flutter/material.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          Card(
            child: ListTile(
              leading: Icon(Icons.check_circle, color: Color(0xFF198754)),
              title: Text('Booking Confirmed'),
              subtitle: Text('Your trip from Mumbai to Pune has been confirmed.'),
              trailing: Text('2m ago'),
            ),
          ),
          Card(
            child: ListTile(
              leading: Icon(Icons.payment, color: Color(0xFF0D6EFD)),
              title: Text('Payment Received'),
              subtitle: Text('You received ₹350 for trip to Pune.'),
              trailing: Text('1h ago'),
            ),
          ),
          Card(
            child: ListTile(
              leading: Icon(Icons.star, color: Color(0xFFFFC107)),
              title: Text('New Rating'),
              subtitle: Text('You received a 5-star rating from John.'),
              trailing: Text('1d ago'),
            ),
          ),
        ],
      ),
    );
  }
}
