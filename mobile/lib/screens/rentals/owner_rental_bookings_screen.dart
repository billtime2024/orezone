import 'package:flutter/material.dart';
import '../../models/rental_booking.dart';
import '../../services/rental_service.dart';

class OwnerRentalBookingsScreen extends StatefulWidget {
  const OwnerRentalBookingsScreen({super.key});

  @override
  State<OwnerRentalBookingsScreen> createState() => _OwnerRentalBookingsScreenState();
}

class _OwnerRentalBookingsScreenState extends State<OwnerRentalBookingsScreen> {
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
      final result = await _service.getOwnerBookings(
        status: _selectedTab == 'all' ? null : _selectedTab,
      );
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Rental Listings — Bookings')),
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
                : _bookings.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inbox, size: 64, color: Colors.grey),
                            SizedBox(height: 16),
                            Text('No bookings found', style: TextStyle(fontSize: 18, color: Colors.grey)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadBookings,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _bookings.length,
                          itemBuilder: (context, index) {
                            return _buildBookingCard(_bookings[index]);
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
        onSelected: (selected) {
          setState(() => _selectedTab = status);
          _loadBookings();
        },
        selectedColor: Theme.of(context).colorScheme.primaryContainer,
      ),
    );
  }

  Widget _buildBookingCard(RentalBooking booking) {
    final guest = booking.guest;
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
                Icon(Icons.person, size: 14, color: Colors.grey[500]),
                const SizedBox(width: 4),
                Text(guest?['name'] ?? 'Guest', style: TextStyle(fontSize: 13, color: Colors.grey[600])),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                Icon(Icons.calendar_today, size: 14, color: Colors.grey[500]),
                const SizedBox(width: 4),
                Text('${booking.checkIn} → ${booking.checkOut}',
                    style: TextStyle(fontSize: 13, color: Colors.grey[600])),
                const SizedBox(width: 12),
                Text('₹${booking.totalAmount.toStringAsFixed(0)}',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green)),
              ],
            ),

            // Action buttons for pending bookings
            if (booking.status == 'pending') ...[
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => _confirmBooking(booking),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('Confirm'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => _rejectBooking(booking),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.red,
                        side: const BorderSide(color: Colors.red),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: const Text('Reject'),
                    ),
                  ),
                ],
              ),
            ],
            // Host cancel for confirmed bookings
            if (booking.status == 'confirmed') ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerRight,
                child: OutlinedButton(
                  onPressed: () => _hostCancelBooking(booking),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.red,
                    side: const BorderSide(color: Colors.red),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Cancel Booking'),
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
        color = Colors.red;
        label = 'Cancelled (Guest)';
        break;
      case 'rejected':
        color = Colors.red;
        label = 'Rejected';
        break;
      case 'cancelled_by_host':
        color = Colors.red;
        label = 'Cancelled by Host';
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

  Future<void> _confirmBooking(RentalBooking booking) async {
    try {
      await _service.confirmBooking(booking.id);
      _loadBookings();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Booking confirmed'), backgroundColor: Colors.green),
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

  Future<void> _rejectBooking(RentalBooking booking) async {
    final reason = await showDialog<String>(
      context: context,
      builder: (context) {
        final controller = TextEditingController();
        return AlertDialog(
          title: const Text('Reject Booking'),
          content: TextField(
            controller: controller,
            decoration: const InputDecoration(hintText: 'Reason for rejection'),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
            TextButton(
              onPressed: () => Navigator.pop(context, controller.text),
              child: const Text('Reject', style: TextStyle(color: Colors.red)),
            ),
          ],
        );
      },
    );

    if (reason != null && reason.isNotEmpty) {
      try {
        await _service.rejectBooking(booking.id, reason);
        _loadBookings();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Booking rejected'), backgroundColor: Colors.green),
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

  Future<void> _hostCancelBooking(RentalBooking booking) async {
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
        await _service.hostCancelBooking(booking.id, reason);
        _loadBookings();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Booking cancelled by host'), backgroundColor: Colors.green),
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
