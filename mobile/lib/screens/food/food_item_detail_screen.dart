import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/food_item.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodItemDetailScreen extends StatefulWidget {
  final String itemSlug;
  const FoodItemDetailScreen({super.key, required this.itemSlug});

  @override
  State<FoodItemDetailScreen> createState() => _FoodItemDetailScreenState();
}

class _FoodItemDetailScreenState extends State<FoodItemDetailScreen> {
  final _service = FoodService();
  FoodItem? _item;
  bool _isLoading = true;
  int _quantity = 1;
  String? _selectedNotes;

  @override
  void initState() {
    super.initState();
    _loadItem();
  }

  Future<void> _loadItem() async {
    try {
      final item = await _service.getItemDetail(widget.itemSlug);
      if (mounted) {
        setState(() {
          _item = item;
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

  Future<void> _addToCart() async {
    if (_item == null) return;
    try {
      final result = await _service.addToCart(
        _item!.id,
        quantity: _quantity,
        notes: _selectedNotes,
      );
      if (result != null && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Added ${_item!.name} to cart'),
            backgroundColor: AppTheme.success,
            action: SnackBarAction(
              label: 'VIEW CART',
              textColor: Colors.white,
              onPressed: () => context.push('/food/cart'),
            ),
          ),
        );
        Navigator.pop(context);
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
        appBar: AppBar(title: const Text('Food Item')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final item = _item;
    if (item == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Food Item')),
        body: const Center(child: Text('Item not found')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(item.name),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Large image
            if (item.imageUrl != null)
              Image.network(
                item.imageUrl!,
                height: 250,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(
                  height: 250,
                  color: AppTheme.primary.withValues(alpha: 0.1),
                  child: const Center(
                      child: Text('🥬', style: TextStyle(fontSize: 64))),
                ),
              )
            else
              Container(
                height: 200,
                color: AppTheme.primary.withValues(alpha: 0.1),
                child: const Center(
                    child: Text('🥬', style: TextStyle(fontSize: 64))),
              ),

            Padding(
              padding: const EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Name and price
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(item.name,
                            style: const TextStyle(
                                fontSize: 22, fontWeight: FontWeight.bold)),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          if (item.hasDiscount)
                            Text('₹${item.price.toStringAsFixed(0)}',
                                style: const TextStyle(
                                    fontSize: 14,
                                    color: AppTheme.textLight,
                                    decoration: TextDecoration.lineThrough)),
                          Text(
                            '₹${item.effectivePrice.toStringAsFixed(0)}/${item.unit}',
                            style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primary),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Dietary badges
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      if (item.isVegan)
                        _buildDietaryBadge('🌱 Vegan', AppTheme.success),
                      if (item.isJain)
                        _buildDietaryBadge('🙏 Jain', AppTheme.accent),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Spice level
                  if (item.spiceLevel > 0) ...[
                    Row(
                      children: [
                        const Text('Spice Level: ',
                            style: TextStyle(fontSize: 14)),
                        ...List.generate(
                          5,
                          (i) => Icon(
                            i < item.spiceLevel
                                ? Icons.whatshot
                                : Icons.whatshot_outlined,
                            color: i < item.spiceLevel
                                ? Colors.orange
                                : AppTheme.textLight,
                            size: 20,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppTheme.spacingMd),
                  ],

                  // Description
                  if (item.description != null &&
                      item.description!.isNotEmpty) ...[
                    const Text('Description',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: AppTheme.spacingSm),
                    Text(item.description!,
                        style: const TextStyle(
                            fontSize: 14,
                            color: AppTheme.textSecondary,
                            height: 1.5)),
                    const SizedBox(height: AppTheme.spacingMd),
                  ],

                  // Allergens
                  if (item.allergens.isNotEmpty) ...[
                    const Text('Allergens',
                        style: TextStyle(
                            fontSize: 14, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Wrap(
                      spacing: 6,
                      children: item.allergens
                          .map((a) => Chip(
                                label: Text(a,
                                    style: const TextStyle(fontSize: 12)),
                                backgroundColor:
                                    AppTheme.error.withValues(alpha: 0.1),
                                labelStyle: const TextStyle(
                                    color: AppTheme.error),
                                materialTapTargetSize:
                                    MaterialTapTargetSize.shrinkWrap,
                                visualDensity: VisualDensity.compact,
                              ))
                          .toList(),
                    ),
                    const SizedBox(height: AppTheme.spacingMd),
                  ],

                  // Preparation time
                  if (item.preparationTimeMin != null)
                    Row(
                      children: [
                        const Icon(Icons.access_time,
                            size: 18, color: AppTheme.textSecondary),
                        const SizedBox(width: 8),
                        Text(
                            'Preparation: ~${item.preparationTimeMin} min',
                            style: const TextStyle(
                                fontSize: 14, color: AppTheme.textSecondary)),
                      ],
                    ),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Quantity selector
                  Row(
                    children: [
                      const Text('Quantity',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                      const Spacer(),
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: AppTheme.border),
                          borderRadius:
                              BorderRadius.circular(AppTheme.radiusSm),
                        ),
                        child: Row(
                          children: [
                            IconButton(
                              onPressed: _quantity > 1
                                  ? () => setState(() => _quantity--)
                                  : null,
                              icon: const Icon(Icons.remove),
                            ),
                            Text('$_quantity',
                                style: const TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.bold)),
                            IconButton(
                              onPressed: () =>
                                  setState(() => _quantity++),
                              icon: const Icon(Icons.add),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Special notes
                  TextField(
                    decoration: const InputDecoration(
                      hintText: 'Special notes (optional)',
                      border: OutlineInputBorder(),
                    ),
                    maxLines: 2,
                    onChanged: (val) => _selectedNotes = val,
                  ),
                  const SizedBox(height: AppTheme.spacingMd),

                  // Reviews placeholder
                  const Text('Reviews',
                      style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: AppTheme.spacingSm),
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Text('Reviews coming soon',
                          style: TextStyle(color: AppTheme.textLight)),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomSheet: Container(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.1),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: item.isAvailable ? _addToCart : null,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: AppTheme.primary,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(AppTheme.radiusMd)),
            ),
            child: Text(
              item.isAvailable
                  ? 'Add to Cart - ₹${(item.effectivePrice * _quantity).toStringAsFixed(0)}'
                  : 'Currently Unavailable',
              style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.white),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDietaryBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTheme.radiusSm),
      ),
      child: Text(label,
          style: TextStyle(
              fontSize: 14,
              color: color,
              fontWeight: FontWeight.w600)),
    );
  }
}
