class FoodCartItem {
  final int id;
  final int foodItemId;
  final String foodItemName;
  final String? foodItemImage;
  final int? pricingTierId;
  final String? tierName;
  final int quantity;
  final double price;
  final String? specialNotes;

  const FoodCartItem({
    required this.id,
    required this.foodItemId,
    required this.foodItemName,
    this.foodItemImage,
    this.pricingTierId,
    this.tierName,
    this.quantity = 1,
    required this.price,
    this.specialNotes,
  });

  double get totalPrice => price * quantity;

  factory FoodCartItem.fromJson(Map<String, dynamic> json) {
    return FoodCartItem(
      id: json['id'] as int,
      foodItemId: json['food_item_id'] as int,
      foodItemName: json['food_item_name'] as String? ?? '',
      foodItemImage: json['food_item_image'] as String?,
      pricingTierId: json['pricing_tier_id'] as int?,
      tierName: json['tier_name'] as String?,
      quantity: json['quantity'] as int? ?? 1,
      price: (json['price'] as num? ?? 0).toDouble(),
      specialNotes: json['special_notes'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'food_item_id': foodItemId,
      'food_item_name': foodItemName,
      'food_item_image': foodItemImage,
      'pricing_tier_id': pricingTierId,
      'tier_name': tierName,
      'quantity': quantity,
      'price': price,
      'special_notes': specialNotes,
    };
  }
}
