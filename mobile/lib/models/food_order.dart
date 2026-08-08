class FoodOrder {
  final int id;
  final String orderNumber;
  final int providerId;
  final String? providerName;
  final String orderType;
  final String status;
  final String deliveryType;
  final double subtotal;
  final double deliveryCharge;
  final double discountAmount;
  final double taxAmount;
  final double totalAmount;
  final String? paymentMethod;
  final String paymentStatus;
  final DateTime createdAt;

  const FoodOrder({
    required this.id,
    required this.orderNumber,
    required this.providerId,
    this.providerName,
    required this.orderType,
    required this.status,
    required this.deliveryType,
    required this.subtotal,
    this.deliveryCharge = 0,
    this.discountAmount = 0,
    this.taxAmount = 0,
    required this.totalAmount,
    this.paymentMethod,
    this.paymentStatus = 'pending',
    required this.createdAt,
  });

  bool get canBeCancelled =>
      status == 'placed' || status == 'confirmed';

  String get statusLabel {
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

  factory FoodOrder.fromJson(Map<String, dynamic> json) {
    return FoodOrder(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String? ?? '',
      providerId: json['provider_id'] as int,
      providerName: json['provider_name'] as String?,
      orderType: json['order_type'] as String? ?? 'regular',
      status: json['status'] as String? ?? 'placed',
      deliveryType: json['delivery_type'] as String? ?? 'delivery',
      subtotal: (json['subtotal'] as num? ?? 0).toDouble(),
      deliveryCharge: (json['delivery_charge'] as num? ?? 0).toDouble(),
      discountAmount: (json['discount_amount'] as num? ?? 0).toDouble(),
      taxAmount: (json['tax_amount'] as num? ?? 0).toDouble(),
      totalAmount: (json['total_amount'] as num? ?? 0).toDouble(),
      paymentMethod: json['payment_method'] as String?,
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'order_number': orderNumber,
      'provider_id': providerId,
      'provider_name': providerName,
      'order_type': orderType,
      'status': status,
      'delivery_type': deliveryType,
      'subtotal': subtotal,
      'delivery_charge': deliveryCharge,
      'discount_amount': discountAmount,
      'tax_amount': taxAmount,
      'total_amount': totalAmount,
      'payment_method': paymentMethod,
      'payment_status': paymentStatus,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
