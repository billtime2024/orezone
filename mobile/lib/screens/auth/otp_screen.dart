import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class OtpScreen extends StatefulWidget {
  final String phone;
  final String countryCode;

  const OtpScreen({
    super.key,
    required this.phone,
    this.countryCode = '+91',
  });

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final List<TextEditingController> _controllers =
      List.generate(6, (_) => TextEditingController());
  final List<FocusNode> _focusNodes = List.generate(6, (_) => FocusNode());
  bool _isLoading = false;
  bool _canResend = false;
  int _resendCountdown = 30;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _startResendTimer();
  }

  @override
  void dispose() {
    for (final controller in _controllers) {
      controller.dispose();
    }
    for (final node in _focusNodes) {
      node.dispose();
    }
    _timer?.cancel();
    super.dispose();
  }

  void _startResendTimer() {
    _canResend = false;
    _resendCountdown = 30;
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          _resendCountdown--;
          if (_resendCountdown <= 0) {
            _canResend = true;
            timer.cancel();
          }
        });
      } else {
        timer.cancel();
      }
    });
  }

  String get _otpCode {
    return _controllers.map((c) => c.text).join();
  }

  bool get _isOtpComplete {
    return _otpCode.length == 6;
  }

  void _onOtpChanged(int index, String value) {
    if (value.length == 1 && index < 5) {
      _focusNodes[index + 1].requestFocus();
    }

    if (_isOtpComplete) {
      _verifyOtp();
    }
  }

  void _onKeyPress(int index, KeyEvent event) {
    if (event is KeyDownEvent &&
        event.logicalKey == LogicalKeyboardKey.backspace &&
        _controllers[index].text.isEmpty &&
        index > 0) {
      _controllers[index - 1].clear();
      _focusNodes[index - 1].requestFocus();
    }
  }

  Future<void> _verifyOtp() async {
    if (!_isOtpComplete) return;

    setState(() => _isLoading = true);

    final apiClient = ApiClient();
    final otp = _otpCode;

    try {
      final response = await apiClient.verifyOtp(
        widget.phone,
        otp,
        countryCode: widget.countryCode,
      );

      if (!mounted) return;

      if (response.success) {
        // Save token
        final token = response.data?['token'] as String?;
        if (token != null) {
          await apiClient.setToken(token);
        }

        // Check if profile is complete
        final profileComplete = response.data?['profile_complete'] as bool? ?? false;

        Fluttertoast.showToast(msg: 'OTP verified successfully');

        if (profileComplete) {
          context.go('/home');
        } else {
          context.go('/profile-setup');
        }
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Invalid OTP',
        );
        // Clear OTP fields on error
        for (final controller in _controllers) {
          controller.clear();
        }
        _focusNodes[0].requestFocus();
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

  Future<void> _resendOtp() async {
    if (!_canResend) return;

    setState(() => _isLoading = true);

    final apiClient = ApiClient();

    try {
      final response = await apiClient.sendOtp(
        widget.phone,
        countryCode: widget.countryCode,
      );

      if (!mounted) return;

      if (response.success) {
        Fluttertoast.showToast(msg: 'OTP resent successfully');
        _startResendTimer();
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Failed to resend OTP',
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
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          color: AppTheme.textPrimary,
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            Text(
              'Verify OTP',
              style: AppTheme.lightTheme.textTheme.headlineMedium,
            ),
            const SizedBox(height: AppTheme.spacingSm),
            Text(
              'Enter the 6-digit code sent to\n${widget.countryCode} ${widget.phone}',
              style: AppTheme.lightTheme.textTheme.bodyMedium,
            ),
            const SizedBox(height: AppTheme.spacingXl),
            // OTP input fields
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: List.generate(6, (index) {
                return SizedBox(
                  width: 50,
                  height: 60,
                  child: KeyboardListener(
                    focusNode: FocusNode(),
                    onKeyEvent: (event) => _onKeyPress(index, event),
                    child: TextField(
                      controller: _controllers[index],
                      focusNode: _focusNodes[index],
                      textAlign: TextAlign.center,
                      keyboardType: TextInputType.number,
                      maxLength: 1,
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.digitsOnly,
                      ],
                      decoration: InputDecoration(
                        counterText: '',
                        contentPadding: EdgeInsets.zero,
                        border: OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(AppTheme.radiusMd),
                          borderSide: const BorderSide(
                            color: AppTheme.border,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(AppTheme.radiusMd),
                          borderSide: const BorderSide(
                            color: AppTheme.primary,
                            width: 2,
                          ),
                        ),
                      ),
                      onChanged: (value) => _onOtpChanged(index, value),
                    ),
                  ),
                );
              }),
            ),
            const SizedBox(height: AppTheme.spacingXl),
            // Verify button
            ElevatedButton(
              onPressed: (_isLoading || !_isOtpComplete) ? null : _verifyOtp,
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
                  : const Text('Verify OTP'),
            ),
            const SizedBox(height: AppTheme.spacingLg),
            // Resend OTP
            Center(
              child: _canResend
                  ? TextButton(
                      onPressed: _resendOtp,
                      child: const Text(
                        'Resend OTP',
                        style: TextStyle(
                          color: AppTheme.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    )
                  : Text(
                      'Resend OTP in ${_resendCountdown}s',
                      style: const TextStyle(
                        color: AppTheme.textSecondary,
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
