import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:shimmer/shimmer.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class TripDetailsScreen extends StatefulWidget {
  final String tripId;

  const TripDetailsScreen({
    super.key,
    required this.tripId,
  });

  @override
  State<TripDetailsScreen> createState() => _TripDetailsScreenState();
}

class _TripDetailsScreenState extends State<TripDetailsScreen> {
  Map<String, dynamic>? _trip;
  bool _isLoading = true;
  String? _error;
  int _selectedSeats = 1;
  bool _isBooking = false;

  @override
  void initState() {
    super.initState();
    _loadTripDetails();
  }

  Future<void> _loadTripDetails() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final apiClient = ApiClient();

    try {
      final response = await apiClient.getTripDetails(widget.tripId);

      if (!mounted) return;

      if (response.success) {
        setState(() {
          _trip = response.asMap;
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.message ?? 'Failed to load trip details';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'An error occurred. Please try again.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _bookSeat() async {
    if (_trip == null) return;

    final availableSeats = _trip!['seats_available'] as int? ?? 0;
    if (_selectedSeats > availableSeats) {
      Fluttertoast.showToast(msg: 'Not enough seats available');
      return;
    }

    setState(() => _isBooking = true);

    final apiClient = ApiClient();

    try {
      final response = await apiClient.bookTrip(widget.tripId, _selectedSeats);

      if (!mounted) return;

      if (response.success) {
        Fluttertoast.showToast(msg: 'Booking confirmed!');
        context.go('/home');
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Failed to book trip',
        );
      }
    } catch (e) {
      if (mounted) {
        Fluttertoast.showToast(msg: 'An error occurred. Please try again.');
      }
    } finally {
      if (mounted) {
        setState(() => _isBooking = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Trip Details'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: () {
              // Share trip
            },
          ),
        ],
      ),
      body: _isLoading
          ? _buildLoadingState()
          : _error != null
              ? _buildErrorState()
              : _buildTripDetails(),
    );
  }

  Widget _buildLoadingState() {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.grey[100]!,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Route skeleton
            Container(
              height: 24,
              width: double.infinity,
              color: Colors.white,
            ),
            const SizedBox(height: AppTheme.spacingLg),
            Container(
              height: 16,
              width: 200,
              color: Colors.white,
            ),
            const SizedBox(height: AppTheme.spacingSm),
            Container(
              height: 16,
              width: 150,
              color: Colors.white,
            ),
            const SizedBox(height: AppTheme.spacingXl),
            // Details skeleton
            Container(
              height: 100,
              width: double.infinity,
              color: Colors.white,
            ),
            const SizedBox(height: AppTheme.spacingMd),
            Container(
              height: 100,
              width: double.infinity,
              color: Colors.white,
            ),
            const SizedBox(height: AppTheme.spacingMd),
            Container(
              height: 60,
              width: double.infinity,
              color: Colors.white,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.error_outline,
            size: 48,
            color: AppTheme.error,
          ),
          const SizedBox(height: AppTheme.spacingMd),
          Text(
            _error!,
            style: const TextStyle(
              color: AppTheme.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: AppTheme.spacingMd),
          ElevatedButton(
            onPressed: _loadTripDetails,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildTripDetails() {
    final trip = _trip!;
    final origin = trip['origin'] as String? ?? 'Unknown';
    final destination = trip['destination'] as String? ?? 'Unknown';
    final date = trip['date'] as String? ?? '';
    final time = trip['time'] as String? ?? '';
    final seats = trip['seats_available'] as int? ?? 0;
    final price = trip['price'] as double? ?? 0.0;
    final hostName = trip['host_name'] as String? ?? '';
    final hostRating = trip['host_rating'] as double? ?? 0.0;
    final vehicle = trip['vehicle'] as String? ?? '';
    final vehicleNumber = trip['vehicle_number'] as String? ?? '';
    final notes = trip['notes'] as String? ?? '';

    return Column(
      children: [
        Expanded(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppTheme.spacingMd),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Route card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppTheme.spacingLg),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        AppTheme.primary,
                        AppTheme.primaryLight,
                      ],
                    ),
                    borderRadius: BorderRadius.circular(AppTheme.radiusLg),
                  ),
                  child: Column(
                    children: [
                      // Origin
                      Row(
                        children: [
                          Container(
                            width: 12,
                            height: 12,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: Colors.white,
                                width: 2,
                              ),
                            ),
                          ),
                          const SizedBox(width: AppTheme.spacingMd),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'FROM',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.white70,
                                  ),
                                ),
                                Text(
                                  origin,
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      // Dashed line
                      Padding(
                        padding: const EdgeInsets.only(left: 5),
                        child: CustomPaint(
                          size: const Size(2, 40),
                          painter: _DashedLinePainter(
                            color: Colors.white54,
                          ),
                        ),
                      ),

                      // Destination
                      Row(
                        children: [
                          Container(
                            width: 12,
                            height: 12,
                            decoration: const BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: AppTheme.spacingMd),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'TO',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.white70,
                                  ),
                                ),
                                Text(
                                  destination,
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppTheme.spacingLg),

                // Trip info
                _InfoCard(
                  icon: Icons.calendar_today,
                  title: 'Date & Time',
                  child: Text(
                    '${date.isNotEmpty ? date : "Not specified"} ${time.isNotEmpty ? '• $time' : ''}',
                    style: const TextStyle(
                      fontSize: 16,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ),
                const SizedBox(height: AppTheme.spacingMd),

                _InfoCard(
                  icon: Icons.event_seat,
                  title: 'Available Seats',
                  child: Text(
                    '$seats seats available',
                    style: const TextStyle(
                      fontSize: 16,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                ),
                const SizedBox(height: AppTheme.spacingMd),

                // Host info
                if (hostName.isNotEmpty) ...[
                  _InfoCard(
                    icon: Icons.person,
                    title: 'Host',
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundColor:
                              AppTheme.primaryLight.withValues(alpha: 0.2),
                          child: const Icon(
                            Icons.person,
                            color: AppTheme.primary,
                          ),
                        ),
                        const SizedBox(width: AppTheme.spacingMd),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                hostName,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              if (hostRating > 0)
                                Row(
                                  children: [
                                    const Icon(
                                      Icons.star,
                                      size: 16,
                                      color: AppTheme.warning,
                                    ),
                                    const SizedBox(width: AppTheme.spacingXs),
                                    Text(
                                      hostRating.toStringAsFixed(1),
                                      style: const TextStyle(
                                        color: AppTheme.textSecondary,
                                      ),
                                    ),
                                  ],
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppTheme.spacingMd),
                ],

                // Vehicle info
                if (vehicle.isNotEmpty)
                  _InfoCard(
                    icon: Icons.directions_car,
                    title: 'Vehicle',
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          vehicle,
                          style: const TextStyle(
                            fontSize: 16,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                        if (vehicleNumber.isNotEmpty)
                          Text(
                            vehicleNumber,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textSecondary,
                            ),
                          ),
                      ],
                    ),
                  ),

                // Notes
                if (notes.isNotEmpty) ...[
                  const SizedBox(height: AppTheme.spacingMd),
                  _InfoCard(
                    icon: Icons.notes,
                    title: 'Notes',
                    child: Text(
                      notes,
                      style: const TextStyle(
                        fontSize: 14,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),

        // Bottom booking bar
        Container(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          decoration: BoxDecoration(
            color: AppTheme.surface,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.1),
                blurRadius: 10,
                offset: const Offset(0, -2),
              ),
            ],
          ),
          child: SafeArea(
            child: Row(
              children: [
                // Price
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Price per seat',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                    Text(
                      '₹${price.toStringAsFixed(0)}',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: AppTheme.spacingLg),

                // Seat selector
                Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: AppTheme.border),
                    borderRadius: BorderRadius.circular(AppTheme.radiusMd),
                  ),
                  child: Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.remove),
                        color: _selectedSeats > 1
                            ? AppTheme.primary
                            : AppTheme.textLight,
                        onPressed: _selectedSeats > 1
                            ? () => setState(() => _selectedSeats--)
                            : null,
                      ),
                      Text(
                        '$_selectedSeats',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.add),
                        color: _selectedSeats < seats
                            ? AppTheme.primary
                            : AppTheme.textLight,
                        onPressed: _selectedSeats < seats
                            ? () => setState(() => _selectedSeats++)
                            : null,
                      ),
                    ],
                  ),
                ),
                const Spacer(),

                // Book button
                ElevatedButton(
                  onPressed: (_isBooking || seats == 0) ? null : _bookSeat,
                  child: _isBooking
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              Colors.white,
                            ),
                          ),
                        )
                      : Text(
                          seats == 0 ? 'Full' : 'Book',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _InfoCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final Widget child;

  const _InfoCard({
    required this.icon,
    required this.title,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppTheme.spacingMd),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                icon,
                size: 18,
                color: AppTheme.primary,
              ),
              const SizedBox(width: AppTheme.spacingSm),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTheme.spacingSm),
          child,
        ],
      ),
    );
  }
}

class _DashedLinePainter extends CustomPainter {
  final Color color;

  _DashedLinePainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 2
      ..style = PaintingStyle.stroke;

    const dashHeight = 5.0;
    const dashSpace = 5.0;
    double startY = 0;

    while (startY < size.height) {
      canvas.drawLine(
        Offset(0, startY),
        Offset(0, startY + dashHeight),
        paint,
      );
      startY += dashHeight + dashSpace;
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
