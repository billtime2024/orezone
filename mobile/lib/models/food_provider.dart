class FoodProvider {
  final int id;
  final int userId;
  final String providerType;
  final String businessName;
  final String? description;
  final String? logoUrl;
  final String? phone;
  final String? email;
  final String? address;
  final double? latitude;
  final double? longitude;
  final String? city;
  final double avgRating;
  final int totalOrders;
  final bool isFeatured;
  final bool isActive;
  final String verificationStatus;

  const FoodProvider({
    required this.id,
    required this.userId,
    required this.providerType,
    required this.businessName,
    this.description,
    this.logoUrl,
    this.phone,
    this.email,
    this.address,
    this.latitude,
    this.longitude,
    this.city,
    this.avgRating = 0.0,
    this.totalOrders = 0,
    this.isFeatured = false,
    this.isActive = true,
    this.verificationStatus = 'pending',
  });

  factory FoodProvider.fromJson(Map<String, dynamic> json) {
    return FoodProvider(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      providerType: json['provider_type'] as String? ?? 'kitchen',
      businessName: json['business_name'] as String? ?? '',
      description: json['description'] as String?,
      logoUrl: json['logo_url'] as String?,
      phone: json['phone'] as String?,
      email: json['email'] as String?,
      address: json['address'] as String?,
      latitude: json['latitude'] != null
          ? (json['latitude'] as num).toDouble()
          : null,
      longitude: json['longitude'] != null
          ? (json['longitude'] as num).toDouble()
          : null,
      city: json['city'] as String?,
      avgRating: (json['avg_rating'] as num? ?? 0).toDouble(),
      totalOrders: json['total_orders'] as int? ?? 0,
      isFeatured: json['is_featured'] as bool? ?? false,
      isActive: json['is_active'] as bool? ?? true,
      verificationStatus: json['verification_status'] as String? ?? 'pending',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'provider_type': providerType,
      'business_name': businessName,
      'description': description,
      'logo_url': logoUrl,
      'phone': phone,
      'email': email,
      'address': address,
      'latitude': latitude,
      'longitude': longitude,
      'city': city,
      'avg_rating': avgRating,
      'total_orders': totalOrders,
      'is_featured': isFeatured,
      'is_active': isActive,
      'verification_status': verificationStatus,
    };
  }
}
