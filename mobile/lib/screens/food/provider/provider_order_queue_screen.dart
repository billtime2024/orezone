import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../models/food_order.dart';
import '../../../services/food_service.dart';
import '../../../theme/app_theme.dart';

class ProviderOrderQueueScreen extends StatefulWidget {
  const ProviderOrderQueueScreen({super.key});

  @override
  State<ProviderOrderQueueScreen> createState() =>
      _ProviderOrderQueueScreenState();
}

class _ProviderOrderQueueScreenState extends State<ProviderOrderQueueScreen> {
  final _service = FoodService();
  List<FoodOrder> _orders = [];
  bool _isLoading = true;
  String _selectedFilter = 'all';

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);
    try {
      final status = _selectedFilter == 'all' ? null : _selectedFilter;
      final orders = await _service.getProviderOrders(status: status);
      if (mounted) {
        setState(() {
          _orders = orders;
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

  Future<void> _updateStatus(FoodOrder order, String newStatus) async {
    try {
      final updated = await _service.updateOrderStatus(order.id, newStatus);
      if (updated != null && mounted) {
        _loadOrders();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Order #${order.orderNumber} → ${_statusLabel(newStatus)}'),
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

  String _statusLabel(String status) {
    switch (status) {
      case 'placed':
        return 'Placed';
      case 'confirmed':
        return 'Confirmed';
      case 'preparing':
        return 'Preparing';
      case 'ready':
        return 'Ready';
      case 'out_for_delivery':
        return 'Out for Delivery';
      case 'delivered':
        return 'Delivered';
      case 'cancelled':
        return 'Cancelled';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Order Queue'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filter tabs
          Container(
            padding: const EdgeInsets.symmetric(
                horizontal: AppTheme.spacingMd, vertical: AppTheme.spacingSm),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('All', 'all'),
                  _buildFilterChip('Placed', 'placed'),
                  _buildFilterChip('Confirmed', 'confirmed'),
                  _buildFilterChip('Preparing', 'preparing'),
                  _buildFilterChip('Ready', 'ready'),
                ],
              ),
            ),
          ),

          // Orders list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _orders.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.receipt_long,
                                size: 64, color: AppTheme.textLight),
                            SizedBox(height: 16),
                            Text('No orders found',
                                style: TextStyle(
                                    fontSize: 18,
                                    color: AppTheme.textSecondary)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadOrders,
                        child: ListView.builder(
                          padding:
                              const EdgeInsets.symmetric(horizontal: AppTheme.spacingMd),
                          itemCount: _orders.length,
                          itemBuilder: (context, index) {
                            return _buildOrderCard(_orders[index]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedFilter == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() => _selectedFilter = value);
          _loadOrders();
        },
        selectedColor: AppTheme.primary.withValues(alpha: 0.15),
        checkmarkColor: AppTheme.primary,
      ),
    );
  }

  Widget _buildOrderCard(FoodOrder order) {
    final statusColor = _getStatusColor(order.status);
    final nextStatus = _getNextStatus(order.status);

    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingSm),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Order #${order.orderNumber}',
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold)),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(order.statusLabel,
                      style: TextStyle(
                          fontSize: 12,
                          color: statusColor,
                          fontWeight: FontWeight.w600)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.access_time,
                    size: 14, color: AppTheme.textSecondary),
                const SizedBox(width: 4),
                Text(
                    DateFormat('dd MMM, hh:mm a').format(order.createdAt),
                    style: const TextStyle(
                        fontSize: 13, color: AppTheme.textSecondary)),
                const SizedBox(width: 12),
                Icon(
                    order.deliveryType == 'delivery'
                        ? Icons.delivery_dining
                        : Icons.takeout_dining,
                    size: 14,
                    color: AppTheme.textSecondary),
                const SizedBox(width: 4),
                Text(
                    order.deliveryType == 'delivery' ? 'Delivery' : 'Pickup',
                    style: const TextStyle(
                        fontSize: 13, color: AppTheme.textSecondary)),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              '₹${order.totalAmount.toStringAsFixed(0)} • ${order.paymentMethod?.toUpperCase() ?? 'COD'}',
              style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primary),
            ),

            // Action buttons
            if (nextStatus != null) ...[
              const SizedBox(height: AppTheme.spacingSm),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        // Cancel/Reject
                        _updateStatus(order, 'cancelled');
                      },
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.error,
                        side: const BorderSide(color: AppTheme.error),
                      ),
                      child: const Text('Reject',
                          style: TextStyle(fontSize: 13)),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => _updateStatus(order, nextStatus),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                      child: Text(_statusActionLabel(nextStatus),
                          style: const TextStyle(
                              fontSize: 13, color: Colors.white)),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  String? _getNextStatus(String current) {
    switch (current) {
      case 'placed':
        return 'confirmed';
      case 'confirmed':
        return 'preparing';
      case 'preparing':
        return 'ready';
      case 'ready':
        return 'out_for_delivery';
      case 'out_for_delivery':
        return 'delivered';
      default:
        return null;
    }
  }

  String _statusActionLabel(String nextStatus) {
    switch (nextStatus) {
      case 'confirmed':
        return 'Accept Order';
      case 'preparing':
        return 'Start Preparing';
      case 'ready':
        return 'Mark Ready';
      case 'out_for_delivery':
        return 'Out for Delivery';
      case 'delivered':
        return 'Mark Delivered';
      default:
        return 'Next';
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'placed':
        return AppTheme.info;
      case 'confirmed':
        return AppTheme.primary;
      case 'preparing':
        return AppTheme.accent;
      case 'ready':
        return AppTheme.warning;
      case 'out_for_delivery':
        return AppTheme.info;
      case 'delivered':
        return AppTheme.success;
      case 'cancelled':
        return AppTheme.error;
      default:
        return AppTheme.textSecondary;
    }
  }
}
