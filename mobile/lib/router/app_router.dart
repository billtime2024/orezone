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
