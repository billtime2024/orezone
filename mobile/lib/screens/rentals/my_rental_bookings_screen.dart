import 'package:flutter/material.dart';
import '../../models/rental_booking.dart';
import '../../services/rental_service.dart';

class MyRentalBookingsScreen extends StatefulWidget {
  const MyRentalBookingsScreen({super.key});

  @override
  State<MyRentalBookingsScreen> createState() => _MyRentalBookingsScreenState();
}

class _MyRentalBookingsScreenState extends State<MyRentalBookingsScreen> {
  final _service = RentalService();
  List<RentalBooking> _bookings = [];
  bool _isLoading = true;
  String _selectedTab = 'all';

  @override
  void initState() {
    super.initState();
    _loadBookings();
  }

  Future<void> _loadBookings() async {
    setState(() => _isLoading = true);
    try {
      final result = await _service.getMyBookings();
      setState(() {
        _bookings = (result['data'] as List? ?? [])
            .map((json) => RentalBooking.fromJson(json))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  List<RentalBooking> get _filteredBookings {
    if (_selectedTab == 'all') return _bookings;
    return _bookings.where((b) => b.status == _selectedTab).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Rental Bookings')),
      body: Column(
        children: [
          // Tabs
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildTab('All', 'all'),
                  _buildTab('Pending', 'pending'),
                  _buildTab('Confirmed', 'confirmed'),
                  _buildTab('Active', 'active'),
                  _buildTab('Completed', 'completed'),
                ],
              ),
            ),
          ),

          // Bookings list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredBookings.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.receipt_long, size: 64, color: Colors.grey),
                            SizedBox(height: 16),
                            Text('No bookings found', style: TextStyle(fontSize: 18, color: Colors.grey)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadBookings,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredBookings.length,
                          itemBuilder: (context, index) {
                            return _buildBookingCard(_filteredBookings[index]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildTab(String label, String status) {
    final isSelected = _selectedTab == status;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) => setState(() => _selectedTab = status),
        selectedColor: Theme.of(context).colorScheme.primaryContainer,
      ),
    );
  }

  Widget _buildBookingCard(RentalBooking booking) {
    final listing = booking.listing;
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    listing?['title'] ?? 'Listing #${booking.rentalListingId}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                _buildStatusBadge(booking.status),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.calendar_today, size: 14, color: Colors.grey[500]),
                const SizedBox(width: 4),
                Text('${booking.checkIn} → ${booking.checkOut}',
                    style: TextStyle(fontSize: 13, color: Colors.grey[600])),
                const SizedBox(width: 12),
                Icon(Icons.nightlight_round, size: 14, color: Colors.grey[500]),
                const SizedBox(width: 4),
                Text('${booking.nights} night(s)', style: TextStyle(fontSize: 13, color: Colors.grey[600])),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '₹${booking.totalAmount.toStringAsFixed(0)}',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.green),
                ),
                Text('Booked ${booking.createdAt.day}/${booking.createdAt.month}/${booking.createdAt.year}',
                    style: TextStyle(fontSize: 12, color: Colors.grey[500])),
              ],
            ),
            if (booking.canBeCancelled) ...[
              const SizedBox(height: 8),
              Align(
                alignment: Alignment.centerRight,
                child: OutlinedButton(
                  onPressed: () => _cancelBooking(booking),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.red,
                    side: const BorderSide(color: Colors.red),
                  ),
                  child: const Text('Cancel'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    String label;
    switch (status) {
      case 'pending':
        color = Colors.orange;
        label = 'Pending';
        break;
      case 'confirmed':
        color = Colors.blue;
        label = 'Confirmed';
        break;
      case 'active':
        color = Colors.green;
        label = 'Active';
        break;
      case 'completed':
        color = Colors.grey;
        label = 'Completed';
        break;
      case 'cancelled_by_guest':
      case 'cancelled_by_host':
      case 'rejected':
        color = Colors.red;
        label = 'Cancelled';
        break;
      default:
        color = Colors.grey;
        label = status;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color..withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
    );
  }

  Future<void> _cancelBooking(RentalBooking booking) async {
    final reason = await showDialog<String>(
      context: context,
      builder: (context) {
        final controller = TextEditingController();
        return AlertDialog(
          title: const Text('Cancel Booking'),
          content: TextField(
            controller: controller,
            decoration: const InputDecoration(hintText: 'Reason for cancellation'),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('No')),
            TextButton(
              onPressed: () => Navigator.pop(context, controller.text),
              child: const Text('Cancel Booking', style: TextStyle(color: Colors.red)),
            ),
          ],
        );
      },
    );

    if (reason != null && reason.isNotEmpty) {
      try {
        await _service.cancelBooking(booking.id, reason);
        _loadBookings();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Booking cancelled'), backgroundColor: Colors.green),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
          );
        }
      }
    }
  }
}
