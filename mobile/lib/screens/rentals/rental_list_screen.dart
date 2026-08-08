import 'package:flutter/material.dart';
import '../../models/rental_listing.dart';
import '../../services/rental_service.dart';
import 'rental_detail_screen.dart';

class RentalListScreen extends StatefulWidget {
  const RentalListScreen({super.key});

  @override
  State<RentalListScreen> createState() => _RentalListScreenState();
}

class _RentalListScreenState extends State<RentalListScreen> {
  final _service = RentalService();
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();
  List<RentalListing> _listings = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  int _currentPage = 1;
  int _lastPage = 1;
  String? _selectedType;
  String? _selectedCity;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadListings();
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      if (!_isLoadingMore && _currentPage < _lastPage) {
        _loadMore();
      }
    }
  }

  Future<void> _loadListings({int page = 1, bool append = false}) async {
    if (!append) setState(() => _isLoading = true);
    if (append) setState(() => _isLoadingMore = true);
    try {
      final result = await _service.searchListings(
        rentalType: _selectedType,
        city: _selectedCity,
        search: _searchController.text.isNotEmpty ? _searchController.text : null,
        page: page,
      );
      final meta = result['meta'] as Map<String, dynamic>?;
      setState(() {
        if (append) {
          _listings.addAll(
            (result['data'] as List? ?? []).map((json) => RentalListing.fromJson(json)).toList(),
          );
        } else {
          _listings = (result['data'] as List? ?? [])
              .map((json) => RentalListing.fromJson(json))
              .toList();
        }
        _currentPage = meta?['current_page'] as int? ?? page;
        _lastPage = meta?['last_page'] as int? ?? 1;
        _isLoading = false;
        _isLoadingMore = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _isLoadingMore = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _loadMore() async {
    await _loadListings(page: _currentPage + 1, append: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rentals'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Search & Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Theme.of(context).scaffoldBackgroundColor,
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search by city, title...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: () {
                        _searchController.clear();
                        _loadListings();
                      },
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                  ),
                  onSubmitted: (_) => _loadListings(),
                ),
                const SizedBox(height: 12),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('All', null),
                      _buildFilterChip('🏠 House', 'house'),
                      _buildFilterChip('🚗 Car', 'car'),
                      _buildFilterChip('🏢 Commercial', 'commercial'),
                      _buildFilterChip('🛏️ Room', 'room'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Listings
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _listings.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.home_work_outlined, size: 64, color: Colors.grey),
                            SizedBox(height: 16),
                            Text('No listings found', style: TextStyle(fontSize: 18, color: Colors.grey)),
                            Text('Try adjusting your filters', style: TextStyle(color: Colors.grey)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => _loadListings(),
                        child: ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.all(16),
                          itemCount: _listings.length + (_isLoadingMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index == _listings.length) {
                              return const Padding(
                                padding: EdgeInsets.all(16),
                                child: Center(child: CircularProgressIndicator()),
                              );
                            }
                            final listing = _listings[index];
                            return _buildListingCard(listing);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String? type) {
    final isSelected = _selectedType == type;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() => _selectedType = selected ? type : null);
          _loadListings();
        },
        selectedColor: Theme.of(context).colorScheme.primaryContainer,
      ),
    );
  }

  Widget _buildListingCard(RentalListing listing) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => RentalDetailScreen(listingId: listing.id),
            ),
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Photo
            if (listing.photos != null && listing.photos!.isNotEmpty)
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                child: Image.network(
                  listing.photos!.first,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    height: 180,
                    color: Colors.grey[200],
                    child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 48))),
                  ),
                ),
              )
            else
              Container(
                height: 120,
                color: Colors.grey[100],
                child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 48))),
              ),

            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Type badge + status
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue[50],
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(listing.typeLabel, style: TextStyle(fontSize: 12, color: Colors.blue[700])),
                      ),
                      const SizedBox(width: 8),
                      if (listing.instantBooking)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.green[50],
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text('Instant', style: TextStyle(fontSize: 12, color: Colors.green[700])),
                        ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Title
                  Text(
                    listing.title,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),

                  // Location
                  Row(
                    children: [
                      Icon(Icons.location_on, size: 14, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Text(
                        '${listing.city}, ${listing.state}',
                        style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Price + Rating
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        listing.formattedPrice,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.green),
                      ),
                      if (listing.avgRating > 0)
                        Row(
                          children: [
                            const Icon(Icons.star, size: 16, color: Colors.amber),
                            const SizedBox(width: 4),
                            Text(
                              '${listing.avgRating.toStringAsFixed(1)} (${listing.reviewCount})',
                              style: const TextStyle(fontSize: 13),
                            ),
                          ],
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
