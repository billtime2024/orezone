import 'package:flutter/material.dart';

class OtpScreen extends StatefulWidget {
  final String phone;
  
  const OtpScreen({super.key, required this.phone});
  
  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final _otpController = TextEditingController();
  
  @override
  void dispose() {
    _otpController.dispose();
    super.dispose();
  }
  
  void _verifyOtp() {
    final otp = _otpController.text.trim();
    if (otp.length == 6) {
      // TODO: Implement OTP verification
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('OTP verification coming soon')),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Verify OTP'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Icon(
              Icons.lock_outline,
              size: 64,
              color: Color(0xFF0D6EFD),
            ),
            const SizedBox(height: 24),
            Text(
              'Enter the OTP sent to\n${widget.phone}',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 18,
                color: Color(0xFF6C757D),
              ),
            ),
            const SizedBox(height: 32),
            TextFormField(
              controller: _otpController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 24,
                letterSpacing: 8,
              ),
              decoration: const InputDecoration(
                labelText: 'OTP',
                counterText: '',
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _verifyOtp,
              child: const Text('Verify'),
            ),
            const SizedBox(height: 16),
            TextButton(
              onPressed: () {
                // TODO: Resend OTP
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('OTP resent')),
                );
              },
              child: const Text('Resend OTP'),
            ),
          ],
        ),
      ),
    );
  }
}
