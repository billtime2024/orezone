import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/booking.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class MyBookingsTravelerScreen extends StatefulWidget {
  const MyBookingsTravelerScreen({super.key});

  @override
  State<MyBookingsTravelerScreen> createState() =>
      _MyBookingsTravelerScreenState();
}

class _MyBookingsTravelerScreenState extends State<MyBookingsTravelerScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  List<Booking> _allBookings = [];
  String? _error;

  final _tabs = ['All', 'Pending', 'Confirmed', 'Completed'];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _loadBookings();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadBookings() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    final api = ApiClient();
    final response = await api.get('/bookings/my');
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (response.success && response.data is Map) {
          final list = response.data['data'] as List? ?? [];
          _allBookings = list.map((b) => Booking.fromJson(b)).toList();
        } else {
          _error = response.message ?? 'Failed to load bookings';
        }
      });
    }
  }

  List<Booking> _filteredBookings(int tabIndex) {
    switch (tabIndex) {
      case 1:
        return _allBookings.where((b) => b.status == 'pending').toList();
      case 2:
        return _allBookings.where((b) => b.status == 'confirmed').toList();
      case 3:
        return _allBookings.where((b) => b.status == 'completed').toList();
      default:
        return _allBookings;
    }
  }

  Future<void> _cancelBooking(Booking booking) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Booking?'),
        content: const Text('Are you sure you want to cancel this booking?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppTheme.error),
            child: const Text('Yes, Cancel'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    final api = ApiClient();
    final response = await api.post('/bookings/${booking.id}/cancel');
    if (mounted) {
      if (response.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Booking cancelled'),
            backgroundColor: AppTheme.success,
          ),
        );
        _loadBookings();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response.message ?? 'Failed to cancel booking'),
            backgroundColor: AppTheme.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Bookings'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: _tabs.map((t) => Tab(text: t)).toList(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: AppTheme.error)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadBookings,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadBookings,
                  child: TabBarView(
                    controller: _tabController,
                    children: _tabs.asMap().entries.map((entry) {
                      final bookings = _filteredBookings(entry.key);
                      if (bookings.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.receipt_long_outlined,
                                size: 48,
                                color: AppTheme.textLight,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'No ${entry.value.toLowerCase()} bookings',
                                style: const TextStyle(
                                  fontSize: 16,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        );
                      }
                      return ListView.separated(
                        padding: const EdgeInsets.all(AppTheme.spacingMd),
                        itemCount: bookings.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: AppTheme.spacingSm),
                        itemBuilder: (context, index) => _BookingListTile(
                          booking: bookings[index],
                          onCancel: () => _cancelBooking(bookings[index]),
                        ),
                      );
                    }).toList(),
                  ),
                ),
    );
  }
}

class _BookingListTile extends StatelessWidget {
  final Booking booking;
  final VoidCallback onCancel;

  const _BookingListTile({required this.booking, required this.onCancel});

  @override
  Widget build(BuildContext context) {
    final canCancel =
        booking.status == 'pending' || booking.status == 'confirmed';

    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppTheme.primary.withValues(alpha: 0.1),
          child: const Icon(Icons.person, color: AppTheme.primary),
        ),
        title: Text('Trip #${booking.tripId}'),
        subtitle: Text(
          '${DateFormat('MMM dd, yyyy').format(booking.createdAt)} • '
          '${booking.seatsBooked} seat${booking.seatsBooked > 1 ? 's' : ''} • '
          '₹${booking.totalAmount.toStringAsFixed(0)}',
        ),
        trailing: canCancel
            ? TextButton(
                onPressed: onCancel,
                style: TextButton.styleFrom(foregroundColor: AppTheme.error),
                child: const Text('Cancel', style: TextStyle(fontSize: 12)),
              )
            : _StatusChip(status: booking.status),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  final String status;

  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    Color bgColor;
    Color textColor;
    String label;

    switch (status) {
      case 'confirmed':
        bgColor = AppTheme.success.withValues(alpha: 0.1);
        textColor = AppTheme.success;
        label = 'Confirmed';
        break;
      case 'cancelled':
        bgColor = AppTheme.error.withValues(alpha: 0.1);
        textColor = AppTheme.error;
        label = 'Cancelled';
        break;
      case 'completed':
        bgColor = AppTheme.info.withValues(alpha: 0.1);
        textColor = AppTheme.info;
        label = 'Completed';
        break;
      default:
        bgColor = AppTheme.warning.withValues(alpha: 0.1);
        textColor = AppTheme.warning;
        label = 'Pending';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(AppTheme.radiusFull),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: textColor,
        ),
      ),
    );
  }
}
