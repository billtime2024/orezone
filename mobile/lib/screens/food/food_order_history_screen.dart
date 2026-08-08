import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../models/food_order.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodOrderHistoryScreen extends StatefulWidget {
  const FoodOrderHistoryScreen({super.key});

  @override
  State<FoodOrderHistoryScreen> createState() =>
      _FoodOrderHistoryScreenState();
}

class _FoodOrderHistoryScreenState extends State<FoodOrderHistoryScreen>
    with SingleTickerProviderStateMixin {
  final _service = FoodService();
  late TabController _tabController;

  List<FoodOrder> _activeOrders = [];
  List<FoodOrder> _pastOrders = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadOrders();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);
    try {
      final orders = await _service.getOrders();
      if (mounted) {
        setState(() {
          _activeOrders = orders
              .where((o) =>
                  o.status != 'delivered' && o.status != 'cancelled')
              .toList();
          _pastOrders = orders
              .where((o) =>
                  o.status == 'delivered' || o.status == 'cancelled')
              .toList();
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
        title: const Text('My Orders'),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'Past'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOrderList(_activeOrders, isActive: true),
                _buildOrderList(_pastOrders),
              ],
            ),
    );
  }

  Widget _buildOrderList(List<FoodOrder> orders, {bool isActive = false}) {
    if (orders.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isActive ? Icons.receipt_long : Icons.history,
              size: 64,
              color: AppTheme.textLight,
            ),
            const SizedBox(height: 16),
            Text(
              isActive ? 'No active orders' : 'No past orders',
              style: const TextStyle(
                  fontSize: 18, color: AppTheme.textSecondary),
            ),
            const SizedBox(height: 8),
            Text(
              isActive ? 'Place an order to get started' : 'Your order history will appear here',
              style: const TextStyle(color: AppTheme.textLight),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadOrders,
      child: ListView.builder(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        itemCount: orders.length,
        itemBuilder: (context, index) {
          return _buildOrderCard(orders[index]);
        },
      ),
    );
  }

  Widget _buildOrderCard(FoodOrder order) {
    final statusColor = _getStatusColor(order.status);

    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingSm),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        onTap: () {
          context.push('/food/orders/${order.id}');
        },
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
                          fontSize: 15, fontWeight: FontWeight.bold)),
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
              Text(order.providerName ?? 'Food Provider',
                  style: const TextStyle(color: AppTheme.textSecondary)),
              const SizedBox(height: 4),
              Text(
                DateFormat('dd MMM yyyy, hh:mm a').format(order.createdAt),
                style: const TextStyle(
                    fontSize: 12, color: AppTheme.textLight),
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                      '${order.deliveryType == 'delivery' ? 'Delivery' : 'Pickup'}'),
                  Text(
                    '₹${order.totalAmount.toStringAsFixed(0)}',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
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
