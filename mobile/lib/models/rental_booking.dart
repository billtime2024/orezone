class RentalBooking {
  final int id;
  final int rentalListingId;
  final int userId;
  final int ownerId;
  final String checkIn;
  final String checkOut;
  final int nights;
  final double pricePerUnit;
  final double subtotal;
  final double cleaningFee;
  final double securityDeposit;
  final double serviceFee;
  final double totalAmount;
  final String currency;
  final String status;
  final String paymentStatus;
  final String? paymentMethod;
  final String? paymentReference;
  final String? guestMessage;
  final String? hostMessage;
  final String bookingType;
  final String? cancellationReason;
  final String? cancelledBy;
  final String? cancelledAt;
  final int guestsCount;
  final Map<String, dynamic>? specialRequests;
  final Map<String, dynamic>? metadata;
  final Map<String, dynamic>? listing;
  final Map<String, dynamic>? guest;
  final Map<String, dynamic>? owner;
  final List<Map<String, dynamic>>? statusHistory;
  final DateTime createdAt;
  final DateTime updatedAt;

  RentalBooking({
    required this.id,
    required this.rentalListingId,
    required this.userId,
    required this.ownerId,
    required this.checkIn,
    required this.checkOut,
    required this.nights,
    required this.pricePerUnit,
    required this.subtotal,
    this.cleaningFee = 0,
    this.securityDeposit = 0,
    this.serviceFee = 0,
    required this.totalAmount,
    this.currency = 'INR',
    required this.status,
    this.paymentStatus = 'pending',
    this.paymentMethod,
    this.paymentReference,
    this.guestMessage,
    this.hostMessage,
    this.bookingType = 'instant',
    this.cancellationReason,
    this.cancelledBy,
    this.cancelledAt,
    this.guestsCount = 1,
    this.specialRequests,
    this.metadata,
    this.listing,
    this.guest,
    this.owner,
    this.statusHistory,
    required this.createdAt,
    required this.updatedAt,
  });

  factory RentalBooking.fromJson(Map<String, dynamic> json) {
    return RentalBooking(
      id: json['id'] as int,
      rentalListingId: json['rental_listing_id'] as int,
      userId: json['user_id'] as int,
      ownerId: json['owner_id'] as int,
      checkIn: json['check_in'] as String,
      checkOut: json['check_out'] as String,
      nights: json['nights'] as int,
      pricePerUnit: (json['price_per_unit'] as num).toDouble(),
      subtotal: (json['subtotal'] as num).toDouble(),
      cleaningFee: (json['cleaning_fee'] as num?)?.toDouble() ?? 0,
      securityDeposit: (json['security_deposit'] as num?)?.toDouble() ?? 0,
      serviceFee: (json['service_fee'] as num?)?.toDouble() ?? 0,
      totalAmount: (json['total_amount'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'INR',
      status: json['status'] as String,
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      paymentMethod: json['payment_method'] as String?,
      paymentReference: json['payment_reference'] as String?,
      guestMessage: json['guest_message'] as String?,
      hostMessage: json['host_message'] as String?,
      bookingType: json['booking_type'] as String? ?? 'instant',
      cancellationReason: json['cancellation_reason'] as String?,
      cancelledBy: json['cancelled_by'] as String?,
      cancelledAt: json['cancelled_at'] as String?,
      guestsCount: json['guests_count'] as int? ?? 1,
      specialRequests: json['special_requests'] as Map<String, dynamic>?,
      metadata: json['metadata'] as Map<String, dynamic>?,
      listing: json['listing'] as Map<String, dynamic>?,
      guest: json['guest'] as Map<String, dynamic>?,
      owner: json['owner'] as Map<String, dynamic>?,
      statusHistory: json['status_history'] != null
          ? List<Map<String, dynamic>>.from(json['status_history'])
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  String get statusLabel {
    switch (status) {
      case 'pending': return 'Pending Confirmation';
      case 'confirmed': return 'Confirmed';
      case 'active': return 'Checked In';
      case 'completed': return 'Completed';
      case 'cancelled_by_guest': return 'Cancelled by Guest';
      case 'cancelled_by_host': return 'Cancelled by Host';
      case 'rejected': return 'Rejected';
      case 'expired': return 'Expired';
      case 'disputed': return 'Disputed';
      default: return status;
    }
  }

  bool get canBeCancelled => status == 'pending' || status == 'confirmed';
  bool get isActive => status == 'active';
  bool get isCompleted => status == 'completed';
}
