import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/trip.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class TripManagementScreen extends StatefulWidget {
  const TripManagementScreen({super.key});

  @override
  State<TripManagementScreen> createState() => _TripManagementScreenState();
}

class _TripManagementScreenState extends State<TripManagementScreen> {
  bool _isLoading = true;
  List<Trip> _trips = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadTrips();
  }

  Future<void> _loadTrips() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    final api = ApiClient();
    final response = await api.get('/trips/host');
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (response.success && response.data is Map) {
          final list = response.data['data'] as List? ?? [];
          _trips = list.map((t) => Trip.fromJson(t)).toList();
        } else {
          _error = response.message ?? 'Failed to load trips';
        }
      });
    }
  }

  Future<void> _updateTripStatus(Trip trip, String action) async {
    final api = ApiClient();
    final response = await api.post('/trips/${trip.id}/$action');
    if (mounted) {
      if (response.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Trip ${action}d successfully'),
            backgroundColor: AppTheme.success,
          ),
        );
        _loadTrips();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response.message ?? 'Failed to update trip'),
            backgroundColor: AppTheme.error,
          ),
        );
      }
    }
  }

  void _showActionsDialog(Trip trip) {
    final actions = <_TripAction>[];

    if (trip.status == 'pending') {
      actions.add(_TripAction(
        icon: Icons.play_arrow,
        label: 'Start Trip',
        color: AppTheme.primary,
        action: 'start',
      ));
      actions.add(_TripAction(
        icon: Icons.cancel,
        label: 'Cancel Trip',
        color: AppTheme.error,
        action: 'cancel',
      ));
    } else if (trip.status == 'active') {
      actions.add(_TripAction(
        icon: Icons.check_circle,
        label: 'Complete Trip',
        color: AppTheme.success,
        action: 'complete',
      ));
    }

    if (actions.isEmpty) return;

    showModalBottomSheet(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                color: AppTheme.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(AppTheme.spacingMd),
              child: Text(
                '${trip.fromCity} → ${trip.toCity}',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            ...actions.map((a) => ListTile(
                  leading: Icon(a.icon, color: a.color),
                  title: Text(
                    a.label,
                    style: TextStyle(color: a.color, fontWeight: FontWeight.w500),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    _updateTripStatus(trip, a.action);
                  },
                )),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Hosted Trips')),
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
                        onPressed: _loadTrips,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _trips.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.directions_car_outlined,
                            size: 64,
                            color: AppTheme.textLight,
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'No trips hosted yet',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Offer your first trip to start earning',
                            style: TextStyle(color: AppTheme.textSecondary),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadTrips,
                      child: ListView.separated(
                        padding: const EdgeInsets.all(AppTheme.spacingMd),
                        itemCount: _trips.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: AppTheme.spacingSm),
                        itemBuilder: (context, index) {
                          final trip = _trips[index];
                          return _TripCard(
                            trip: trip,
                            onTap: () => _showActionsDialog(trip),
                          );
                        },
                      ),
                    ),
    );
  }
}

class _TripCard extends StatelessWidget {
  final Trip trip;
  final VoidCallback onTap;

  const _TripCard({required this.trip, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        Text(
                          trip.fromCity,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 16,
                          ),
                        ),
                        const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 6),
                          child: Icon(Icons.arrow_forward, size: 16),
                        ),
                        Text(
                          trip.toCity,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),
                  ),
                  _StatusBadge(status: trip.status),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.calendar_today, size: 14, color: AppTheme.textSecondary),
                  const SizedBox(width: 4),
                  Text(
                    DateFormat('MMM dd, yyyy • h:mm a').format(trip.departureTime),
                    style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(Icons.people, size: 14, color: AppTheme.textSecondary),
                  const SizedBox(width: 4),
                  Text(
                    '${trip.seatsAvailable}/${trip.availableSeats} seats available',
                    style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
                  ),
                  const SizedBox(width: AppTheme.spacingMd),
                  const Icon(Icons.attach_money, size: 14, color: AppTheme.textSecondary),
                  const SizedBox(width: 4),
                  Text(
                    '₹${trip.pricePerSeat.toStringAsFixed(0)}/seat',
                    style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color bgColor;
    Color textColor;
    String label;

    switch (status) {
      case 'active':
        bgColor = AppTheme.primary.withValues(alpha: 0.1);
        textColor = AppTheme.primary;
        label = 'In Progress';
        break;
      case 'completed':
        bgColor = AppTheme.success.withValues(alpha: 0.1);
        textColor = AppTheme.success;
        label = 'Completed';
        break;
      case 'cancelled':
        bgColor = AppTheme.error.withValues(alpha: 0.1);
        textColor = AppTheme.error;
        label = 'Cancelled';
        break;
      default:
        bgColor = AppTheme.warning.withValues(alpha: 0.1);
        textColor = AppTheme.warning;
        label = 'Published';
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

class _TripAction {
  final IconData icon;
  final String label;
  final Color color;
  final String action;

  const _TripAction({
    required this.icon,
    required this.label,
    required this.color,
    required this.action,
  });
}
