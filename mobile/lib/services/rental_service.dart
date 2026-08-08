import 'api_client.dart';

class RentalService {
  static final RentalService _instance = RentalService._internal();
  factory RentalService() => _instance;
  RentalService._internal();

  final _client = ApiClient();

  // ── Listings ──────────────────────────────────

  /// Search listings with filters
  Future<Map<String, dynamic>> searchListings({
    String? rentalType,
    String? city,
    double? minPrice,
    double? maxPrice,
    String? checkIn,
    String? checkOut,
    String? search,
    String? sort,
    String? direction,
    int page = 1,
    int perPage = 20,
  }) async {
    final params = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };
    if (rentalType != null) params['rental_type'] = rentalType;
    if (city != null) params['city'] = city;
    if (minPrice != null) params['min_price'] = minPrice.toString();
    if (maxPrice != null) params['max_price'] = maxPrice.toString();
    if (checkIn != null) params['check_in'] = checkIn;
    if (checkOut != null) params['check_out'] = checkOut;
    if (search != null) params['search'] = search;
    if (sort != null) params['sort'] = sort;
    if (direction != null) params['direction'] = direction;

    final response = await _client.get('/rentals', queryParams: params);
    return response.data ?? {};
  }

  /// Get listing details
  Future<Map<String, dynamic>> getListing(int listingId) async {
    final response = await _client.get('/rentals/$listingId');
    return response.data ?? {};
  }

  /// Create a new listing
  Future<Map<String, dynamic>> createListing(Map<String, dynamic> data) async {
    final response = await _client.post('/rentals', body: data);
    return response.data ?? {};
  }

  /// Update a listing
  Future<Map<String, dynamic>> updateListing(int listingId, Map<String, dynamic> data) async {
    final response = await _client.put('/rentals/$listingId', body: data);
    return response.data ?? {};
  }

  /// Get owner's listings
  Future<Map<String, dynamic>> getMyListings({int page = 1}) async {
    final response = await _client.get('/rentals/my', queryParams: {'page': page.toString()});
    return response.data ?? {};
  }

  /// Get availability calendar
  Future<Map<String, dynamic>> getCalendar(int listingId, {String? month}) async {
    final params = <String, String>{};
    if (month != null) params['month'] = month;
    final response = await _client.get('/rentals/$listingId/calendar', queryParams: params);
    return response.data ?? {};
  }

  // ── Bookings ──────────────────────────────────

  /// Create a booking
  Future<Map<String, dynamic>> createBooking(int listingId, Map<String, dynamic> data) async {
    final response = await _client.post('/rentals/$listingId/bookings', body: data);
    return response.data ?? {};
  }

  /// Get my bookings (as guest)
  Future<Map<String, dynamic>> getMyBookings({int page = 1}) async {
    final response = await _client.get('/rentals-bookings/my', queryParams: {'page': page.toString()});
    return response.data ?? {};
  }

  /// Get owner's bookings
  Future<Map<String, dynamic>> getOwnerBookings({String? status, int page = 1}) async {
    final params = <String, String>{'page': page.toString()};
    if (status != null) params['status'] = status;
    final response = await _client.get('/rentals-bookings/owner', queryParams: params);
    return response.data ?? {};
  }

  /// Get booking details
  Future<Map<String, dynamic>> getBooking(int bookingId) async {
    final response = await _client.get('/rentals-bookings/$bookingId');
    return response.data ?? {};
  }

  /// Confirm booking (host)
  Future<Map<String, dynamic>> confirmBooking(int bookingId, {String? hostMessage}) async {
    final body = <String, dynamic>{};
    if (hostMessage != null) body['host_message'] = hostMessage;
    final response = await _client.post('/rentals-bookings/$bookingId/confirm', body: body);
    return response.data ?? {};
  }

  /// Reject booking (host)
  Future<Map<String, dynamic>> rejectBooking(int bookingId, String reason) async {
    final response = await _client.post('/rentals-bookings/$bookingId/reject', body: {'reason': reason});
    return response.data ?? {};
  }

  /// Cancel booking (guest)
  Future<Map<String, dynamic>> cancelBooking(int bookingId, String reason) async {
    final response = await _client.post('/rentals-bookings/$bookingId/cancel', body: {'reason': reason});
    return response.data ?? {};
  }

  /// Cancel booking (host)
  Future<Map<String, dynamic>> hostCancelBooking(int bookingId, String reason) async {
    final response = await _client.post('/rentals-bookings/$bookingId/host-cancel', body: {'reason': reason});
    return response.data ?? {};
  }

  // ── Reviews ───────────────────────────────────

  /// Get reviews for a listing
  Future<Map<String, dynamic>> getReviews(int listingId, {int page = 1}) async {
    final response = await _client.get('/rentals/$listingId/reviews', queryParams: {'page': page.toString()});
    return response.data ?? {};
  }

  /// Create a review
  Future<Map<String, dynamic>> createReview(int bookingId, Map<String, dynamic> data) async {
    final response = await _client.post('/rentals-bookings/$bookingId/review', body: data);
    return response.data ?? {};
  }

  // ── Owner Listing Management ────────────────

  /// Delete a listing (owner only)
  Future<Map<String, dynamic>> deleteListing(int listingId) async {
    final response = await _client.delete('/rentals/$listingId');
    return response.data ?? {};
  }

  /// Toggle listing status between active and paused
  Future<Map<String, dynamic>> toggleStatus(int listingId) async {
    // Fetch current listing to know its status
    final listing = await getListing(listingId);
    final currentStatus = listing['data']?['status'] ?? 'active';
    final newStatus = currentStatus == 'active' ? 'paused' : 'active';
    return updateListing(listingId, {'status': newStatus});
  }

  /// Upload photos for a listing (owner only)
  Future<Map<String, dynamic>> uploadPhotos(int listingId, List<String> filePaths) async {
    final formData = <String, dynamic>{};
    for (var i = 0; i < filePaths.length; i++) {
      formData['photos[$i]'] = filePaths[i];
    }
    final response = await _client.post(
      '/rentals/$listingId/photos',
      body: formData,
    );
    return response.data ?? {};
  }

  /// Delete a photo from a listing (owner only)
  Future<Map<String, dynamic>> deletePhoto(int listingId, String photoUrl) async {
    final response = await _client.delete(
      '/rentals/$listingId/photos',
      body: {'photo_url': photoUrl},
    );
    return response.data ?? {};
  }

  /// Block dates for a listing (owner only)
  Future<Map<String, dynamic>> blockDates(int listingId, List<String> dates, {String? reason}) async {
    final body = <String, dynamic>{
      'dates': dates,
    };
    if (reason != null) body['reason'] = reason;
    final response = await _client.post('/rentals/$listingId/block-dates', body: body);
    return response.data ?? {};
  }

  /// Unblock dates for a listing (owner only)
  Future<Map<String, dynamic>> unblockDates(int listingId, List<String> dates) async {
    final response = await _client.post(
      '/rentals/$listingId/unblock-dates',
      body: {'dates': dates},
    );
    return response.data ?? {};
  }
}
