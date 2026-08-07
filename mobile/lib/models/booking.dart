class Booking {
  final int id;
  final int tripId;
  final int userId;
  final String userName;
  final int seatsBooked;
  final double totalAmount;
  final String status; // pending, confirmed, cancelled, completed
  final DateTime? pickupLocation;
  final DateTime? dropoffLocation;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  Booking({
    required this.id,
    required this.tripId,
    required this.userId,
    required this.userName,
    required this.seatsBooked,
    required this.totalAmount,
    this.status = 'pending',
    this.pickupLocation,
    this.dropoffLocation,
    required this.createdAt,
    required this.updatedAt,
  });
  
  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] as int,
      tripId: json['trip_id'] as int,
      userId: json['user_id'] as int,
      userName: json['user_name'] as String,
      seatsBooked: json['seats_booked'] as int,
      totalAmount: (json['total_amount'] as num).toDouble(),
      status: json['status'] as String? ?? 'pending',
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'trip_id': tripId,
      'user_id': userId,
      'user_name': userName,
      'seats_booked': seatsBooked,
      'total_amount': totalAmount,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  bool get isPending => status == 'pending';
  bool get isConfirmed => status == 'confirmed';
  bool get isCancelled => status == 'cancelled';
  bool get isCompleted => status == 'completed';
}
