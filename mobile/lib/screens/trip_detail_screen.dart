import 'package:flutter/material.dart';

class TripDetailScreen extends StatelessWidget {
  final int tripId;
  
  const TripDetailScreen({super.key, required this.tripId});
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Trip Details'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Trip Info
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Mumbai → Pune',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Tomorrow, 7:00 AM',
                      style: TextStyle(
                        fontSize: 16,
                        color: Color(0xFF6C757D),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Price per seat'),
                        const Text(
                          '₹350',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF198754),
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 32),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Available seats'),
                        const Text('3'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            // Driver Info
            Card(
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: const Color(0xFF0D6EFD),
                  child: const Icon(Icons.person, color: Colors.white),
                ),
                title: const Text('John Doe'),
                subtitle: const Text('⭐ 4.8 • 150 trips'),
                trailing: const Icon(Icons.chevron_right),
              ),
            ),
            const SizedBox(height: 24),
            // Book Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  // TODO: Book trip
                },
                child: const Text('Book Seat'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
