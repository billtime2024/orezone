class FoodItem {
  final int id;
  final int providerId;
  final int? categoryId;
  final String name;
  final String slug;
  final String? description;
  final String? imageUrl;
  final double price;
  final double? discountPrice;
  final String unit;
  final int? preparationTimeMin;
  final bool isJain;
  final bool isVegan;
  final int spiceLevel; // 0-5
  final List<String> allergens;
  final bool isAvailable;
  final bool isFeatured;
  final double avgRating;
  final int totalOrders;

  const FoodItem({
    required this.id,
    required this.providerId,
    this.categoryId,
    required this.name,
    required this.slug,
    this.description,
    this.imageUrl,
    required this.price,
    this.discountPrice,
    this.unit = 'plate',
    this.preparationTimeMin,
    this.isJain = false,
    this.isVegan = false,
    this.spiceLevel = 0,
    this.allergens = const [],
    this.isAvailable = true,
    this.isFeatured = false,
    this.avgRating = 0.0,
    this.totalOrders = 0,
  });

  double get effectivePrice => discountPrice != null && discountPrice! > 0
      ? discountPrice!
      : price;

  bool get hasDiscount => discountPrice != null &&
      discountPrice! > 0 &&
      discountPrice! < price;

  factory FoodItem.fromJson(Map<String, dynamic> json) {
    return FoodItem(
      id: json['id'] as int,
      providerId: json['provider_id'] as int,
      categoryId: json['category_id'] as int?,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      imageUrl: json['image_url'] as String?,
      price: (json['price'] as num? ?? 0).toDouble(),
      discountPrice: json['discount_price'] != null
          ? (json['discount_price'] as num).toDouble()
          : null,
      unit: json['unit'] as String? ?? 'plate',
      preparationTimeMin: json['preparation_time_min'] as int?,
      isJain: json['is_jain'] as bool? ?? false,
      isVegan: json['is_vegan'] as bool? ?? false,
      spiceLevel: json['spice_level'] as int? ?? 0,
      allergens: (json['allergens'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      isAvailable: json['is_available'] as bool? ?? true,
      isFeatured: json['is_featured'] as bool? ?? false,
      avgRating: (json['avg_rating'] as num? ?? 0).toDouble(),
      totalOrders: json['total_orders'] as int? ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'provider_id': providerId,
      'category_id': categoryId,
      'name': name,
      'slug': slug,
      'description': description,
      'image_url': imageUrl,
      'price': price,
      'discount_price': discountPrice,
      'unit': unit,
      'preparation_time_min': preparationTimeMin,
      'is_jain': isJain,
      'is_vegan': isVegan,
      'spice_level': spiceLevel,
      'allergens': allergens,
      'is_available': isAvailable,
      'is_featured': isFeatured,
      'avg_rating': avgRating,
      'total_orders': totalOrders,
    };
  }
}

class FoodCategory {
  final int id;
  final String name;
  final String? icon;
  final String? imageUrl;

  const FoodCategory({
    required this.id,
    required this.name,
    this.icon,
    this.imageUrl,
  });

  factory FoodCategory.fromJson(Map<String, dynamic> json) {
    return FoodCategory(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      icon: json['icon'] as String?,
      imageUrl: json['image_url'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'icon': icon,
      'image_url': imageUrl,
    };
  }
}
