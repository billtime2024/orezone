import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/food_item.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodSearchScreen extends StatefulWidget {
  const FoodSearchScreen({super.key});

  @override
  State<FoodSearchScreen> createState() => _FoodSearchScreenState();
}

class _FoodSearchScreenState extends State<FoodSearchScreen> {
  final _service = FoodService();
  final _searchController = TextEditingController();
  Timer? _debounce;
  List<FoodItem> _results = [];
  bool _isLoading = false;
  bool _hasSearched = false;

  String? _selectedDietary;
  String _sortBy = 'rating';
  double? _minPrice;
  double? _maxPrice;

  final _dietaryOptions = const [
    ('Jain', 'jain'),
    ('Vegan', 'vegan'),
    ('Gluten-Free', 'gluten_free'),
  ];

  final _sortOptions = const [
    ('Rating', 'rating'),
    ('Price: Low to High', 'price_asc'),
    ('Price: High to Low', 'price_desc'),
    ('Distance', 'distance'),
  ];

  @override
  void dispose() {
    _searchController.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _onSearchChanged(String query) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 500), () {
      _search(query: query);
    });
  }

  Future<void> _search({String? query}) async {
    final q = query ?? _searchController.text;
    if (q.isEmpty && _selectedDietary == null) return;

    setState(() => _isLoading = true);
    try {
      final results = await _service.searchItems(
        query: q.isNotEmpty ? q : null,
        dietary: _selectedDietary,
        minPrice: _minPrice,
        maxPrice: _maxPrice,
      );
      if (mounted) {
        setState(() {
          _results = results;
          _isLoading = false;
          _hasSearched = true;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasSearched = true;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: TextField(
          controller: _searchController,
          autofocus: true,
          style: const TextStyle(color: Colors.white),
          decoration: const InputDecoration(
            hintText: 'Search food items...',
            hintStyle: TextStyle(color: Colors.white70),
            border: InputBorder.none,
          ),
          onChanged: _onSearchChanged,
          onSubmitted: (_) => _search(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune),
            onPressed: _showSortSheet,
          ),
        ],
      ),
      body: Column(
        children: [
          // Dietary filter chips
          Container(
            padding: const EdgeInsets.symmetric(
                horizontal: AppTheme.spacingMd, vertical: AppTheme.spacingSm),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildDietaryChip('All', null),
                  ..._dietaryOptions.map(
                    (opt) => _buildDietaryChip(opt.$1, opt.$2),
                  ),
                ],
              ),
            ),
          ),

          // Results
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : !_hasSearched
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.search, size: 64, color: AppTheme.textLight),
                            SizedBox(height: 16),
                            Text('Search for your favourite food',
                                style: TextStyle(color: AppTheme.textSecondary)),
                          ],
                        ),
                      )
                    : _results.isEmpty
                        ? const Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.no_meals_outlined,
                                    size: 64, color: AppTheme.textLight),
                                SizedBox(height: 16),
                                Text('No items found',
                                    style: TextStyle(
                                        fontSize: 18,
                                        color: AppTheme.textSecondary)),
                                SizedBox(height: 8),
                                Text('Try a different search or filter',
                                    style: TextStyle(color: AppTheme.textLight)),
                              ],
                            ),
                          )
                        : ListView.builder(
                            padding:
                                const EdgeInsets.all(AppTheme.spacingMd),
                            itemCount: _results.length,
                            itemBuilder: (context, index) {
                              return _buildResultCard(_results[index]);
                            },
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildDietaryChip(String label, String? value) {
    final isSelected = _selectedDietary == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() => _selectedDietary = selected ? value : null);
          _search();
        },
        selectedColor: AppTheme.primary.withValues(alpha: 0.15),
        checkmarkColor: AppTheme.primary,
      ),
    );
  }

  Widget _buildResultCard(FoodItem item) {
    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingSm),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        onTap: () => context.push('/food/item/${item.slug}'),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.horizontal(
                  left: Radius.circular(AppTheme.radiusMd)),
              child: item.imageUrl != null
                  ? Image.network(
                      item.imageUrl!,
                      width: 100,
                      height: 100,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        width: 100,
                        height: 100,
                        color: AppTheme.primary.withValues(alpha: 0.1),
                        child: const Center(
                            child: Text('🥬', style: TextStyle(fontSize: 32))),
                      ),
                    )
                  : Container(
                      width: 100,
                      height: 100,
                      color: AppTheme.primary.withValues(alpha: 0.1),
                      child: const Center(
                          child: Text('🥬', style: TextStyle(fontSize: 32))),
                    ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(AppTheme.spacingMd),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(item.name,
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (item.isJain)
                          _buildBadge('Jain', AppTheme.accent),
                        if (item.isVegan)
                          _buildBadge('Vegan', AppTheme.success),
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
                        if (item.hasDiscount) ...[
                          Text('₹${item.price.toStringAsFixed(0)}',
                              style: const TextStyle(
                                  fontSize: 13,
                                  color: AppTheme.textLight,
                                  decoration: TextDecoration.lineThrough)),
                          const SizedBox(width: 4),
                        ],
                        Text(
                          '₹${item.effectivePrice.toStringAsFixed(0)}/${item.unit}',
                          style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary),
                        ),
                        if (item.avgRating > 0)
                          Row(
                            children: [
                              const Icon(Icons.star,
                                  size: 14, color: Colors.amber),
                              Text('${item.avgRating.toStringAsFixed(1)}',
                                  style: const TextStyle(fontSize: 13)),
                            ],
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(label,
          style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w500)),
    );
  }

  void _showSortSheet() {
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Padding(
                padding: EdgeInsets.all(16),
                child: Text('Sort & Filter',
                    style:
                        TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
              ..._sortOptions.map((opt) {
                return RadioListTile<String>(
                  title: Text(opt.$1),
                  value: opt.$2,
                  groupValue: _sortBy,
                  onChanged: (val) {
                    setState(() => _sortBy = val!);
                    Navigator.pop(context);
                    _search();
                  },
                  activeColor: AppTheme.primary,
                );
              }),
              const SizedBox(height: 16),
            ],
          ),
        );
      },
    );
  }
}
