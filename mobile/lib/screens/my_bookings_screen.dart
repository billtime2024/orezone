import 'package:flutter/material.dart';

class MyBookingsScreen extends StatelessWidget {
  const MyBookingsScreen({super.key});
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Bookings'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          Card(
            child: ListTile(
              leading: Icon(Icons.check_circle, color: Color(0xFF198754)),
              title: Text('Mumbai → Pune'),
              subtitle: Text('Today, 6:00 AM • Confirmed'),
              trailing: Icon(Icons.chevron_right),
            ),
          ),
          Card(
            child: ListTile(
              leading: Icon(Icons.pending, color: Color(0xFFFFC107)),
              title: Text('Pune → Mumbai'),
              subtitle: Text('Dec 20, 5:00 PM • Pending'),
              trailing: Icon(Icons.chevron_right),
            ),
          ),
        ],
      ),
    );
  }
}
