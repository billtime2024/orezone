import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _originController = TextEditingController();
  final _destinationController = TextEditingController();
  final _originFocusNode = FocusNode();
  final _destinationFocusNode = FocusNode();
  DateTime? _selectedDate;
  int _seats = 1;
  bool _isSearching = false;
  bool _hasSearched = false;
  List<Map<String, dynamic>> _results = [];
  String? _error;

  @override
  void dispose() {
    _originController.dispose();
    _destinationController.dispose();
    _originFocusNode.dispose();
    _destinationFocusNode.dispose();
    super.dispose();
  }

  bool _validateForm() {
    if (_originController.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Please enter origin');
      _originFocusNode.requestFocus();
      return false;
    }
    if (_destinationController.text.trim().isEmpty) {
      Fluttertoast.showToast(msg: 'Please enter destination');
      _destinationFocusNode.requestFocus();
      return false;
    }
    if (_originController.text.trim() == _destinationController.text.trim()) {
      Fluttertoast.showToast(msg: 'Origin and destination cannot be the same');
      return false;
    }
    return true;
  }

  Future<void> _selectDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? now,
      firstDate: now,
      lastDate: now.add(const Duration(days: 30)),
    );

    if (picked != null && mounted) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _searchTrips() async {
    if (!_validateForm()) return;

    setState(() {
      _isSearching = true;
      _error = null;
    });

    final apiClient = ApiClient();

    try {
      final response = await apiClient.searchTrips(
        origin: _originController.text.trim(),
        destination: _destinationController.text.trim(),
        date: _selectedDate != null
            ? DateFormat('yyyy-MM-dd').format(_selectedDate!)
            : null,
        seats: _seats,
      );

      if (!mounted) return;

      setState(() {
        _hasSearched = true;
        if (response.success) {
          final data = response.asList;
          _results = data.cast<Map<String, dynamic>>();
        } else {
          _error = response.message ?? 'Failed to search trips';
        }
      });
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'An error occurred. Please try again.';
        });
      }
    } finally {
      if (mounted) {
        setState(() => _isSearching = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Search Trips'),
      ),
      body: Column(
        children: [
          // Search form
          Container(
            padding: const EdgeInsets.all(AppTheme.spacingMd),
            color: AppTheme.surface,
            child: Column(
              children: [
                // Origin
                TextField(
                  controller: _originController,
                  focusNode: _originFocusNode,
                  textCapitalization: TextCapitalization.words,
                  decoration: const InputDecoration(
                    hintText: 'Origin (e.g., Mumbai)',
                    prefixIcon: Icon(Icons.circle_outlined),
                  ),
                  textInputAction: TextInputAction.next,
                  onSubmitted: (_) => _destinationFocusNode.requestFocus(),
                ),
                const SizedBox(height: AppTheme.spacingSm),

                // Swap button
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.swap_vert,
                        color: AppTheme.primary,
                      ),
                      onPressed: () {
                        final temp = _originController.text;
                        _originController.text = _destinationController.text;
                        _destinationController.text = temp;
                      },
                    ),
                  ],
                ),

                // Destination
                TextField(
                  controller: _destinationController,
                  focusNode: _destinationFocusNode,
                  textCapitalization: TextCapitalization.words,
                  decoration: const InputDecoration(
                    hintText: 'Destination (e.g., Pune)',
                    prefixIcon: Icon(Icons.location_on_outlined),
                  ),
                  textInputAction: TextInputAction.done,
                  onSubmitted: (_) => _searchTrips(),
                ),
                const SizedBox(height: AppTheme.spacingMd),

                // Date and seats row
                Row(
                  children: [
                    // Date picker
                    Expanded(
                      child: GestureDetector(
                        onTap: _selectDate,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTheme.spacingMd,
                            vertical: AppTheme.spacingMd,
                          ),
                          decoration: BoxDecoration(
                            border: Border.all(color: AppTheme.border),
                            borderRadius: BorderRadius.circular(
                              AppTheme.radiusMd,
                            ),
                            color: AppTheme.surface,
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.calendar_today,
                                color: AppTheme.textSecondary,
                                size: 20,
                              ),
                              const SizedBox(width: AppTheme.spacingSm),
                              Text(
                                _selectedDate != null
                                    ? DateFormat('MMM dd, yyyy')
                                        .format(_selectedDate!)
                                    : 'Select date',
                                style: TextStyle(
                                  color: _selectedDate != null
                                      ? AppTheme.textPrimary
                                      : AppTheme.textLight,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: AppTheme.spacingMd),

                    // Seats selector
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTheme.spacingSm,
                      ),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppTheme.border),
                        borderRadius: BorderRadius.circular(
                          AppTheme.radiusMd,
                        ),
                        color: AppTheme.surface,
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            icon: const Icon(
                              Icons.remove_circle_outline,
                              size: 28,
                            ),
                            color: _seats > 1
                                ? AppTheme.primary
                                : AppTheme.textLight,
                            onPressed: _seats > 1
                                ? () => setState(() => _seats--)
                                : null,
                          ),
                          Text(
                            '$_seats',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          IconButton(
                            icon: const Icon(
                              Icons.add_circle_outline,
                              size: 28,
                            ),
                            color: _seats < 8
                                ? AppTheme.primary
                                : AppTheme.textLight,
                            onPressed: _seats < 8
                                ? () => setState(() => _seats++)
                                : null,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppTheme.spacingMd),

                // Search button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _isSearching ? null : _searchTrips,
                    icon: _isSearching
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
                        : const Icon(Icons.search),
                    label: Text(_isSearching ? 'Searching...' : 'Search'),
                  ),
                ),
              ],
            ),
          ),

          // Results
          Expanded(
            child: _buildResults(),
          ),
        ],
      ),
    );
  }

  Widget _buildResults() {
    if (_isSearching) {
      return _buildShimmerLoading();
    }

    if (_error != null) {
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
            ),
            const SizedBox(height: AppTheme.spacingMd),
            ElevatedButton(
              onPressed: _searchTrips,
              child: const Text('Retry'),
            ),
          ],
        ),
      );
    }

    if (!_hasSearched) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.search,
              size: 80,
              color: AppTheme.textLight.withValues(alpha: 0.5),
            ),
            const SizedBox(height: AppTheme.spacingLg),
            const Text(
              'Search for trips',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondary,
              ),
            ),
            const SizedBox(height: AppTheme.spacingSm),
            const Text(
              'Enter origin and destination to find available rides',
              style: TextStyle(
                color: AppTheme.textLight,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }

    if (_results.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.directions_car_outlined,
              size: 80,
              color: AppTheme.textLight.withValues(alpha: 0.5),
            ),
            const SizedBox(height: AppTheme.spacingLg),
            const Text(
              'No trips found',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSecondary,
              ),
            ),
            const SizedBox(height: AppTheme.spacingSm),
            const Text(
              'Try adjusting your search criteria',
              style: TextStyle(
                color: AppTheme.textLight,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppTheme.spacingMd),
      itemCount: _results.length,
      itemBuilder: (context, index) {
        final trip = _results[index];
        return _SearchResultCard(
          trip: trip,
          onTap: () {
            final tripId = trip['id']?.toString() ?? '';
            if (tripId.isNotEmpty) {
              context.push('/trip/$tripId');
            }
          },
        );
      },
    );
  }

  Widget _buildShimmerLoading() {
    return ListView.builder(
      padding: const EdgeInsets.all(AppTheme.spacingMd),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Shimmer.fromColors(
          baseColor: Colors.grey[300]!,
          highlightColor: Colors.grey[100]!,
          child: Container(
            margin: const EdgeInsets.only(bottom: AppTheme.spacingMd),
            padding: const EdgeInsets.all(AppTheme.spacingMd),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(AppTheme.radiusMd),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 16,
                  width: 200,
                  color: Colors.white,
                ),
                const SizedBox(height: AppTheme.spacingSm),
                Container(
                  height: 14,
                  width: 150,
                  color: Colors.white,
                ),
                const SizedBox(height: AppTheme.spacingMd),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      height: 14,
                      width: 80,
                      color: Colors.white,
                    ),
                    Container(
                      height: 20,
                      width: 60,
                      color: Colors.white,
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _SearchResultCard extends StatelessWidget {
  final Map<String, dynamic> trip;
  final VoidCallback onTap;

  const _SearchResultCard({
    required this.trip,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final origin = trip['origin'] as String? ?? 'Unknown';
    final destination = trip['destination'] as String? ?? 'Unknown';
    final date = trip['date'] as String? ?? '';
    final time = trip['time'] as String? ?? '';
    final seats = trip['seats_available'] as int? ?? 0;
    final price = trip['price'] as double? ?? 0.0;
    final hostName = trip['host_name'] as String? ?? '';
    final hostRating = trip['host_rating'] as double? ?? 0.0;
    final vehicle = trip['vehicle'] as String? ?? '';

    return Padding(
      padding: const EdgeInsets.only(bottom: AppTheme.spacingMd),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
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
              // Route
              Row(
                children: [
                  const Icon(
                    Icons.circle_outlined,
                    size: 12,
                    color: AppTheme.primary,
                  ),
                  const SizedBox(width: AppTheme.spacingSm),
                  Expanded(
                    child: Text(
                      origin,
                      style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                  ),
                  const Icon(
                    Icons.arrow_forward,
                    size: 16,
                    color: AppTheme.textSecondary,
                  ),
                  const SizedBox(width: AppTheme.spacingSm),
                  Expanded(
                    child: Text(
                      destination,
                      style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        color: AppTheme.textPrimary,
                      ),
                      textAlign: TextAlign.end,
                    ),
                  ),
                  const Icon(
                    Icons.location_on,
                    size: 12,
                    color: AppTheme.primary,
                  ),
                ],
              ),
              const SizedBox(height: AppTheme.spacingSm),

              // Date and time
              if (date.isNotEmpty || time.isNotEmpty)
                Text(
                  '$date ${time.isNotEmpty ? '• $time' : ''}',
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textSecondary,
                  ),
                ),
              const SizedBox(height: AppTheme.spacingSm),

              // Bottom row
              Row(
                children: [
                  // Vehicle
                  if (vehicle.isNotEmpty) ...[
                    Icon(
                      Icons.directions_car,
                      size: 16,
                      color: AppTheme.textSecondary,
                    ),
                    const SizedBox(width: AppTheme.spacingXs),
                    Text(
                      vehicle,
                      style: const TextStyle(
                        fontSize: 14,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                    const SizedBox(width: AppTheme.spacingMd),
                  ],

                  // Seats
                  Icon(
                    Icons.person_outline,
                    size: 16,
                    color: AppTheme.textSecondary,
                  ),
                  const SizedBox(width: AppTheme.spacingXs),
                  Text(
                    '$seats seats',
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textSecondary,
                    ),
                  ),

                  const Spacer(),

                  // Price
                  Text(
                    '₹${price.toStringAsFixed(0)}',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primary,
                    ),
                  ),
                ],
              ),

              // Host info
              if (hostName.isNotEmpty) ...[
                const Divider(),
                Row(
                  children: [
                    CircleAvatar(
                      radius: 14,
                      backgroundColor:
                          AppTheme.primaryLight.withValues(alpha: 0.2),
                      child: const Icon(
                        Icons.person,
                        size: 16,
                        color: AppTheme.primary,
                      ),
                    ),
                    const SizedBox(width: AppTheme.spacingSm),
                    Text(
                      hostName,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(width: AppTheme.spacingSm),
                    if (hostRating > 0) ...[
                      const Icon(
                        Icons.star,
                        size: 14,
                        color: AppTheme.warning,
                      ),
                      const SizedBox(width: AppTheme.spacingXs),
                      Text(
                        hostRating.toStringAsFixed(1),
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppTheme.textSecondary,
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
