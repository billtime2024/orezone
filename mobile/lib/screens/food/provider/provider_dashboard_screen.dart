import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../services/food_service.dart';
import '../../../theme/app_theme.dart';

class ProviderDashboardScreen extends StatefulWidget {
  const ProviderDashboardScreen({super.key});

  @override
  State<ProviderDashboardScreen> createState() =>
      _ProviderDashboardScreenState();
}

class _ProviderDashboardScreenState extends State<ProviderDashboardScreen> {
  final _service = FoodService();
  Map<String, dynamic> _dashboard = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);
    try {
      final data = await _service.getProviderDashboard();
      if (mounted) {
        setState(() {
          _dashboard = data;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Provider Dashboard'),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadDashboard,
              child: ListView(
                padding: const EdgeInsets.all(AppTheme.spacingMd),
                children: [
                  // Stats cards
                  Row(
                    children: [
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.receipt_long,
                          label: "Today's Orders",
                          value: '${_dashboard['today_orders'] ?? 0}',
                          color: AppTheme.primary,
                        ),
                      ),
                      const SizedBox(width: AppTheme.spacingSm),
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.currency_rupee,
                          label: 'Revenue',
                          value:
                              '₹${(_dashboard['today_revenue'] ?? 0).toStringAsFixed(0)}',
                          color: AppTheme.success,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingSm),
                  Row(
                    children: [
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.star,
                          label: 'Rating',
                          value:
                              '${(_dashboard['avg_rating'] ?? 0).toStringAsFixed(1)}',
                          color: Colors.amber,
                        ),
                      ),
                      const SizedBox(width: AppTheme.spacingSm),
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.pending_actions,
                          label: 'Pending',
                          value: '${_dashboard['pending_orders'] ?? 0}',
                          color: AppTheme.warning,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingLg),

                  // Quick actions
                  const Text('Quick Actions',
                      style: TextStyle(
                          fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: AppTheme.spacingSm),
                  Row(
                    children: [
                      Expanded(
                        child: _buildActionCard(
                          icon: Icons.restaurant_menu,
                          label: 'View Menu',
                          onTap: () => context.push('/food/provider/menu'),
                        ),
                      ),
                      const SizedBox(width: AppTheme.spacingSm),
                      Expanded(
                        child: _buildActionCard(
                          icon: Icons.receipt,
                          label: 'Order Queue',
                          onTap: () => context.push('/food/provider/orders'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppTheme.spacingLg),

                  // Recent orders
                  const Text('Recent Orders',
                      style: TextStyle(
                          fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: AppTheme.spacingSm),
                  if (_dashboard['recent_orders'] == null ||
                      (_dashboard['recent_orders'] as List).isEmpty)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Text('No recent orders',
                            style: TextStyle(color: AppTheme.textSecondary)),
                      ),
                    )
                  else
                    ...(_dashboard['recent_orders'] as List).map((order) {
                      return Card(
                        margin:
                            const EdgeInsets.only(bottom: AppTheme.spacingSm),
                        child: ListTile(
                          leading: const CircleAvatar(
                            backgroundColor: AppTheme.primary,
                            child: Icon(Icons.receipt, color: Colors.white),
                          ),
                          title: Text(
                              'Order #${order['order_number'] ?? ''}',
                              style: const TextStyle(
                                  fontWeight: FontWeight.w600)),
                          subtitle:
                              Text('₹${order['total_amount'] ?? 0}'),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppTheme.primary.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                                order['status'] ?? '',
                                style: const TextStyle(
                                    fontSize: 11, color: AppTheme.primary)),
                          ),
                        ),
                      );
                    }),
                ],
              ),
            ),
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 8),
            Text(value,
                style: TextStyle(
                    fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 4),
            Text(label,
                style: const TextStyle(
                    fontSize: 12, color: AppTheme.textSecondary)),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          child: Column(
            children: [
              Icon(icon, color: AppTheme.primary, size: 32),
              const SizedBox(height: 8),
              Text(label,
                  style: const TextStyle(
                      fontSize: 14, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ),
    );
  }
}
