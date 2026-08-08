import 'package:flutter/material.dart';
import '../../../models/food_item.dart';
import '../../../services/food_service.dart';
import '../../../theme/app_theme.dart';

class ProviderMenuScreen extends StatefulWidget {
  const ProviderMenuScreen({super.key});

  @override
  State<ProviderMenuScreen> createState() => _ProviderMenuScreenState();
}

class _ProviderMenuScreenState extends State<ProviderMenuScreen> {
  final _service = FoodService();
  List<FoodItem> _menuItems = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadMenu();
  }

  Future<void> _loadMenu() async {
    setState(() => _isLoading = true);
    try {
      final items = await _service.getProviderMenuItems();
      if (mounted) {
        setState(() {
          _menuItems = items;
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

  Future<void> _toggleAvailability(FoodItem item) async {
    try {
      final updated = await _service.toggleItemAvailability(item.id);
      if (updated != null && mounted) {
        setState(() {
          final index = _menuItems.indexWhere((m) => m.id == item.id);
          if (index != -1) {
            _menuItems[index] = updated;
          }
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                '${item.name} is now ${updated.isAvailable ? 'available' : 'unavailable'}'),
            backgroundColor: AppTheme.success,
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
    return Scaffold(
      appBar: AppBar(
        title: const Text('Menu Management'),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _menuItems.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.restaurant_menu,
                          size: 64, color: AppTheme.textLight),
                      SizedBox(height: 16),
                      Text('No menu items',
                          style: TextStyle(
                              fontSize: 18, color: AppTheme.textSecondary)),
                      SizedBox(height: 8),
                      Text('Add items to start receiving orders',
                          style: TextStyle(color: AppTheme.textLight)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadMenu,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppTheme.spacingMd),
                    itemCount: _menuItems.length,
                    itemBuilder: (context, index) {
                      final item = _menuItems[index];
                      return _buildMenuItem(item);
                    },
                  ),
                ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Navigate to add item screen
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Add item screen coming soon')),
          );
        },
        backgroundColor: AppTheme.primary,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildMenuItem(FoodItem item) {
    return Dismissible(
      key: ValueKey(item.id),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: AppTheme.spacingMd),
        color: AppTheme.error,
        child: const Icon(Icons.delete, color: Colors.white),
      ),
      confirmDismiss: (direction) async {
        return await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Delete Item'),
            content: Text('Remove "${item.name}" from menu?'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Cancel'),
              ),
              TextButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Delete',
                    style: TextStyle(color: AppTheme.error)),
              ),
            ],
          ),
        );
      },
      onDismissed: (direction) {
        // TODO: Delete item
        setState(() => _menuItems.removeWhere((m) => m.id == item.id));
      },
      child: Card(
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
                          child: Text('🥬', style: TextStyle(fontSize: 20))),
                    ),
                  )
                : Container(
                    width: 56,
                    height: 56,
                    color: AppTheme.primary.withValues(alpha: 0.1),
                    child: const Center(
                        child: Text('🥬', style: TextStyle(fontSize: 20))),
                  ),
          ),
          title: Row(
            children: [
              Expanded(
                child: Text(item.name,
                    style: const TextStyle(fontWeight: FontWeight.w600)),
              ),
              if (item.isVegan)
                const Icon(Icons.eco, size: 14, color: AppTheme.success),
            ],
          ),
          subtitle: Text(
            '₹${item.effectivePrice.toStringAsFixed(0)}/${item.unit}',
            style: const TextStyle(color: AppTheme.textSecondary),
          ),
          trailing: Switch(
            value: item.isAvailable,
            onChanged: (_) => _toggleAvailability(item),
            activeColor: AppTheme.primary,
          ),
        ),
      ),
    );
  }
}
