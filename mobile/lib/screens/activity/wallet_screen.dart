import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../../models/wallet.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  bool _isLoading = true;
  Wallet? _wallet;
  List<Map<String, dynamic>> _transactions = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadWallet();
  }

  Future<void> _loadWallet() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    final api = ApiClient();
    final response = await api.get('/wallet');
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (response.success && response.data is Map) {
          final data = response.data['data'] as Map<String, dynamic>?;
          if (data != null) {
            _wallet = Wallet.fromJson(data);
          }
          final txns = response.data['transactions'] as List? ?? [];
          _transactions = txns.cast<Map<String, dynamic>>();
        } else {
          _error = response.message ?? 'Failed to load wallet';
        }
      });
    }
  }

  void _showTopUpDialog() {
    final controller = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          left: AppTheme.spacingMd,
          right: AppTheme.spacingMd,
          top: AppTheme.spacingMd,
          bottom: MediaQuery.of(context).viewInsets.bottom + AppTheme.spacingMd,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Top Up Wallet',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            Row(
              children: [100, 200, 500, 1000].map((amount) {
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text('₹$amount'),
                    selected: controller.text == '$amount',
                    onSelected: (_) {
                      controller.text = '$amount';
                      setState(() {});
                    },
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            TextField(
              controller: controller,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Amount (₹)',
                prefixIcon: Icon(Icons.attach_money),
              ),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            SizedBox(
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  final amount = double.tryParse(controller.text);
                  if (amount == null || amount <= 0) {
                    Fluttertoast.showToast(msg: 'Enter a valid amount');
                    return;
                  }
                  Navigator.pop(context);
                  _topUp(amount);
                },
                child: const Text('Top Up'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _topUp(double amount) async {
    final api = ApiClient();
    final response = await api.post('/wallet/topup', body: {
      'amount': amount,
    });
    if (mounted) {
      if (response.success) {
        Fluttertoast.showToast(
          msg: '₹${amount.toStringAsFixed(0)} added to wallet',
          backgroundColor: AppTheme.success,
        );
        _loadWallet();
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Failed to top up',
          backgroundColor: AppTheme.error,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Wallet')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: AppTheme.error)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadWallet,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadWallet,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(AppTheme.spacingMd),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Balance Card
                        Container(
                          padding: const EdgeInsets.all(AppTheme.spacingLg),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppTheme.primaryDark, AppTheme.primary],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius:
                                BorderRadius.circular(AppTheme.radiusLg),
                          ),
                          child: Column(
                            children: [
                              const Text(
                                'Available Balance',
                                style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: 14,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _wallet?.formattedBalance ?? '₹0.00',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 36,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: AppTheme.spacingMd),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  _WalletMiniStat(
                                    label: 'Earned',
                                    value: _wallet?.formattedEarned ?? '₹0',
                                  ),
                                  Container(
                                    width: 1,
                                    height: 30,
                                    color: Colors.white24,
                                    margin: const EdgeInsets.symmetric(
                                        horizontal: AppTheme.spacingLg),
                                  ),
                                  _WalletMiniStat(
                                    label: 'Spent',
                                    value: _wallet?.formattedSpent ?? '₹0',
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingMd),

                        // Top Up Button
                        SizedBox(
                          height: 48,
                          child: ElevatedButton.icon(
                            onPressed: _showTopUpDialog,
                            icon: const Icon(Icons.add),
                            label: const Text('Top Up'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.accent,
                            ),
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingLg),

                        // Transaction History
                        const Text(
                          'Transaction History',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: AppTheme.spacingSm),

                        if (_transactions.isEmpty)
                          Center(
                            child: Padding(
                              padding: const EdgeInsets.all(AppTheme.spacingXl),
                              child: Column(
                                children: [
                                  Icon(
                                    Icons.receipt_long_outlined,
                                    size: 48,
                                    color: AppTheme.textLight,
                                  ),
                                  const SizedBox(height: 12),
                                  const Text(
                                    'No transactions yet',
                                    style: TextStyle(
                                      color: AppTheme.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          )
                        else
                          ..._transactions.map((txn) {
                            final isCredit = (txn['amount'] ?? 0) >= 0;
                            return Card(
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: isCredit
                                      ? AppTheme.success.withValues(alpha: 0.1)
                                      : AppTheme.error.withValues(alpha: 0.1),
                                  child: Icon(
                                    isCredit
                                        ? Icons.add_circle_outline
                                        : Icons.remove_circle_outline,
                                    color: isCredit
                                        ? AppTheme.success
                                        : AppTheme.error,
                                  ),
                                ),
                                title: Text(txn['description'] ?? 'Transaction'),
                                subtitle: Text(
                                  txn['created_at'] ?? '',
                                  style: const TextStyle(fontSize: 12),
                                ),
                                trailing: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    Text(
                                      '${isCredit ? '+' : ''}₹${(txn['amount'] ?? 0).abs().toStringAsFixed(0)}',
                                      style: TextStyle(
                                        color: isCredit
                                            ? AppTheme.success
                                            : AppTheme.error,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                    Text(
                                      '₹${(txn['balance_after'] ?? 0).toStringAsFixed(0)}',
                                      style: const TextStyle(
                                        fontSize: 11,
                                        color: AppTheme.textLight,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }),
                      ],
                    ),
                  ),
                ),
    );
  }
}

class _WalletMiniStat extends StatelessWidget {
  final String label;
  final String value;

  const _WalletMiniStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          label,
          style: const TextStyle(color: Colors.white60, fontSize: 12),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}
