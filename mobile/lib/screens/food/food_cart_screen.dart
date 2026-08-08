import 'package:flutter/material.dart';
import '../../models/food_cart_item.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodCartScreen extends StatefulWidget {
  const FoodCartScreen({super.key});

  @override
  State<FoodCartScreen> createState() => _FoodCartScreenState();
}

class _FoodCartScreenState extends State<FoodCartScreen> {
  final _service = FoodService();
  List<FoodCartItem> _cartItems = [];
  bool _isLoading = true;
  bool _isPlacingOrder = false;

  bool _isDelivery = true;
  String _selectedPaymentMethod = 'cod';
  final _promoController = TextEditingController();
  final _addressController = TextEditingController();
  final _instructionsController = TextEditingController();
  double _promoDiscount = 0;

  @override
  void initState() {
    super.initState();
    _loadCart();
  }

  @override
  void dispose() {
    _promoController.dispose();
    _addressController.dispose();
    _instructionsController.dispose();
    super.dispose();
  }

  Future<void> _loadCart() async {
    setState(() => _isLoading = true);
    try {
      final items = await _service.getCart();
      if (mounted) {
        setState(() {
          _cartItems = items;
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

  Future<void> _updateQuantity(FoodCartItem item, int delta) async {
    final newQty = item.quantity + delta;
    if (newQty <= 0) {
      _removeItem(item);
      return;
    }
    try {
      await _service.updateCartItem(item.id, newQty);
      _loadCart();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _removeItem(FoodCartItem item) async {
    try {
      await _service.removeFromCart(item.id);
      _loadCart();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  double get _subtotal =>
      _cartItems.fold(0, (sum, item) => sum + item.totalPrice);

  double get _deliveryCharge => _isDelivery ? 30.0 : 0;
  double get _tax => _subtotal * 0.05; // 5% GST
  double get _total => _subtotal + _deliveryCharge + _tax - _promoDiscount;

  Future<void> _placeOrder() async {
    if (_cartItems.isEmpty) return;
    if (_isDelivery && _addressController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter delivery address')),
      );
      return;
    }

    setState(() => _isPlacingOrder = true);
    try {
      final order = await _service.placeOrder(
        deliveryType: _isDelivery ? 'delivery' : 'pickup',
        address: _isDelivery ? _addressController.text : null,
        paymentMethod: _selectedPaymentMethod,
        instructions: _instructionsController.text.isNotEmpty
            ? _instructionsController.text
            : null,
      );
      if (mounted) {
        setState(() => _isPlacingOrder = false);
        if (order != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Order placed successfully!'),
              backgroundColor: AppTheme.success,
            ),
          );
          Navigator.pop(context);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Failed to place order'),
              backgroundColor: AppTheme.error,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isPlacingOrder = false);
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
        title: const Text('Your Cart'),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _cartItems.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.shopping_cart_outlined,
                          size: 64, color: AppTheme.textLight),
                      SizedBox(height: 16),
                      Text('Your cart is empty',
                          style: TextStyle(
                              fontSize: 18, color: AppTheme.textSecondary)),
                      SizedBox(height: 8),
                      Text('Add some delicious food items',
                          style: TextStyle(color: AppTheme.textLight)),
                    ],
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.all(AppTheme.spacingMd),
                  children: [
                    // Delivery/Pickup toggle
                    _buildDeliveryToggle(),
                    const SizedBox(height: AppTheme.spacingMd),

                    // Address (if delivery)
                    if (_isDelivery) ...[
                      TextField(
                        controller: _addressController,
                        decoration: const InputDecoration(
                          labelText: 'Delivery Address',
                          prefixIcon: Icon(Icons.location_on),
                        ),
                        maxLines: 2,
                      ),
                      const SizedBox(height: AppTheme.spacingMd),
                    ],

                    // Cart items
                    ..._cartItems.map((item) => _buildCartItem(item)),
                    const SizedBox(height: AppTheme.spacingMd),

                    // Promo code
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _promoController,
                            decoration: const InputDecoration(
                              hintText: 'Promo code',
                              isDense: true,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        OutlinedButton(
                          onPressed: () {
                            // TODO: Apply promo code
                            setState(() => _promoDiscount = 0);
                          },
                          child: const Text('Apply'),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppTheme.spacingMd),

                    // Payment method
                    const Text('Payment Method',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600)),
                    const SizedBox(height: AppTheme.spacingSm),
                    _buildPaymentOption('cod', '💵 Cash on Delivery'),
                    _buildPaymentOption('upi', '📱 UPI'),
                    _buildPaymentOption('wallet', '💰 Wallet'),
                    const SizedBox(height: AppTheme.spacingMd),

                    // Special instructions
                    TextField(
                      controller: _instructionsController,
                      decoration: const InputDecoration(
                        hintText: 'Special instructions (optional)',
                      ),
                      maxLines: 2,
                    ),
                    const SizedBox(height: AppTheme.spacingMd),

                    // Price summary
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(AppTheme.spacingMd),
                        child: Column(
                          children: [
                            _buildPriceRow('Item Total', _subtotal),
                            if (_isDelivery)
                              _buildPriceRow('Delivery Charge', _deliveryCharge),
                            _buildPriceRow('GST (5%)', _tax),
                            if (_promoDiscount > 0)
                              _buildPriceRow('Promo Discount', -_promoDiscount,
                                  isDiscount: true),
                            const Divider(),
                            _buildPriceRow('Grand Total', _total,
                                isBold: true),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: AppTheme.spacingLg),

                    // Place order button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isPlacingOrder ? null : _placeOrder,
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          backgroundColor: AppTheme.primary,
                          shape: RoundedRectangleBorder(
                              borderRadius:
                                  BorderRadius.circular(AppTheme.radiusMd)),
                        ),
                        child: _isPlacingOrder
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                'Place Order - ₹${_total.toStringAsFixed(0)}',
                                style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white),
                              ),
                      ),
                    ),
                    const SizedBox(height: AppTheme.spacingMd),
                  ],
                ),
    );
  }

  Widget _buildDeliveryToggle() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        border: Border.all(color: AppTheme.border),
      ),
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _isDelivery = true),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12),
                decoration: BoxDecoration(
                  color: _isDelivery ? AppTheme.primary : Colors.transparent,
                  borderRadius:
                      BorderRadius.circular(AppTheme.radiusSm),
                ),
                child: Center(
                  child: Text('Delivery',
                      style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: _isDelivery
                              ? Colors.white
                              : AppTheme.textSecondary)),
                ),
              ),
            ),
          ),
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _isDelivery = false),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12),
                decoration: BoxDecoration(
                  color: !_isDelivery ? AppTheme.primary : Colors.transparent,
                  borderRadius:
                      BorderRadius.circular(AppTheme.radiusSm),
                ),
                child: Center(
                  child: Text('Pickup',
                      style: TextStyle(
                          fontWeight: FontWeight.w600,
                          color: !_isDelivery
                              ? Colors.white
                              : AppTheme.textSecondary)),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCartItem(FoodCartItem item) {
    return Card(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingSm),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(AppTheme.radiusSm),
              child: item.foodItemImage != null
                  ? Image.network(
                      item.foodItemImage!,
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
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(item.foodItemName,
                      style: const TextStyle(
                          fontSize: 15, fontWeight: FontWeight.w600)),
                  if (item.tierName != null)
                    Text(item.tierName!,
                        style: const TextStyle(
                            fontSize: 12, color: AppTheme.textSecondary)),
                  Text('₹${item.price.toStringAsFixed(0)} x ${item.quantity}',
                      style: const TextStyle(
                          fontSize: 13, color: AppTheme.textSecondary)),
                ],
              ),
            ),
            Row(
              children: [
                IconButton(
                  onPressed: () => _updateQuantity(item, -1),
                  icon: const Icon(Icons.remove_circle_outline),
                  iconSize: 24,
                  color: AppTheme.textSecondary,
                ),
                Text('${item.quantity}',
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold)),
                IconButton(
                  onPressed: () => _updateQuantity(item, 1),
                  icon: const Icon(Icons.add_circle),
                  iconSize: 24,
                  color: AppTheme.primary,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPaymentOption(String value, String label) {
    return RadioListTile<String>(
      title: Text(label),
      value: value,
      groupValue: _selectedPaymentMethod,
      onChanged: (val) {
        if (val != null) setState(() => _selectedPaymentMethod = val);
      },
      activeColor: AppTheme.primary,
      contentPadding: EdgeInsets.zero,
    );
  }

  Widget _buildPriceRow(String label, double amount,
      {bool isBold = false, bool isDiscount = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontSize: isBold ? 16 : 14,
                  fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(
            '${isDiscount ? '-' : ''}₹${amount.abs().toStringAsFixed(0)}',
            style: TextStyle(
                fontSize: isBold ? 16 : 14,
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
                color: isDiscount ? AppTheme.success : null),
          ),
        ],
      ),
    );
  }
}
