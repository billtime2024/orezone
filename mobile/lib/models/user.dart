class User {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? avatar;
  final bool isVerified;
  final double rating;
  final int totalTrips;
  final String? vehicleType;
  final String? vehicleNumber;
  final DateTime createdAt;
  
  User({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.avatar,
    this.isVerified = false,
    this.rating = 0.0,
    this.totalTrips = 0,
    this.vehicleType,
    this.vehicleNumber,
    required this.createdAt,
  });
  
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      phone: json['phone'] as String,
      email: json['email'] as String?,
      avatar: json['avatar'] as String?,
      isVerified: json['is_verified'] as bool? ?? false,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      totalTrips: json['total_trips'] as int? ?? 0,
      vehicleType: json['vehicle_type'] as String?,
      vehicleNumber: json['vehicle_number'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'email': email,
      'avatar': avatar,
      'is_verified': isVerified,
      'rating': rating,
      'total_trips': totalTrips,
      'vehicle_type': vehicleType,
      'vehicle_number': vehicleNumber,
      'created_at': createdAt.toIso8601String(),
    };
  }
  
  User copyWith({
    int? id,
    String? name,
    String? phone,
    String? email,
    String? avatar,
    bool? isVerified,
    double? rating,
    int? totalTrips,
    String? vehicleType,
    String? vehicleNumber,
    DateTime? createdAt,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      phone: phone ?? this.phone,
      email: email ?? this.email,
      avatar: avatar ?? this.avatar,
      isVerified: isVerified ?? this.isVerified,
      rating: rating ?? this.rating,
      totalTrips: totalTrips ?? this.totalTrips,
      vehicleType: vehicleType ?? this.vehicleType,
      vehicleNumber: vehicleNumber ?? this.vehicleNumber,
      createdAt: createdAt ?? this.createdAt,
    );
  }
}
