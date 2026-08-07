import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/trip.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class MyTripsHostScreen extends StatefulWidget {
  const MyTripsHostScreen({super.key});

  @override
  State<MyTripsHostScreen> createState() => _MyTripsHostScreenState();
}

class _MyTripsHostScreenState extends State<MyTripsHostScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  List<Trip> _allTrips = [];
  String? _error;

  final _tabs = ['All', 'Published', 'In Progress', 'Completed'];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _loadTrips();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
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
          _allTrips = list.map((t) => Trip.fromJson(t)).toList();
        } else {
          _error = response.message ?? 'Failed to load trips';
        }
      });
    }
  }

  List<Trip> _filteredTrips(int tabIndex) {
    switch (tabIndex) {
      case 1:
        return _allTrips.where((t) => t.status == 'pending').toList();
      case 2:
        return _allTrips.where((t) => t.status == 'active').toList();
      case 3:
        return _allTrips.where((t) => t.status == 'completed').toList();
      default:
        return _allTrips;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Trips'),
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
                        onPressed: _loadTrips,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadTrips,
                  child: TabBarView(
                    controller: _tabController,
                    children: _tabs.asMap().entries.map((entry) {
                      final trips = _filteredTrips(entry.key);
                      if (trips.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.directions_car_outlined,
                                size: 48,
                                color: AppTheme.textLight,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                'No ${entry.value.toLowerCase()} trips',
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
                        itemCount: trips.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: AppTheme.spacingSm),
                        itemBuilder: (context, index) =>
                            _TripListTile(trip: trips[index]),
                      );
                    }).toList(),
                  ),
                ),
    );
  }
}

class _TripListTile extends StatelessWidget {
  final Trip trip;

  const _TripListTile({required this.trip});

  @override
  Widget build(BuildContext context) {
    Color statusColor;
    String statusLabel;

    switch (trip.status) {
      case 'active':
        statusColor = AppTheme.primary;
        statusLabel = 'In Progress';
        break;
      case 'completed':
        statusColor = AppTheme.success;
        statusLabel = 'Completed';
        break;
      case 'cancelled':
        statusColor = AppTheme.error;
        statusLabel = 'Cancelled';
        break;
      default:
        statusColor = AppTheme.warning;
        statusLabel = 'Published';
    }

    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppTheme.primary.withValues(alpha: 0.1),
          child: const Icon(Icons.directions_car, color: AppTheme.primary),
        ),
        title: Text('${trip.fromCity} → ${trip.toCity}'),
        subtitle: Text(
          '${DateFormat('MMM dd, yyyy • h:mm a').format(trip.departureTime)} • '
          '${trip.seatsAvailable} seats • ₹${trip.pricePerSeat.toStringAsFixed(0)}',
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
          decoration: BoxDecoration(
            color: statusColor.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(AppTheme.radiusFull),
          ),
          child: Text(
            statusLabel,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: statusColor,
            ),
          ),
        ),
      ),
    );
  }
}
