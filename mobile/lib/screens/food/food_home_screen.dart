import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/food_item.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodHomeScreen extends StatefulWidget {
  const FoodHomeScreen({super.key});

  @override
  State<FoodHomeScreen> createState() => _FoodHomeScreenState();
}

class _FoodHomeScreenState extends State<FoodHomeScreen> {
  final _service = FoodService();
  List<FoodCategory> _categories = [];
  List<FoodItem> _featuredItems = [];
  List<FoodItem> _nearbyItems = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final results = await Future.wait([
        _service.getCategories(),
        _service.getFeaturedItems(),
        _service.searchItems(),
      ]);
      if (mounted) {
        setState(() {
          _categories = results[0] as List<FoodCategory>;
          _featuredItems = results[1] as List<FoodItem>;
          _nearbyItems = results[2] as List<FoodItem>;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
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
        title: const Text('Food Services'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.shopping_cart_outlined),
            onPressed: () => context.push('/food/cart'),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(AppTheme.spacingMd),
                children: [
                  // Search bar
                  _buildSearchBar(),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Categories
                  if (_categories.isNotEmpty) ...[
                    const Text('Categories',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: AppTheme.spacingSm),
                    SizedBox(
                      height: 90,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: _categories.length,
                        itemBuilder: (context, index) {
                          return _buildCategoryCard(_categories[index]);
                        },
                      ),
                    ),
                    const SizedBox(height: AppTheme.spacingLg),
                  ],

                  // Featured items
                  if (_featuredItems.isNotEmpty) ...[
                    const Text('Featured',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: AppTheme.spacingSm),
                    SizedBox(
                      height: 200,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: _featuredItems.length,
                        itemBuilder: (context, index) {
                          return _buildFeaturedCard(_featuredItems[index]);
                        },
                      ),
                    ),
                    const SizedBox(height: AppTheme.spacingLg),
                  ],

                  // Nearby items
                  const Text('Near You',
                      style: TextStyle(
                          fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: AppTheme.spacingSm),
                  if (_nearbyItems.isEmpty)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(32),
                        child: Text('No food items available nearby',
                            style: TextStyle(color: AppTheme.textSecondary)),
                      ),
                    )
                  else
                    ..._nearbyItems.map((item) => _buildNearbyItem(item)),
                ],
              ),
            ),
    );
  }

  Widget _buildSearchBar() {
    return GestureDetector(
      onTap: () => context.push('/food/search'),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(AppTheme.radiusMd),
          border: Border.all(color: AppTheme.border),
        ),
        child: const Row(
          children: [
            Icon(Icons.search, color: AppTheme.textLight),
            SizedBox(width: 12),
            Text('Search for food, snacks, meals...',
                style: TextStyle(color: AppTheme.textLight)),
          ],
        ),
      ),
    );
  }

  Widget _buildCategoryCard(FoodCategory category) {
    return GestureDetector(
      onTap: () => context.push('/food/search?category=${category.id}'),
      child: Container(
        width: 80,
        margin: const EdgeInsets.only(right: AppTheme.spacingSm),
        child: Column(
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: AppTheme.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(AppTheme.radiusMd),
              ),
              child: Center(
                child: Text(
                  category.icon ?? '🍽️',
                  style: const TextStyle(fontSize: 28),
                ),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              category.name,
              style: const TextStyle(fontSize: 12),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFeaturedCard(FoodItem item) {
    return GestureDetector(
      onTap: () => context.push('/food/item/${item.slug}'),
      child: Container(
        width: 160,
        margin: const EdgeInsets.only(right: AppTheme.spacingSm),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(AppTheme.radiusMd),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(AppTheme.radiusMd)),
              child: item.imageUrl != null
                  ? Image.network(
                      item.imageUrl!,
                      height: 110,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        height: 110,
                        color: AppTheme.primary.withValues(alpha: 0.1),
                        child: const Center(
                            child: Text('🥬', style: TextStyle(fontSize: 36))),
                      ),
                    )
                  : Container(
                      height: 110,
                      color: AppTheme.primary.withValues(alpha: 0.1),
                      child: const Center(
                          child: Text('🥬', style: TextStyle(fontSize: 36))),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: const TextStyle(
                        fontSize: 14, fontWeight: FontWeight.w600),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      if (item.isVegan)
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 4, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppTheme.success.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text('Veg',
                              style: TextStyle(
                                  fontSize: 10, color: AppTheme.success)),
                        ),
                      if (item.isVegan) const SizedBox(width: 4),
                      if (item.avgRating > 0) ...[
                        const Icon(Icons.star, size: 12, color: Colors.amber),
                        const SizedBox(width: 2),
                        Text('${item.avgRating.toStringAsFixed(1)}',
                            style: const TextStyle(fontSize: 12)),
                      ],
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '₹${item.effectivePrice.toStringAsFixed(0)}/${item.unit}',
                    style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNearbyItem(FoodItem item) {
    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingSm),
      child: ListTile(
        leading: ClipRRect(
          borderRadius: BorderRadius.circular(AppTheme.radiusSm),
          child: item.imageUrl != null
              ? Image.network(
                  item.imageUrl!,
                  width: 56,
                  height: 56,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    width: 56,
                    height: 56,
                    color: AppTheme.primary.withValues(alpha: 0.1),
                    child: const Center(
                        child: Text('🥬', style: TextStyle(fontSize: 24))),
                  ),
                )
              : Container(
                  width: 56,
                  height: 56,
                  color: AppTheme.primary.withValues(alpha: 0.1),
                  child: const Center(
                      child: Text('🥬', style: TextStyle(fontSize: 24))),
                ),
        ),
        title: Text(item.name,
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Row(
          children: [
            if (item.isVegan)
              const Text('🌱 Veg ',
                  style: TextStyle(fontSize: 12, color: AppTheme.success)),
            if (item.avgRating > 0) ...[
              const Icon(Icons.star, size: 12, color: Colors.amber),
              Text(' ${item.avgRating.toStringAsFixed(1)}',
                  style: const TextStyle(fontSize: 12)),
            ],
          ],
        ),
        trailing: Text(
          '₹${item.effectivePrice.toStringAsFixed(0)}',
          style: const TextStyle(
              fontWeight: FontWeight.bold, color: AppTheme.primary),
        ),
        onTap: () => context.push('/food/item/${item.slug}'),
      ),
    );
  }
}
