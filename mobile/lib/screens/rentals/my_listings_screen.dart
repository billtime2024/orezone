import 'package:flutter/material.dart';
import '../../models/rental_listing.dart';
import '../../services/rental_service.dart';
import 'rental_detail_screen.dart';

class MyListingsScreen extends StatefulWidget {
  const MyListingsScreen({super.key});

  @override
  State<MyListingsScreen> createState() => _MyListingsScreenState();
}

class _MyListingsScreenState extends State<MyListingsScreen> {
  final _service = RentalService();
  List<RentalListing> _listings = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadListings();
  }

  Future<void> _loadListings() async {
    setState(() => _isLoading = true);
    try {
      final result = await _service.getMyListings();
      setState(() {
        _listings = (result['data'] as List? ?? [])
            .map((json) => RentalListing.fromJson(json))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Listings'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              Navigator.pushNamed(context, '/rentals/create');
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _listings.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.home_work_outlined, size: 64, color: Colors.grey),
                      SizedBox(height: 16),
                      Text('No listings yet', style: TextStyle(fontSize: 18, color: Colors.grey)),
                      Text('Create your first rental listing', style: TextStyle(color: Colors.grey)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadListings,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _listings.length,
                    itemBuilder: (context, index) {
                      return _buildListingCard(_listings[index]);
                    },
                  ),
                ),
    );
  }

  Widget _buildListingCard(RentalListing listing) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
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
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              // Photo thumbnail
              if (listing.photos != null && listing.photos!.isNotEmpty)
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    listing.photos!.first,
                    width: 70,
                    height: 70,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 70,
                      height: 70,
                      color: Colors.grey[200],
                      child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 28))),
                    ),
                  ),
                )
              else
                Container(
                  width: 70,
                  height: 70,
                  decoration: BoxDecoration(color: Colors.grey[100], borderRadius: BorderRadius.circular(8)),
                  child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 28))),
                ),
              const SizedBox(width: 12),
              // Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      listing.title,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Text('${listing.city}, ${listing.state}', style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                        const SizedBox(width: 8),
                        Text(listing.formattedPrice, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.green)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    // Status badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: listing.status == 'active'
                            ? Colors.green[50]
                            : listing.status == 'paused'
                                ? Colors.orange[50]
                                : Colors.grey[200],
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        listing.status.toUpperCase(),
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: listing.status == 'active'
                              ? Colors.green[700]
                              : listing.status == 'paused'
                                  ? Colors.orange[700]
                                  : Colors.grey[700],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
