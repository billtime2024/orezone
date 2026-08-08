import 'package:flutter/material.dart';
import '../../models/rental_listing.dart';
import '../../services/rental_service.dart';
import '../../services/api_client.dart';
import 'rental_booking_screen.dart';

class RentalDetailScreen extends StatefulWidget {
  final int listingId;
  const RentalDetailScreen({super.key, required this.listingId});

  @override
  State<RentalDetailScreen> createState() => _RentalDetailScreenState();
}

class _RentalDetailScreenState extends State<RentalDetailScreen> {
  final _service = RentalService();
  final _apiClient = ApiClient();
  RentalListing? _listing;
  bool _isLoading = true;
  bool _isOwner = false;
  int? _currentUserId;

  // Calendar state
  List<dynamic> _calendarDays = [];
  bool _calendarLoading = false;
  DateTime _calendarMonth = DateTime.now();

  // Reviews state
  List<Map<String, dynamic>> _reviews = [];
  bool _reviewsLoading = false;
  int _reviewsPage = 1;
  bool _hasMoreReviews = true;

  @override
  void initState() {
    super.initState();
    _loadListing();
    _loadUserProfile();
  }

  Future<void> _loadUserProfile() async {
    try {
      final response = await _apiClient.getProfile();
      if (response.success && mounted) {
        final data = response.asMap;
        setState(() {
          _currentUserId = data['id'] as int?;
        });
        _checkOwnership();
      }
    } catch (e) {
      // Silently fail - owner actions just won't show
    }
  }

  void _checkOwnership() {
    if (_listing != null && _currentUserId != null) {
      setState(() {
        _isOwner = _listing!.userId == _currentUserId;
      });
    }
  }

  Future<void> _loadListing() async {
    try {
      final result = await _service.getListing(widget.listingId);
      setState(() {
        _listing = RentalListing.fromJson(result);
        _isLoading = false;
      });
      _checkOwnership();
      _loadCalendar();
      _loadReviews();
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _loadCalendar() async {
    if (_listing == null) return;
    setState(() => _calendarLoading = true);
    try {
      final monthStr = '${_calendarMonth.year}-${_calendarMonth.month.toString().padLeft(2, '0')}';
      final result = await _service.getCalendar(_listing!.id, month: monthStr);
      setState(() {
        _calendarDays = (result['data'] as List?) ?? [];
        _calendarLoading = false;
      });
    } catch (e) {
      setState(() => _calendarLoading = false);
    }
  }

  Future<void> _loadReviews() async {
    if (_listing == null) return;
    setState(() => _reviewsLoading = true);
    try {
      final result = await _service.getReviews(_listing!.id, page: _reviewsPage);
      final data = result['data'] as List? ?? [];
      final meta = result['meta'] as Map<String, dynamic>?;
      setState(() {
        if (_reviewsPage == 1) {
          _reviews = List<Map<String, dynamic>>.from(data);
        } else {
          _reviews.addAll(List<Map<String, dynamic>>.from(data));
        }
        _hasMoreReviews = meta != null && _reviewsPage < (meta['last_page'] ?? 1);
        _reviewsLoading = false;
      });
    } catch (e) {
      setState(() => _reviewsLoading = false);
    }
  }

  Future<void> _toggleStatus() async {
    if (_listing == null) return;
    try {
      await _service.toggleStatus(_listing!.id);
      _loadListing();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Status updated'), backgroundColor: Colors.green),
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

  Future<void> _deleteListing() async {
    if (_listing == null) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Listing'),
        content: const Text('Are you sure you want to delete this listing? This action cannot be undone.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await _service.deleteListing(_listing!.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listing deleted'), backgroundColor: Colors.green),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Listing')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final listing = _listing;
    if (listing == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Listing')),
        body: const Center(child: Text('Listing not found')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(listing.title),
        elevation: 0,
        actions: [
          if (_isOwner) ...[
            IconButton(
              icon: Icon(
                listing.status == 'active' ? Icons.pause_circle : Icons.play_circle,
                color: listing.status == 'active' ? Colors.orange : Colors.green,
              ),
              tooltip: listing.status == 'active' ? 'Pause Listing' : 'Activate Listing',
              onPressed: _toggleStatus,
            ),
            PopupMenuButton<String>(
              onSelected: (value) {
                if (value == 'edit') {
                  // TODO: Navigate to edit screen
                } else if (value == 'delete') {
                  _deleteListing();
                }
              },
              itemBuilder: (context) => [
                const PopupMenuItem(value: 'edit', child: Text('Edit Listing')),
                const PopupMenuItem(
                  value: 'delete',
                  child: Text('Delete Listing', style: TextStyle(color: Colors.red)),
                ),
              ],
            ),
          ],
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Photos
            if (listing.photos != null && listing.photos!.isNotEmpty)
              SizedBox(
                height: 250,
                child: PageView.builder(
                  itemCount: listing.photos!.length,
                  itemBuilder: (context, index) {
                    return Image.network(
                      listing.photos![index],
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: Colors.grey[200],
                        child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 64))),
                      ),
                    );
                  },
                ),
              )
            else
              Container(
                height: 200,
                color: Colors.grey[100],
                child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 64))),
              ),

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Type badge + price + status
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.blue[50],
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text('${listing.typeIcon} ${listing.typeLabel}',
                            style: TextStyle(fontWeight: FontWeight.w600, color: Colors.blue[700])),
                      ),
                      Row(
                        children: [
                          if (listing.status != 'active')
                            Container(
                              margin: const EdgeInsets.only(right: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: listing.status == 'paused' ? Colors.orange[50] : Colors.grey[200],
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                listing.status.toUpperCase(),
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: listing.status == 'paused' ? Colors.orange : Colors.grey,
                                ),
                              ),
                            ),
                          Text(
                            listing.formattedPrice,
                            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.green),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Title
                  Text(listing.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),

                  // Location
                  Row(
                    children: [
                      Icon(Icons.location_on, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${listing.addressLine1}, ${listing.city}, ${listing.state} - ${listing.pincode}',
                          style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Rating + Bookings
                  Row(
                    children: [
                      if (listing.avgRating > 0) ...[
                        const Icon(Icons.star, color: Colors.amber, size: 18),
                        const SizedBox(width: 4),
                        Text('${listing.avgRating.toStringAsFixed(1)} (${listing.reviewCount} reviews)',
                            style: const TextStyle(fontSize: 14)),
                        const SizedBox(width: 16),
                      ],
                      Icon(Icons.calendar_today, size: 16, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Text('${listing.totalBookings} bookings', style: TextStyle(fontSize: 14, color: Colors.grey[600])),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Description
                  if (listing.description != null) ...[
                    const Text('Description', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(listing.description!, style: TextStyle(fontSize: 14, color: Colors.grey[700], height: 1.5)),
                    const SizedBox(height: 16),
                  ],

                  // Type-specific details
                  _buildDetailsSection(listing),
                  const SizedBox(height: 16),

                  // Rules
                  if (listing.rules != null && listing.rules!.isNotEmpty) ...[
                    const Text('House Rules', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    ...listing.rules!.map((rule) => Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle_outline, size: 16, color: Colors.green),
                          const SizedBox(width: 8),
                          Expanded(child: Text(rule, style: const TextStyle(fontSize: 14))),
                        ],
                      ),
                    )),
                    const SizedBox(height: 16),
                  ],

                  // Calendar / Availability
                  _buildCalendarSection(),
                  const SizedBox(height: 16),

                  // Pricing breakdown
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Pricing', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                          const Divider(),
                          _buildPriceRow('Price per ${listing.priceUnit}', '₹${listing.pricePerUnit.toStringAsFixed(0)}'),
                          if (listing.cleaningFee > 0)
                            _buildPriceRow('Cleaning Fee', '₹${listing.cleaningFee.toStringAsFixed(0)}'),
                          if (listing.securityDeposit > 0)
                            _buildPriceRow('Security Deposit', '₹${listing.securityDeposit.toStringAsFixed(0)}'),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Reviews
                  _buildReviewsSection(),
                  const SizedBox(height: 100), // Space for bottom button
                ],
              ),
            ),
          ],
        ),
      ),
      bottomSheet: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 10, offset: const Offset(0, -2))],
        ),
        child: SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: listing.status == 'active' && !_isOwner
                ? () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => RentalBookingScreen(listing: listing),
                      ),
                    );
                  }
                : null,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: Text(
              _isOwner
                  ? 'Your Listing'
                  : listing.status == 'active'
                      ? 'Book Now'
                      : 'Not Available',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDetailsSection(RentalListing listing) {
    final details = listing.details;
    if (details == null) return const SizedBox.shrink();

    List<Widget> chips = [];

    switch (listing.rentalType) {
      case 'house':
        if (details['bedrooms'] != null) chips.add(_buildDetailChip('🛏️ ${details['bedrooms']} Beds'));
        if (details['bathrooms'] != null) chips.add(_buildDetailChip('🚿 ${details['bathrooms']} Baths'));
        if (details['furnished'] == true) chips.add(_buildDetailChip('🛋️ Furnished'));
        if (details['parking'] == true) chips.add(_buildDetailChip('🅿️ Parking'));
        if (details['ac'] == true) chips.add(_buildDetailChip('❄️ AC'));
        if (details['wifi'] == true) chips.add(_buildDetailChip('📶 WiFi'));
        if (details['area_sqft'] != null) chips.add(_buildDetailChip('📐 ${details['area_sqft']} sqft'));
        break;
      case 'car':
        if (details['make'] != null) chips.add(_buildDetailChip('${details['make']} ${details['model']}'));
        if (details['year'] != null) chips.add(_buildDetailChip('📅 ${details['year']}'));
        if (details['fuel_type'] != null) chips.add(_buildDetailChip('⛽ ${details['fuel_type']}'));
        if (details['transmission'] != null) chips.add(_buildDetailChip('🔄 ${details['transmission']}'));
        if (details['seats'] != null) chips.add(_buildDetailChip('💺 ${details['seats']} Seats'));
        if (details['self_drive'] == true) chips.add(_buildDetailChip('Self Drive'));
        break;
      case 'commercial':
        if (details['property_type'] != null) chips.add(_buildDetailChip(details['property_type']));
        if (details['area_sqft'] != null) chips.add(_buildDetailChip('📐 ${details['area_sqft']} sqft'));
        if (details['furnished'] == true) chips.add(_buildDetailChip('🛋️ Furnished'));
        if (details['parking'] == true) chips.add(_buildDetailChip('🅿️ Parking'));
        break;
      case 'room':
        if (details['room_type'] != null) chips.add(_buildDetailChip(details['room_type']));
        if (details['stay_type'] != null) chips.add(_buildDetailChip(details['stay_type']));
        if (details['meals_included'] == true) chips.add(_buildDetailChip('🍽️ Meals Included'));
        if (details['ac'] == true) chips.add(_buildDetailChip('❄️ AC'));
        if (details['wifi'] == true) chips.add(_buildDetailChip('📶 WiFi'));
        break;
    }

    if (chips.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Details', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        Wrap(spacing: 8, runSpacing: 8, children: chips),
      ],
    );
  }

  Widget _buildCalendarSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Availability', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.chevron_left, size: 20),
                      onPressed: () {
                        setState(() {
                          _calendarMonth = DateTime(_calendarMonth.year, _calendarMonth.month - 1);
                        });
                        _loadCalendar();
                      },
                    ),
                    Text(
                      '${_calendarMonth.month.toString().padLeft(2, '0')}/${_calendarMonth.year}',
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    IconButton(
                      icon: const Icon(Icons.chevron_right, size: 20),
                      onPressed: () {
                        setState(() {
                          _calendarMonth = DateTime(_calendarMonth.year, _calendarMonth.month + 1);
                        });
                        _loadCalendar();
                      },
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (_calendarLoading)
              const Center(child: CircularProgressIndicator())
            else if (_calendarDays.isEmpty)
              const Center(
                child: Text('No availability data', style: TextStyle(color: Colors.grey)),
              )
            else ...[
              // Legend
              Row(
                children: [
                  _buildLegendDot(Colors.green, 'Available'),
                  const SizedBox(width: 12),
                  _buildLegendDot(Colors.red, 'Booked'),
                  const SizedBox(width: 12),
                  _buildLegendDot(Colors.grey, 'Blocked'),
                ],
              ),
              const SizedBox(height: 8),
              // Calendar grid
              _buildCalendarGrid(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildLegendDot(Color color, String label) {
    return Row(
      children: [
        Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    );
  }

  Widget _buildCalendarGrid() {
    final firstDay = DateTime(_calendarMonth.year, _calendarMonth.month, 1);
    final lastDay = DateTime(_calendarMonth.year, _calendarMonth.month + 1, 0);
    final startWeekday = firstDay.weekday % 7; // Sunday = 0

    return Column(
      children: [
        // Weekday headers
        Row(
          children: ['S', 'M', 'T', 'W', 'T', 'F', 'S'].map((d) {
            return Expanded(
              child: Center(
                child: Text(d, style: TextStyle(fontSize: 12, color: Colors.grey[600], fontWeight: FontWeight.w600)),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: 4),
        // Days
        for (var week = 0; week < ((startWeekday + lastDay.day + 6) / 7).ceil(); week++)
          Row(
            children: List.generate(7, (dayIndex) {
              final dayNum = week * 7 + dayIndex - startWeekday + 1;
              if (dayNum < 1 || dayNum > lastDay.day) {
                return const Expanded(child: SizedBox(height: 36));
              }
              final dateStr = '${_calendarMonth.year}-${_calendarMonth.month.toString().padLeft(2, '0')}-${dayNum.toString().padLeft(2, '0')}';
              final dayData = _calendarDays.where((d) => d['date'] == dateStr).firstOrNull;
              final isAvailable = dayData?['available'] == true;
              final isBooked = dayData?['status'] == 'booked';
              final isBlocked = dayData?['status'] == 'blocked' || dayData?['blocked'] == true;

              Color bgColor;
              if (isBooked) {
                bgColor = Colors.red[100]!;
              } else if (isBlocked) {
                bgColor = Colors.grey[300]!;
              } else if (isAvailable) {
                bgColor = Colors.green[50]!;
              } else {
                bgColor = Colors.transparent;
              }

              return Expanded(
                child: Container(
                  height: 36,
                  margin: const EdgeInsets.all(1),
                  decoration: BoxDecoration(
                    color: bgColor,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Center(
                    child: Text(
                      '$dayNum',
                      style: TextStyle(
                        fontSize: 12,
                        color: isBooked ? Colors.red[700] : isBlocked ? Colors.grey[600] : Colors.black87,
                        fontWeight: isAvailable ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ),
                ),
              );
            }),
          ),
      ],
    );
  }

  Widget _buildReviewsSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Reviews (${_reviews.length})',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                if (_listing != null && _listing!.avgRating > 0)
                  Row(
                    children: [
                      const Icon(Icons.star, color: Colors.amber, size: 18),
                      const SizedBox(width: 4),
                      Text(
                        _listing!.avgRating.toStringAsFixed(1),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 12),
            if (_reviewsLoading && _reviews.isEmpty)
              const Center(child: CircularProgressIndicator())
            else if (_reviews.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: Text('No reviews yet', style: TextStyle(color: Colors.grey)),
                ),
              )
            else ...[
              ..._reviews.map((review) => _buildReviewCard(review)),
              if (_hasMoreReviews)
                Center(
                  child: TextButton(
                    onPressed: () {
                      _reviewsPage++;
                      _loadReviews();
                    },
                    child: _reviewsLoading
                        ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Text('Load More Reviews'),
                  ),
                ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildReviewCard(Map<String, dynamic> review) {
    final user = review['user'] as Map<String, dynamic>?;
    final rating = review['rating'] as int? ?? 0;
    final comment = review['comment'] as String?;
    final createdAt = review['created_at'] as String?;

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: Colors.blue[100],
                child: Text(
                  (user?['name'] as String? ?? 'U')[0].toUpperCase(),
                  style: TextStyle(fontSize: 14, color: Colors.blue[700]),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(user?['name'] ?? 'User', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    if (createdAt != null)
                      Text(createdAt.split('T').first, style: TextStyle(fontSize: 11, color: Colors.grey[500])),
                  ],
                ),
              ),
              Row(
                children: List.generate(5, (i) {
                  return Icon(
                    i < rating ? Icons.star : Icons.star_border,
                    size: 14,
                    color: Colors.amber,
                  );
                }),
              ),
            ],
          ),
          if (comment != null && comment.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(comment, style: TextStyle(fontSize: 13, color: Colors.grey[700])),
          ],
          const Divider(height: 20),
        ],
      ),
    );
  }

  Widget _buildDetailChip(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(label, style: const TextStyle(fontSize: 13)),
    );
  }

  Widget _buildPriceRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600])),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
