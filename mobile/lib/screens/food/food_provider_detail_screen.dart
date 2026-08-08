import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/food_item.dart';
import '../../models/food_provider.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodProviderDetailScreen extends StatefulWidget {
  final String providerSlug;
  const FoodProviderDetailScreen({super.key, required this.providerSlug});

  @override
  State<FoodProviderDetailScreen> createState() =>
      _FoodProviderDetailScreenState();
}

class _FoodProviderDetailScreenState extends State<FoodProviderDetailScreen> {
  final _service = FoodService();
  FoodProvider? _provider;
  List<FoodItem> _menuItems = [];
  bool _isLoading = true;
  int _cartCount = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final provider = await _service.getProviderDetail(widget.providerSlug);
      if (provider != null) {
        final menu = await _service.getProviderMenu(provider.id);
        final cart = await _service.getCart();
        if (mounted) {
          setState(() {
            _provider = provider;
            _menuItems = menu;
            _cartCount = cart.length;
            _isLoading = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoading = false);
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

  Future<void> _addToCart(FoodItem item) async {
    try {
      final result = await _service.addToCart(item.id);
      if (result != null && mounted) {
        setState(() => _cartCount++);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${item.name} added to cart'),
            backgroundColor: AppTheme.success,
            action: SnackBarAction(
              label: 'VIEW CART',
              textColor: Colors.white,
              onPressed: () => context.push('/food/cart'),
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppTheme.error),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Provider')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final provider = _provider;
    if (provider == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Provider')),
        body: const Center(child: Text('Provider not found')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(provider.businessName),
        elevation: 0,
      ),
      body: ListView(
        children: [
          // Provider header
          Container(
            padding: const EdgeInsets.all(AppTheme.spacingMd),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(AppTheme.radiusMd),
                  child: provider.logoUrl != null
                      ? Image.network(
                          provider.logoUrl!,
                          width: 64,
                          height: 64,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 64,
                            height: 64,
                            color: AppTheme.primary.withValues(alpha: 0.1),
                            child: const Center(
                                child: Text('🏪', style: TextStyle(fontSize: 28))),
                          ),
                        )
                      : Container(
                          width: 64,
                          height: 64,
                          color: AppTheme.primary.withValues(alpha: 0.1),
                          child: const Center(
                              child: Text('🏪', style: TextStyle(fontSize: 28))),
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(provider.businessName,
                          style: const TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          if (provider.avgRating > 0) ...[
                            const Icon(Icons.star,
                                size: 16, color: Colors.amber),
                            const SizedBox(width: 4),
                            Text(
                                '${provider.avgRating.toStringAsFixed(1)} (${provider.totalOrders} orders)',
                                style: const TextStyle(fontSize: 13)),
                          ],
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(provider.providerType.toUpperCase(),
                          style: const TextStyle(
                              fontSize: 11,
                              color: AppTheme.textSecondary,
                              letterSpacing: 0.5)),
                    ],
                  ),
                ),
              ],
            ),
          ),

          if (provider.description != null &&
              provider.description!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(
                  horizontal: AppTheme.spacingMd),
              child: Text(provider.description!,
                  style: const TextStyle(
                      color: AppTheme.textSecondary, fontSize: 14)),
            ),
          const SizedBox(height: AppTheme.spacingMd),

          // Menu items
          Padding(
            padding:
                const EdgeInsets.symmetric(horizontal: AppTheme.spacingMd),
            child: Text('Menu',
                style: const TextStyle(
                    fontSize: 18, fontWeight: FontWeight.bold)),
          ),
          const SizedBox(height: AppTheme.spacingSm),
          if (_menuItems.isEmpty)
            const Padding(
              padding: EdgeInsets.all(32),
              child: Center(
                child: Text('No menu items available',
                    style: TextStyle(color: AppTheme.textSecondary)),
              ),
            )
          else
            ..._menuItems.map((item) => _buildMenuItem(item)),
          const SizedBox(height: 100), // Space for cart button
        ],
      ),
      floatingActionButton: _cartCount > 0
          ? FloatingActionButton.extended(
              onPressed: () => context.push('/food/cart'),
              backgroundColor: AppTheme.primary,
              icon: const Icon(Icons.shopping_cart, color: Colors.white),
              label: Text('Cart ($_cartCount)',
                  style: const TextStyle(
                      color: Colors.white, fontWeight: FontWeight.bold)),
            )
          : null,
    );
  }

  Widget _buildMenuItem(FoodItem item) {
    return Card(
      margin: const EdgeInsets.symmetric(
          horizontal: AppTheme.spacingMd, vertical: AppTheme.spacingXs),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        onTap: () => context.push('/food/item/${item.slug}'),
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          child: Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(AppTheme.radiusSm),
                child: item.imageUrl != null
                    ? Image.network(
                        item.imageUrl!,
                        width: 72,
                        height: 72,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          width: 72,
                          height: 72,
                          color: AppTheme.primary.withValues(alpha: 0.1),
                          child: const Center(
                              child: Text('🥬', style: TextStyle(fontSize: 24))),
                        ),
                      )
                    : Container(
                        width: 72,
                        height: 72,
                        color: AppTheme.primary.withValues(alpha: 0.1),
                        child: const Center(
                            child: Text('🥬', style: TextStyle(fontSize: 24))),
                      ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(item.name,
                              style: const TextStyle(
                                  fontSize: 15, fontWeight: FontWeight.w600)),
                        ),
                        if (item.isVegan)
                          const Icon(Icons.eco, size: 14, color: AppTheme.success),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (item.isJain)
                          _smallBadge('Jain', AppTheme.accent),
                        if (item.isVegan)
                          _smallBadge('Vegan', AppTheme.success),
                        if (item.spiceLevel > 0) ...[
                          const SizedBox(width: 4),
                          Text('🌶️' * item.spiceLevel.clamp(1, 5),
                              style: const TextStyle(fontSize: 10)),
                        ],
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (item.hasDiscount)
                              Text('₹${item.price.toStringAsFixed(0)}',
                                  style: const TextStyle(
                                      fontSize: 12,
                                      color: AppTheme.textLight,
                                      decoration:
                                          TextDecoration.lineThrough)),
                            Text(
                              '₹${item.effectivePrice.toStringAsFixed(0)}/${item.unit}',
                              style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primary),
                            ),
                          ],
                        ),
                        if (item.isAvailable)
                          IconButton(
                            onPressed: () => _addToCart(item),
                            icon: const Icon(Icons.add_circle,
                                color: AppTheme.primary, size: 32),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _smallBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(3),
      ),
      child: Text(label,
          style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w500)),
    );
  }
}
