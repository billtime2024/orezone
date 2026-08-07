import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _phoneController = TextEditingController();
  final _phoneFocusNode = FocusNode();
  String _selectedCountryCode = '+91';
  bool _isLoading = false;

  final List<Map<String, String>> _countryCodes = [
    {'code': '+91', 'name': 'India', 'flag': '🇮🇳'},
    {'code': '+1', 'name': 'USA', 'flag': '🇺🇸'},
    {'code': '+44', 'name': 'UK', 'flag': '🇬🇧'},
    {'code': '+61', 'name': 'Australia', 'flag': '🇦🇺'},
    {'code': '+971', 'name': 'UAE', 'flag': '🇦🇪'},
    {'code': '+966', 'name': 'Saudi Arabia', 'flag': '🇸🇦'},
  ];

  @override
  void dispose() {
    _phoneController.dispose();
    _phoneFocusNode.dispose();
    super.dispose();
  }

  bool _validatePhone() {
    final phone = _phoneController.text.trim();
    if (phone.isEmpty) {
      Fluttertoast.showToast(msg: 'Please enter your phone number');
      return false;
    }
    if (phone.length < 8 || phone.length > 15) {
      Fluttertoast.showToast(msg: 'Please enter a valid phone number');
      return false;
    }
    return true;
  }

  Future<void> _sendOtp() async {
    if (!_validatePhone()) return;

    setState(() => _isLoading = true);

    final apiClient = ApiClient();
    final phone = _phoneController.text.trim();

    try {
      final response = await apiClient.sendOtp(
        phone,
        countryCode: _selectedCountryCode,
      );

      if (!mounted) return;

      if (response.success) {
        Fluttertoast.showToast(msg: 'OTP sent successfully');
        context.push(
          '/otp/$phone?countryCode=$_selectedCountryCode',
        );
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Failed to send OTP',
        );
      }
    } catch (e) {
      if (mounted) {
        Fluttertoast.showToast(msg: 'An error occurred. Please try again.');
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppTheme.spacingLg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: AppTheme.spacingXxl),
              // Header
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppTheme.primary,
                    borderRadius: BorderRadius.circular(AppTheme.radiusLg),
                  ),
                  child: const Icon(
                    Icons.directions_car_filled,
                    size: 40,
                    color: Colors.white,
                  ),
                ),
              ),
              const SizedBox(height: AppTheme.spacingLg),
              Text(
                'Welcome to Orozone',
                textAlign: TextAlign.center,
                style: AppTheme.lightTheme.textTheme.headlineMedium,
              ),
              const SizedBox(height: AppTheme.spacingSm),
              Text(
                'Enter your phone number to get started',
                textAlign: TextAlign.center,
                style: AppTheme.lightTheme.textTheme.bodyMedium,
              ),
              const SizedBox(height: AppTheme.spacingXl),
              // Phone input with country code
              Row(
                children: [
                  // Country code selector
                  Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: AppTheme.border),
                      borderRadius: BorderRadius.circular(AppTheme.radiusMd),
                      color: AppTheme.surface,
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTheme.spacingSm,
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedCountryCode,
                        items: _countryCodes.map((country) {
                          return DropdownMenuItem<String>(
                            value: country['code'],
                            child: Text(
                              '${country['flag']} ${country['code']}',
                              style: const TextStyle(fontSize: 14),
                            ),
                          );
                        }).toList(),
                        onChanged: (value) {
                          if (value != null) {
                            setState(() => _selectedCountryCode = value);
                          }
                        },
                      ),
                    ),
                  ),
                  const SizedBox(width: AppTheme.spacingSm),
                  // Phone number input
                  Expanded(
                    child: TextField(
                      controller: _phoneController,
                      focusNode: _phoneFocusNode,
                      keyboardType: TextInputType.phone,
                      inputFormatters: [
                        FilteringTextInputFormatter.digitsOnly,
                        LengthLimitingTextInputFormatter(15),
                      ],
                      decoration: const InputDecoration(
                        hintText: 'Phone number',
                        prefixIcon: Icon(Icons.phone_outlined),
                      ),
                      onSubmitted: (_) => _sendOtp(),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppTheme.spacingXl),
              // Send OTP button
              ElevatedButton(
                onPressed: _isLoading ? null : _sendOtp,
                child: _isLoading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.white,
                          ),
                        ),
                      )
                    : const Text('Send OTP'),
              ),
              const SizedBox(height: AppTheme.spacingLg),
              // Terms
              Text(
                'By continuing, you agree to our Terms of Service and Privacy Policy',
                textAlign: TextAlign.center,
                style: AppTheme.lightTheme.textTheme.bodySmall,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
