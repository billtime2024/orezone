import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/food_order.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodOrderTrackingScreen extends StatefulWidget {
  final int orderId;
  const FoodOrderTrackingScreen({super.key, required this.orderId});

  @override
  State<FoodOrderTrackingScreen> createState() =>
      _FoodOrderTrackingScreenState();
}

class _FoodOrderTrackingScreenState extends State<FoodOrderTrackingScreen> {
  final _service = FoodService();
  FoodOrder? _order;
  bool _isLoading = true;

  static const _statusSteps = [
    'placed',
    'confirmed',
    'preparing',
    'ready',
    'delivered',
  ];

  static const _statusLabels = [
    'Placed',
    'Confirmed',
    'Preparing',
    'Ready',
    'Delivered',
  ];

  static const _statusIcons = [
    Icons.receipt_long,
    Icons.check_circle,
    Icons.restaurant,
    Icons.takeout_dining,
    Icons.delivery_dining,
  ];

  @override
  void initState() {
    super.initState();
    _loadOrder();
  }

  Future<void> _loadOrder() async {
    try {
      final order = await _service.getOrderDetail(widget.orderId);
      if (mounted) {
        setState(() {
          _order = order;
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

  Future<void> _cancelOrder() async {
    final reason = await showDialog<String>(
      context: context,
      builder: (context) {
        final controller = TextEditingController();
        return AlertDialog(
          title: const Text('Cancel Order'),
          content: TextField(
            controller: controller,
            decoration: const InputDecoration(hintText: 'Reason for cancellation'),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('No'),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, controller.text),
              child:
                  const Text('Cancel Order', style: TextStyle(color: AppTheme.error)),
            ),
          ],
        );
      },
    );

    if (reason == null || reason.isEmpty) return;

    try {
      await _service.cancelOrder(widget.orderId, reason);
      _loadOrder();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Order cancelled'),
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
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Order Tracking')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final order = _order;
    if (order == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Order Tracking')),
        body: const Center(child: Text('Order not found')),
      );
    }

    final currentStep = _statusSteps.indexOf(order.status);
    final isCancelled = order.status == 'cancelled';

    return Scaffold(
      appBar: AppBar(
        title: Text('Order #${order.orderNumber}'),
        elevation: 0,
      ),
      body: ListView(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        children: [
          // Status header
          Card(
            child: Padding(
              padding: const EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                children: [
                  Text(
                    isCancelled ? 'Order Cancelled' : order.statusLabel,
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: isCancelled ? AppTheme.error : AppTheme.primary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    DateFormat('dd MMM yyyy, hh:mm a').format(order.createdAt),
                    style: const TextStyle(color: AppTheme.textSecondary),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: AppTheme.spacingMd),

          // Status timeline
          if (!isCancelled)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(AppTheme.spacingMd),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Order Status',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: AppTheme.spacingMd),
                    ...List.generate(_statusSteps.length, (index) {
                      final isCompleted = index <= currentStep;
                      final isCurrent = index == currentStep;
                      return _buildTimelineStep(
                        index: index,
                        isCompleted: isCompleted,
                        isCurrent: isCurrent,
                        isLast: index == _statusSteps.length - 1,
                      );
                    }),
                  ],
                ),
              ),
            ),
          const SizedBox(height: AppTheme.spacingMd),

          // Provider info
          Card(
            child: ListTile(
              leading: const CircleAvatar(
                backgroundColor: AppTheme.primary,
                child: Icon(Icons.store, color: Colors.white),
              ),
              title: Text(order.providerName ?? 'Food Provider',
                  style: const TextStyle(fontWeight: FontWeight.w600)),
              subtitle: Text(
                  '${order.deliveryType == 'delivery' ? 'Delivery' : 'Pickup'} • ₹${order.totalAmount.toStringAsFixed(0)}'),
            ),
          ),
          const SizedBox(height: AppTheme.spacingMd),

          // Payment info
          Card(
            child: Padding(
              padding: const EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Payment Summary',
                      style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: AppTheme.spacingSm),
                  _buildPriceRow('Subtotal', order.subtotal),
                  if (order.deliveryCharge > 0)
                    _buildPriceRow('Delivery', order.deliveryCharge),
                  if (order.discountAmount > 0)
                    _buildPriceRow('Discount', -order.discountAmount,
                        isDiscount: true),
                  if (order.taxAmount > 0)
                    _buildPriceRow('Tax', order.taxAmount),
                  const Divider(),
                  _buildPriceRow('Total', order.totalAmount, isBold: true),
                  const SizedBox(height: 4),
                  Text(
                      'Payment: ${order.paymentMethod?.toUpperCase() ?? 'COD'} (${order.paymentStatus})',
                      style: const TextStyle(
                          fontSize: 12, color: AppTheme.textSecondary)),
                ],
              ),
            ),
          ),
          const SizedBox(height: AppTheme.spacingMd),

          // Cancel button
          if (order.canBeCancelled)
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: _cancelOrder,
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.error,
                  side: const BorderSide(color: AppTheme.error),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                child: const Text('Cancel Order'),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildTimelineStep({
    required int index,
    required bool isCompleted,
    required bool isCurrent,
    required bool isLast,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: isCompleted ? AppTheme.primary : AppTheme.border,
                shape: BoxShape.circle,
              ),
              child: Center(
                child: isCompleted
                    ? Icon(_statusIcons[index],
                        color: Colors.white, size: 18)
                    : Text('${index + 1}',
                        style: TextStyle(
                            color: AppTheme.textLight, fontSize: 12)),
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 30,
                color: isCompleted ? AppTheme.primary : AppTheme.border,
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(top: 6),
            child: Text(
              _statusLabels[index],
              style: TextStyle(
                fontSize: 14,
                fontWeight: isCurrent ? FontWeight.bold : FontWeight.normal,
                color: isCompleted ? AppTheme.textPrimary : AppTheme.textLight,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPriceRow(String label, double amount,
      {bool isBold = false, bool isDiscount = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontSize: isBold ? 15 : 13,
                  fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(
            '${isDiscount ? '-' : ''}₹${amount.abs().toStringAsFixed(0)}',
            style: TextStyle(
                fontSize: isBold ? 15 : 13,
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
                color: isDiscount ? AppTheme.success : null),
          ),
        ],
      ),
    );
  }
}
