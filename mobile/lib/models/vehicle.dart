class Vehicle {
  final int id;
  final int userId;
  final String type; // car, bike, auto
  final String make;
  final String model;
  final String number;
  final int seats;
  final String? color;
  final String? photo;
  final bool isVerified;
  final DateTime createdAt;
  
  Vehicle({
    required this.id,
    required this.userId,
    required this.type,
    required this.make,
    required this.model,
    required this.number,
    required this.seats,
    this.color,
    this.photo,
    this.isVerified = false,
    required this.createdAt,
  });
  
  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      type: json['type'] as String,
      make: json['make'] as String,
      model: json['model'] as String,
      number: json['number'] as String,
      seats: json['seats'] as int,
      color: json['color'] as String?,
      photo: json['photo'] as String?,
      isVerified: json['is_verified'] as bool? ?? false,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'type': type,
      'make': make,
      'model': model,
      'number': number,
      'seats': seats,
      'color': color,
      'photo': photo,
      'is_verified': isVerified,
      'created_at': createdAt.toIso8601String(),
    };
  }
  
  String get displayName => '$make $model';
}
