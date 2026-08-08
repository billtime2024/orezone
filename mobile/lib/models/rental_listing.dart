class RentalListing {
  final int id;
  final int userId;
  final String rentalType; // house, car, commercial, room
  final String title;
  final String? description;
  final String slug;
  final double pricePerUnit;
  final String priceUnit; // hour, day, month, year
  final double securityDeposit;
  final double cleaningFee;
  final String addressLine1;
  final String? addressLine2;
  final String city;
  final String state;
  final String pincode;
  final double? latitude;
  final double? longitude;
  final String status; // draft, active, paused, closed
  final bool instantBooking;
  final List<String>? blockedDates;
  final List<String>? photos;
  final List<String>? rules;
  final int totalBookings;
  final double avgRating;
  final int reviewCount;
  final Map<String, dynamic>? details;
  final Map<String, dynamic>? owner;
  final DateTime createdAt;
  final DateTime updatedAt;

  RentalListing({
    required this.id,
    required this.userId,
    required this.rentalType,
    required this.title,
    this.description,
    required this.slug,
    required this.pricePerUnit,
    required this.priceUnit,
    this.securityDeposit = 0,
    this.cleaningFee = 0,
    required this.addressLine1,
    this.addressLine2,
    required this.city,
    required this.state,
    required this.pincode,
    this.latitude,
    this.longitude,
    this.status = 'draft',
    this.instantBooking = false,
    this.blockedDates,
    this.photos,
    this.rules,
    this.totalBookings = 0,
    this.avgRating = 0,
    this.reviewCount = 0,
    this.details,
    this.owner,
    required this.createdAt,
    required this.updatedAt,
  });

  factory RentalListing.fromJson(Map<String, dynamic> json) {
    return RentalListing(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      rentalType: json['rental_type'] as String,
      title: json['title'] as String,
      description: json['description'] as String?,
      slug: json['slug'] as String,
      pricePerUnit: (json['price_per_unit'] as num).toDouble(),
      priceUnit: json['price_unit'] as String,
      securityDeposit: (json['security_deposit'] as num?)?.toDouble() ?? 0,
      cleaningFee: (json['cleaning_fee'] as num?)?.toDouble() ?? 0,
      addressLine1: json['address_line1'] as String,
      addressLine2: json['address_line2'] as String?,
      city: json['city'] as String,
      state: json['state'] as String,
      pincode: json['pincode'] as String,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      status: json['status'] as String? ?? 'draft',
      instantBooking: json['instant_booking'] as bool? ?? false,
      blockedDates: json['blocked_dates'] != null
          ? List<String>.from(json['blocked_dates'])
          : null,
      photos: json['photos'] != null
          ? List<String>.from(json['photos'])
          : null,
      rules: json['rules'] != null
          ? List<String>.from(json['rules'])
          : null,
      totalBookings: json['total_bookings'] as int? ?? 0,
      avgRating: (json['avg_rating'] as num?)?.toDouble() ?? 0,
      reviewCount: json['review_count'] as int? ?? 0,
      details: json['details'] as Map<String, dynamic>?,
      owner: json['owner'] as Map<String, dynamic>?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  String get formattedPrice => '₹${pricePerUnit.toStringAsFixed(0)} / $priceUnit';

  String get typeIcon {
    switch (rentalType) {
      case 'house': return '🏠';
      case 'car': return '🚗';
      case 'commercial': return '🏢';
      case 'room': return '🛏️';
      default: return '📦';
    }
  }

  String get typeLabel {
    switch (rentalType) {
      case 'house': return 'House';
      case 'car': return 'Car';
      case 'commercial': return 'Commercial';
      case 'room': return 'Room';
      default: return rentalType;
    }
  }
}
