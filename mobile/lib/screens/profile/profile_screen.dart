import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../models/user.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isLoading = true;
  User? _user;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    final api = ApiClient();
    final response = await api.getProfile();
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (response.success && response.data is Map) {
          final data = response.data['data'] as Map<String, dynamic>?;
          if (data != null) {
            _user = User.fromJson(data);
          }
        } else {
          _error = response.message ?? 'Failed to load profile';
        }
      });
    }
  }

  Future<void> _logout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppTheme.error),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      final api = ApiClient();
      await api.clearToken();
      if (mounted) {
        context.go('/login');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () => context.push('/settings'),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!,
                          style: const TextStyle(color: AppTheme.error)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadProfile,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadProfile,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(AppTheme.spacingMd),
                    child: Column(
                      children: [
                        // Avatar
                        CircleAvatar(
                          radius: 50,
                          backgroundColor:
                              AppTheme.primary.withValues(alpha: 0.1),
                          backgroundImage: _user?.avatar != null
                              ? NetworkImage(_user!.avatar!)
                              : null,
                          child: _user?.avatar == null
                              ? const Icon(
                                  Icons.person,
                                  size: 50,
                                  color: AppTheme.primary,
                                )
                              : null,
                        ),
                        const SizedBox(height: AppTheme.spacingMd),

                        // Name
                        Text(
                          _user?.name ?? 'User',
                          style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),

                        // Phone
                        Text(
                          _user?.phone ?? '',
                          style: const TextStyle(
                            fontSize: 16,
                            color: AppTheme.textSecondary,
                          ),
                        ),

                        // Email
                        if (_user?.email != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            _user!.email!,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textLight,
                            ),
                          ),
                        ],
                        const SizedBox(height: AppTheme.spacingSm),

                        // Verification Badge
                        if (_user?.isVerified == true)
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppTheme.success,
                              borderRadius: BorderRadius.circular(
                                  AppTheme.radiusFull),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.verified,
                                    color: Colors.white, size: 14),
                                SizedBox(width: 4),
                                Text(
                                  'Verified',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        const SizedBox(height: AppTheme.spacingLg),

                        // Stats Row
                        Container(
                          padding: const EdgeInsets.symmetric(
                              vertical: AppTheme.spacingMd),
                          decoration: BoxDecoration(
                            color: AppTheme.surface,
                            borderRadius:
                                BorderRadius.circular(AppTheme.radiusMd),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.05),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                            children: [
                              _ProfileStat(
                                value: _user?.rating.toStringAsFixed(1) ?? '0.0',
                                label: 'Rating',
                                icon: Icons.star,
                                color: AppTheme.warning,
                              ),
                              Container(
                                  width: 1,
                                  height: 40,
                                  color: AppTheme.divider),
                              _ProfileStat(
                                value: '${_user?.totalTrips ?? 0}',
                                label: 'Trips',
                                icon: Icons.directions_car,
                                color: AppTheme.primary,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingLg),

                        // Menu Section
                        Card(
                          child: Column(
                            children: [
                              _MenuTile(
                                icon: Icons.person,
                                title: 'Edit Profile',
                                onTap: () {
                                  // TODO: Navigate to edit profile
                                },
                              ),
                              const Divider(height: 1),
                              _MenuTile(
                                icon: Icons.notifications,
                                title: 'Notifications',
                                onTap: () => context.push('/notifications'),
                              ),
                              const Divider(height: 1),
                              _MenuTile(
                                icon: Icons.account_balance_wallet,
                                title: 'Wallet',
                                onTap: () => context.push('/wallet'),
                              ),
                              const Divider(height: 1),
                              _MenuTile(
                                icon: Icons.directions_car,
                                title: 'My Vehicles',
                                onTap: () {
                                  // TODO: Navigate to vehicles
                                },
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingMd),

                        Card(
                          child: Column(
                            children: [
                              _MenuTile(
                                icon: Icons.local_hospital,
                                title: 'Emergency Contacts',
                                onTap: () {
                                  // TODO: Navigate to emergency contacts
                                },
                              ),
                              const Divider(height: 1),
                              _MenuTile(
                                icon: Icons.help,
                                title: 'Help & Support',
                                onTap: () {
                                  // TODO: Navigate to help
                                },
                              ),
                              const Divider(height: 1),
                              _MenuTile(
                                icon: Icons.info_outline,
                                title: 'About OruZone',
                                onTap: () {
                                  // TODO: Navigate to about
                                },
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingMd),

                        // Logout
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: _logout,
                            icon: const Icon(Icons.logout,
                                color: AppTheme.error),
                            label: const Text(
                              'Logout',
                              style: TextStyle(color: AppTheme.error),
                            ),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: AppTheme.error),
                              padding: const EdgeInsets.symmetric(
                                  vertical: 14),
                            ),
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingMd),
                      ],
                    ),
                  ),
                ),
    );
  }
}

class _ProfileStat extends StatelessWidget {
  final String value;
  final String label;
  final IconData icon;
  final Color color;

  const _ProfileStat({
    required this.value,
    required this.label,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            color: AppTheme.textSecondary,
          ),
        ),
      ],
    );
  }
}

class _MenuTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final VoidCallback onTap;

  const _MenuTile({
    required this.icon,
    required this.title,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: AppTheme.textSecondary),
      title: Text(title),
      trailing: const Icon(Icons.chevron_right, color: AppTheme.textLight),
      onTap: onTap,
    );
  }
}
