class CateringRequest {
  final int id;
  final String requestNumber;
  final String eventType;
  final String? eventName;
  final String? eventDate;
  final String? eventTime;
  final String? venueAddress;
  final int? guestCount;
  final double? budgetMin;
  final double? budgetMax;
  final String status;
  final double? totalAmount;
  final DateTime createdAt;

  const CateringRequest({
    required this.id,
    required this.requestNumber,
    required this.eventType,
    this.eventName,
    this.eventDate,
    this.eventTime,
    this.venueAddress,
    this.guestCount,
    this.budgetMin,
    this.budgetMax,
    this.status = 'pending',
    this.totalAmount,
    required this.createdAt,
  });

  factory CateringRequest.fromJson(Map<String, dynamic> json) {
    return CateringRequest(
      id: json['id'] as int,
      requestNumber: json['request_number'] as String? ?? '',
      eventType: json['event_type'] as String? ?? 'party',
      eventName: json['event_name'] as String?,
      eventDate: json['event_date'] as String?,
      eventTime: json['event_time'] as String?,
      venueAddress: json['venue_address'] as String?,
      guestCount: json['guest_count'] as int?,
      budgetMin: json['budget_min'] != null
          ? (json['budget_min'] as num).toDouble()
          : null,
      budgetMax: json['budget_max'] != null
          ? (json['budget_max'] as num).toDouble()
          : null,
      status: json['status'] as String? ?? 'pending',
      totalAmount: json['total_amount'] != null
          ? (json['total_amount'] as num).toDouble()
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'request_number': requestNumber,
      'event_type': eventType,
      'event_name': eventName,
      'event_date': eventDate,
      'event_time': eventTime,
      'venue_address': venueAddress,
      'guest_count': guestCount,
      'budget_min': budgetMin,
      'budget_max': budgetMax,
      'status': status,
      'total_amount': totalAmount,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
