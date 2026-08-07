class Trip {
  final int id;
  final int driverId;
  final String driverName;
  final String? driverAvatar;
  final String fromCity;
  final String toCity;
  final DateTime departureTime;
  final DateTime? arrivalTime;
  final int availableSeats;
  final int bookedSeats;
  final double pricePerSeat;
  final String? vehicleType;
  final String status; // pending, active, completed, cancelled
  final String? notes;
  final DateTime createdAt;
  
  Trip({
    required this.id,
    required this.driverId,
    required this.driverName,
    this.driverAvatar,
    required this.fromCity,
    required this.toCity,
    required this.departureTime,
    this.arrivalTime,
    required this.availableSeats,
    this.bookedSeats = 0,
    required this.pricePerSeat,
    this.vehicleType,
    this.status = 'pending',
    this.notes,
    required this.createdAt,
  });
  
  factory Trip.fromJson(Map<String, dynamic> json) {
    return Trip(
      id: json['id'] as int,
      driverId: json['driver_id'] as int,
      driverName: json['driver_name'] as String,
      driverAvatar: json['driver_avatar'] as String?,
      fromCity: json['from_city'] as String,
      toCity: json['to_city'] as String,
      departureTime: DateTime.parse(json['departure_time'] as String),
      arrivalTime: json['arrival_time'] != null 
          ? DateTime.parse(json['arrival_time'] as String) 
          : null,
      availableSeats: json['available_seats'] as int,
      bookedSeats: json['booked_seats'] as int? ?? 0,
      pricePerSeat: (json['price_per_seat'] as num).toDouble(),
      vehicleType: json['vehicle_type'] as String?,
      status: json['status'] as String? ?? 'pending',
      notes: json['notes'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'driver_id': driverId,
      'driver_name': driverName,
      'driver_avatar': driverAvatar,
      'from_city': fromCity,
      'to_city': toCity,
      'departure_time': departureTime.toIso8601String(),
      'arrival_time': arrivalTime?.toIso8601String(),
      'available_seats': availableSeats,
      'booked_seats': bookedSeats,
      'price_per_seat': pricePerSeat,
      'vehicle_type': vehicleType,
      'status': status,
      'notes': notes,
      'created_at': createdAt.toIso8601String(),
    };
  }
  
  int get seatsAvailable => availableSeats - bookedSeats;
  bool get isFull => bookedSeats >= availableSeats;
}
