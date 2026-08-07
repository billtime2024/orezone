import 'package:flutter/material.dart';

class MyTripsScreen extends StatelessWidget {
  const MyTripsScreen({super.key});
  
  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('My Trips'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'As Driver'),
              Tab(text: 'As Rider'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Driver trips
            ListView(
              padding: const EdgeInsets.all(16),
              children: const [
                Card(
                  child: ListTile(
                    leading: Icon(Icons.directions_car),
                    title: Text('Mumbai → Pune'),
                    subtitle: Text('Tomorrow, 7:00 AM • 3 seats • ₹350'),
                    trailing: Icon(Icons.chevron_right),
                  ),
                ),
                Card(
                  child: ListTile(
                    leading: Icon(Icons.directions_car),
                    title: Text('Pune → Mumbai'),
                    subtitle: Text('Dec 20, 5:00 PM • 2 seats • ₹300'),
                    trailing: Icon(Icons.chevron_right),
                  ),
                ),
              ],
            ),
            // Rider trips
            ListView(
              padding: const EdgeInsets.all(16),
              children: const [
                Card(
                  child: ListTile(
                    leading: Icon(Icons.person),
                    title: Text('Mumbai → Pune'),
                    subtitle: Text('Today, 6:00 AM • John Doe'),
                    trailing: Icon(Icons.chevron_right),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
