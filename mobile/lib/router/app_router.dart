import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../screens/auth/splash_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/otp_screen.dart';
import '../screens/auth/profile_setup_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/search/search_screen.dart';
import '../screens/search/trip_details_screen.dart';
import '../screens/main_scaffold.dart';
import '../screens/host/offer_trip_screen.dart';
import '../screens/host/vehicle_selector_screen.dart';
import '../screens/host/booking_inbox_screen.dart';
import '../screens/host/trip_management_screen.dart';
import '../screens/activity/my_trips_screen.dart';
import '../screens/activity/my_bookings_screen.dart';
import '../screens/activity/wallet_screen.dart';
import '../screens/notifications/notifications_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/profile/settings_screen.dart';
import '../screens/rentals/rental_list_screen.dart';
import '../screens/rentals/rental_detail_screen.dart';
import '../screens/rentals/my_rental_bookings_screen.dart';
import '../screens/rentals/owner_rental_bookings_screen.dart';
import '../screens/rentals/my_listings_screen.dart';
import '../screens/food/food_home_screen.dart';
import '../screens/food/food_search_screen.dart';
import '../screens/food/food_provider_detail_screen.dart';
import '../screens/food/food_item_detail_screen.dart';
import '../screens/food/food_cart_screen.dart';
import '../screens/food/food_order_history_screen.dart';
import '../screens/food/food_order_tracking_screen.dart';
import '../screens/food/food_catering_request_screen.dart';
import '../screens/food/provider/provider_dashboard_screen.dart';
import '../screens/food/provider/provider_menu_screen.dart';
import '../screens/food/provider/provider_order_queue_screen.dart';
import '../screens/food/provider/provider_registration_screen.dart';

class AppRouter {
  static final GoRouter router = GoRouter(
    initialLocation: '/',
    debugLogDiagnostics: false,
    routes: [
      GoRoute(
        path: '/',
        name: 'splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/otp/:phone',
        name: 'otp',
        builder: (context, state) {
          final phone = state.pathParameters['phone'] ?? '';
          final countryCode = state.uri.queryParameters['countryCode'] ?? '+91';
          return OtpScreen(phone: phone, countryCode: countryCode);
        },
      ),
      GoRoute(
        path: '/profile-setup',
        name: 'profileSetup',
        builder: (context, state) => const ProfileSetupScreen(),
      ),

      // Main scaffold with bottom nav
      ShellRoute(
        builder: (context, state, child) => MainScaffold(child: child),
        routes: [
          GoRoute(
            path: '/home',
            name: 'home',
            builder: (context, state) => const HomeScreen(),
          ),
          GoRoute(
            path: '/search',
            name: 'search',
            builder: (context, state) => const SearchScreen(),
          ),
          GoRoute(
            path: '/offer-trip',
            name: 'offerTrip',
            builder: (context, state) => const OfferTripScreen(),
          ),
          GoRoute(
            path: '/my-activity',
            name: 'myActivity',
            builder: (context, state) => const _MyActivityScreen(),
          ),
          GoRoute(
            path: '/profile',
            name: 'profile',
            builder: (context, state) => const ProfileScreen(),
          ),
        ],
      ),

      GoRoute(
        path: '/trip/:id',
        name: 'tripDetails',
        builder: (context, state) {
          final tripId = state.pathParameters['id'] ?? '';
          return TripDetailsScreen(tripId: tripId);
        },
      ),

      // Host flow
      GoRoute(
        path: '/offer-trip/new',
        name: 'offerTripNew',
        builder: (context, state) => const OfferTripScreen(),
      ),
      GoRoute(
        path: '/vehicles',
        name: 'vehicles',
        builder: (context, state) => const VehicleSelectorScreen(),
      ),
      GoRoute(
        path: '/booking-inbox',
        name: 'bookingInbox',
        builder: (context, state) => const BookingInboxScreen(),
      ),
      GoRoute(
        path: '/trip-management',
        name: 'tripManagement',
        builder: (context, state) => const TripManagementScreen(),
      ),

      // Activity
      GoRoute(
        path: '/my-trips',
        name: 'myTrips',
        builder: (context, state) => const MyTripsHostScreen(),
      ),
      GoRoute(
        path: '/my-bookings',
        name: 'myBookings',
        builder: (context, state) => const MyBookingsTravelerScreen(),
      ),
      GoRoute(
        path: '/wallet',
        name: 'wallet',
        builder: (context, state) => const WalletScreen(),
      ),

      // Notifications
      GoRoute(
        path: '/notifications',
        name: 'notifications',
        builder: (context, state) => const NotificationsScreen(),
      ),

      // Settings
      GoRoute(
        path: '/settings',
        name: 'settings',
        builder: (context, state) => const SettingsScreen(),
      ),

      // Rentals
      GoRoute(
        path: '/rentals',
        name: 'rentals',
        builder: (context, state) => const RentalListScreen(),
      ),
      GoRoute(
        path: '/rental/:id',
        name: 'rentalDetails',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id'] ?? '0');
          return RentalDetailScreen(listingId: id);
        },
      ),
      GoRoute(
        path: '/my-rental-bookings',
        name: 'myRentalBookings',
        builder: (context, state) => const MyRentalBookingsScreen(),
      ),
      GoRoute(
        path: '/owner-rental-bookings',
        name: 'ownerRentalBookings',
        builder: (context, state) => const OwnerRentalBookingsScreen(),
      ),
      GoRoute(
        path: '/rentals/my',
        name: 'myListings',
        builder: (context, state) => const MyListingsScreen(),
      ),
      GoRoute(
        path: '/rentals/create',
        name: 'createListing',
        builder: (context, state) => const _CreateListingPlaceholder(),
      ),
      GoRoute(
        path: '/rentals/:id/edit',
        name: 'editListing',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id'] ?? '0');
          return _EditListingPlaceholder(listingId: id);
        },
      ),

      // Food Services
      GoRoute(
        path: '/food',
        name: 'food',
        builder: (context, state) => const FoodHomeScreen(),
      ),
      GoRoute(
        path: '/food/search',
        name: 'foodSearch',
        builder: (context, state) => const FoodSearchScreen(),
      ),
      GoRoute(
        path: '/food/provider/:slug',
        name: 'foodProviderDetail',
        builder: (context, state) {
          final slug = state.pathParameters['slug'] ?? '';
          return FoodProviderDetailScreen(providerSlug: slug);
        },
      ),
      GoRoute(
        path: '/food/item/:slug',
        name: 'foodItemDetail',
        builder: (context, state) {
          final slug = state.pathParameters['slug'] ?? '';
          return FoodItemDetailScreen(itemSlug: slug);
        },
      ),
      GoRoute(
        path: '/food/cart',
        name: 'foodCart',
        builder: (context, state) => const FoodCartScreen(),
      ),
      GoRoute(
        path: '/food/orders',
        name: 'foodOrders',
        builder: (context, state) => const FoodOrderHistoryScreen(),
      ),
      GoRoute(
        path: '/food/orders/:id',
        name: 'foodOrderTracking',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id'] ?? '0');
          return FoodOrderTrackingScreen(orderId: id);
        },
      ),
      GoRoute(
        path: '/food/catering',
        name: 'foodCatering',
        builder: (context, state) => const FoodCateringRequestScreen(),
      ),
      GoRoute(
        path: '/food/provider/registration',
        name: 'providerRegistration',
        builder: (context, state) => const ProviderRegistrationScreen(),
      ),
      GoRoute(
        path: '/food/provider/dashboard',
        name: 'providerDashboard',
        builder: (context, state) => const ProviderDashboardScreen(),
      ),
      GoRoute(
        path: '/food/provider/menu',
        name: 'providerMenu',
        builder: (context, state) => const ProviderMenuScreen(),
      ),
      GoRoute(
        path: '/food/provider/orders',
        name: 'providerOrders',
        builder: (context, state) => const ProviderOrderQueueScreen(),
      ),
    ],
  );
}

/// My Activity hub screen with tabs for trips and bookings
class _MyActivityScreen extends StatelessWidget {
  const _MyActivityScreen();

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('My Activity'),
          bottom: const TabBar(
            tabs: [
              Tab(icon: Icon(Icons.directions_car), text: 'My Trips'),
              Tab(icon: Icon(Icons.receipt_long), text: 'My Bookings'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            MyTripsHostScreen(),
            MyBookingsTravelerScreen(),
          ],
        ),
      ),
    );
  }
}

/// Placeholder for create listing screen
class _CreateListingPlaceholder extends StatelessWidget {
  const _CreateListingPlaceholder();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Create Listing')),
      body: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.add_home, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text('Create Rental Listing', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            SizedBox(height: 8),
            Text('Coming soon', style: TextStyle(color: Colors.grey)),
          ],
        ),
      ),
    );
  }
}

/// Placeholder for edit listing screen
class _EditListingPlaceholder extends StatelessWidget {
  final int listingId;
  const _EditListingPlaceholder({required this.listingId});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Edit Listing')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.edit, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            const Text('Edit Listing', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text('Listing #$listingId - Coming soon', style: const TextStyle(color: Colors.grey)),
          ],
        ),
      ),
    );
  }
}
