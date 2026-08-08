import '../models/food_item.dart';
import '../models/food_provider.dart';
import '../models/food_order.dart';
import '../models/catering_request.dart';
import '../models/food_cart_item.dart';
import 'api_client.dart';

class FoodService {
  static final FoodService _instance = FoodService._internal();
  factory FoodService() => _instance;
  FoodService._internal();

  final _client = ApiClient();

  // ── Categories ──────────────────────────────────

  Future<List<FoodCategory>> getCategories() async {
    final response = await _client.get('/food/categories');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodCategory.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // ── Search & Browse ─────────────────────────────

  Future<List<FoodItem>> searchItems({
    String? query,
    int? categoryId,
    String? dietary,
    double? minPrice,
    double? maxPrice,
    double? lat,
    double? lng,
    double? radius,
  }) async {
    final params = <String, String>{};
    if (query != null && query.isNotEmpty) params['query'] = query;
    if (categoryId != null) params['category_id'] = categoryId.toString();
    if (dietary != null) params['dietary'] = dietary;
    if (minPrice != null) params['min_price'] = minPrice.toString();
    if (maxPrice != null) params['max_price'] = maxPrice.toString();
    if (lat != null) params['lat'] = lat.toString();
    if (lng != null) params['lng'] = lng.toString();
    if (radius != null) params['radius'] = radius.toString();

    final response = await _client.get('/food/items', queryParams: params);
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<List<FoodItem>> getFeaturedItems() async {
    final response = await _client.get('/food/items/featured');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<FoodItem?> getItemDetail(String slug) async {
    final response = await _client.get('/food/items/$slug');
    if (!response.success || response.data == null) return null;
    final data = response.asMap;
    return FoodItem.fromJson(data);
  }

  Future<FoodProvider?> getProviderDetail(String slug) async {
    final response = await _client.get('/food/providers/$slug');
    if (!response.success || response.data == null) return null;
    final data = response.asMap;
    return FoodProvider.fromJson(data);
  }

  Future<List<FoodItem>> getProviderMenu(int providerId) async {
    final response = await _client.get('/food/providers/$providerId/menu');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // ── Cart ────────────────────────────────────────

  Future<List<FoodCartItem>> getCart() async {
    final response = await _client.get('/food/cart');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodCartItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<FoodCartItem?> addToCart(
    int itemId, {
    int? tierId,
    int quantity = 1,
    String? notes,
  }) async {
    final body = <String, dynamic>{
      'food_item_id': itemId,
      'quantity': quantity,
    };
    if (tierId != null) body['pricing_tier_id'] = tierId;
    if (notes != null) body['special_notes'] = notes;

    final response = await _client.post('/food/cart', body: body);
    if (!response.success) return null;
    return FoodCartItem.fromJson(response.asMap);
  }

  Future<FoodCartItem?> updateCartItem(int cartItemId, int quantity) async {
    final response = await _client.put(
      '/food/cart/$cartItemId',
      body: {'quantity': quantity},
    );
    if (!response.success) return null;
    return FoodCartItem.fromJson(response.asMap);
  }

  Future<bool> removeFromCart(int cartItemId) async {
    final response = await _client.delete('/food/cart/$cartItemId');
    return response.success;
  }

  // ── Orders ──────────────────────────────────────

  Future<FoodOrder?> placeOrder({
    required String deliveryType,
    String? address,
    String? paymentMethod,
    String? instructions,
  }) async {
    final body = <String, dynamic>{
      'delivery_type': deliveryType,
    };
    if (address != null) body['delivery_address'] = address;
    if (paymentMethod != null) body['payment_method'] = paymentMethod;
    if (instructions != null) body['special_instructions'] = instructions;

    final response = await _client.post('/food/orders', body: body);
    if (!response.success) return null;
    return FoodOrder.fromJson(response.asMap);
  }

  Future<List<FoodOrder>> getOrders() async {
    final response = await _client.get('/food/orders');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodOrder.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<FoodOrder?> getOrderDetail(int orderId) async {
    final response = await _client.get('/food/orders/$orderId');
    if (!response.success || response.data == null) return null;
    return FoodOrder.fromJson(response.asMap);
  }

  Future<FoodOrder?> cancelOrder(int orderId, String reason) async {
    final response = await _client.post(
      '/food/orders/$orderId/cancel',
      body: {'reason': reason},
    );
    if (!response.success) return null;
    return FoodOrder.fromJson(response.asMap);
  }

  // ── Catering ────────────────────────────────────

  Future<List<CateringRequest>> getCateringRequests() async {
    final response = await _client.get('/food/catering');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) =>
            CateringRequest.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<CateringRequest?> createCateringRequest(
      Map<String, dynamic> data) async {
    final response = await _client.post('/food/catering', body: data);
    if (!response.success) return null;
    return CateringRequest.fromJson(response.asMap);
  }

  // ── Provider Management ─────────────────────────

  Future<Map<String, dynamic>> getProviderDashboard() async {
    final response = await _client.get('/food/provider/dashboard');
    if (!response.success) return {};
    return response.asMap;
  }

  Future<List<FoodItem>> getProviderMenuItems() async {
    final response = await _client.get('/food/provider/menu');
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<FoodItem?> toggleItemAvailability(int itemId) async {
    final response =
        await _client.post('/food/provider/menu/$itemId/toggle');
    if (!response.success) return null;
    return FoodItem.fromJson(response.asMap);
  }

  Future<List<FoodOrder>> getProviderOrders({String? status}) async {
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    final response =
        await _client.get('/food/provider/orders', queryParams: params);
    if (!response.success) return [];
    final list = response.asList;
    return list
        .map((json) => FoodOrder.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<FoodOrder?> updateOrderStatus(int orderId, String status) async {
    final response = await _client.put(
      '/food/provider/orders/$orderId/status',
      body: {'status': status},
    );
    if (!response.success) return null;
    return FoodOrder.fromJson(response.asMap);
  }

  // ── Provider Registration ─────────────────────────

  Future<ApiResponse> registerProvider(Map<String, dynamic> data) async {
    return _client.post('/food/provider/register', body: data);
  }

  Future<FoodProvider?> getProviderProfile() async {
    final response = await _client.get('/food/provider/profile');
    if (!response.success || response.data == null) return null;
    return FoodProvider.fromJson(response.asMap);
  }

  Future<ApiResponse> updateProviderProfile(Map<String, dynamic> data) async {
    return _client.put('/food/provider/profile', body: data);
  }
}
