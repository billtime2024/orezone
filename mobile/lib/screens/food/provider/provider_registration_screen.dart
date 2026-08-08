import 'dart:io';
import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import '../../../services/api_client.dart';
import '../../../theme/app_theme.dart';

/// Multi-step provider registration form.
/// Steps: 1) Type Selection → 2) Business Details → 3) Location → 4) Documents → 5) Bank Details → 6) Review
class ProviderRegistrationScreen extends StatefulWidget {
  const ProviderRegistrationScreen({super.key});

  @override
  State<ProviderRegistrationScreen> createState() =>
      _ProviderRegistrationScreenState();
}

class _ProviderRegistrationScreenState extends State<ProviderRegistrationScreen> {
  final _pageController = PageController();
  final _formKeys = List.generate(4, (_) => GlobalKey<FormState>());

  // Step 1 — Type
  String? _providerType;

  // Step 2 — Business details
  final _businessNameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();

  // Step 3 — Location
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _stateController = TextEditingController();
  final _pincodeController = TextEditingController();
  double? _latitude;
  double? _longitude;

  // Step 4 — Documents
  String? _fssaiLicense;
  DateTime? _fssaiExpiry;
  String? _gstNumber;
  String? _panNumber;
  final List<File> _documentFiles = [];
  String _documentType = 'fssai';

  // Step 5 — Bank details
  final _bankAccountController = TextEditingController();
  final _bankIfscController = TextEditingController();
  final _upiIdController = TextEditingController();

  // Submission
  bool _isSubmitting = false;
  bool _agreedToTerms = false;

  static const _brandColor = Color(0xFF2E7D5B);
  static const _brandLight = Color(0xFFE8F5E9);

  int _currentStep = 0;
  static const int _totalSteps = 6;

  @override
  void dispose() {
    _pageController.dispose();
    _businessNameController.dispose();
    _descriptionController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _stateController.dispose();
    _pincodeController.dispose();
    _bankAccountController.dispose();
    _bankIfscController.dispose();
    _upiIdController.dispose();
    super.dispose();
  }

  // ── Navigation ──────────────────────────────────────────────────

  void _nextStep() {
    // Validate current step before advancing
    if (_currentStep == 0 && _providerType == null) {
      _showSnack('Please select a provider type');
      return;
    }
    if (_currentStep == 1 && !_formKeys[0].currentState!.validate()) return;
    if (_currentStep == 2 && !_formKeys[1].currentState!.validate()) return;

    if (_currentStep < _totalSteps - 1) {
      setState(() => _currentStep++);
      _pageController.nextPage(
        duration: const Duration(milliseconds: 350),
        curve: Curves.easeInOut,
      );
    }
  }

  void _prevStep() {
    if (_currentStep > 0) {
      setState(() => _currentStep--);
      _pageController.previousPage(
        duration: const Duration(milliseconds: 350),
        curve: Curves.easeInOut,
      );
    }
  }

  // ── Submission ──────────────────────────────────────────────────

  Future<void> _submitRegistration() async {
    if (!_agreedToTerms) {
      _showSnack('Please agree to the terms & conditions');
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final client = ApiClient();

      // Step 1: Register provider
      final response = await client.post('/food/provider/register', body: {
        'provider_type': _providerType,
        'business_name': _businessNameController.text.trim(),
        'description': _descriptionController.text.trim(),
        'phone': _phoneController.text.trim(),
        'email': _emailController.text.trim(),
        'address': _addressController.text.trim(),
        'latitude': _latitude ?? 0.0,
        'longitude': _longitude ?? 0.0,
        'city': _cityController.text.trim(),
        'state': _stateController.text.trim(),
        'pincode': _pincodeController.text.trim(),
        'fssai_license': _fssaiLicense?.trim(),
        'fssai_expiry': _fssaiExpiry?.toIso8601String().split('T').first,
        'gst_number': _gstNumber?.trim(),
        'pan_number': _panNumber?.trim(),
      });

      if (!response.success) {
        _showSnack(response.message ?? 'Registration failed');
        return;
      }

      // Step 2: Upload documents if any
      if (_documentFiles.isNotEmpty) {
        final dio = Dio();
        final token = await client.getToken();
        final formData = FormData.fromMap({
          'document_type': _documentType,
          'documents[]': _documentFiles
              .map((f) => MultipartFile.fromFileSync(f.path))
              .toList(),
        });
        await dio.post(
          '${client.baseUrl}/food/provider/documents',
          data: formData,
          options: Options(
            headers: {'Authorization': 'Bearer $token'},
          ),
        );
      }

      // Step 3: Update bank details
      await client.put('/food/provider/profile', body: {
        'bank_account_number': _bankAccountController.text.trim(),
        'bank_ifsc': _bankIfscController.text.trim(),
        'upi_id': _upiIdController.text.trim(),
      });

      if (mounted) {
        _showSuccessDialog();
      }
    } catch (e) {
      _showSnack('Error: $e');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red.shade600),
    );
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        icon: const Icon(Icons.check_circle, color: _brandColor, size: 64),
        title: const Text('Registration Submitted!'),
        content: const Text(
          'Your provider profile is now pending verification. '
          'We\'ll review your documents and activate your account within 24-48 hours.',
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              context.go('/food');
            },
            child: const Text('Back to Food Home'),
          ),
        ],
      ),
    );
  }

  // ── Document picker ─────────────────────────────────────────────

  Future<void> _pickDocument() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
      allowMultiple: true,
    );
    if (result != null && result.files.isNotEmpty) {
      setState(() {
        _documentFiles.addAll(result.files.map((f) => File(f.path!)));
      });
    }
  }

  void _removeDocument(int index) {
    setState(() => _documentFiles.removeAt(index));
  }

  // ── Build ───────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: _brandColor,
        foregroundColor: Colors.white,
        title: const Text(
          'Become a Provider',
          style: TextStyle(fontWeight: FontWeight.w600),
        ),
        elevation: 0,
      ),
      body: Column(
        children: [
          _buildProgressIndicator(),
          Expanded(
            child: PageView(
              controller: _pageController,
              physics: const NeverScrollableScrollPhysics(),
              children: [
                _buildStep1TypeSelection(),
                _buildStep2BusinessDetails(),
                _buildStep3Location(),
                _buildStep4Documents(),
                _buildStep5BankDetails(),
                _buildStep6Review(),
              ],
            ),
          ),
          _buildBottomNav(),
        ],
      ),
    );
  }

  // ── Progress indicator ──────────────────────────────────────────

  Widget _buildProgressIndicator() {
    final stepLabels = ['Type', 'Business', 'Location', 'Docs', 'Bank', 'Review'];
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      color: Colors.grey.shade50,
      child: Row(
        children: List.generate(_totalSteps, (i) {
          final isActive = i <= _currentStep;
          final isCurrent = i == _currentStep;
          return Expanded(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    if (i > 0)
                      Expanded(
                        child: Container(
                          height: 2,
                          color: isActive ? _brandColor : Colors.grey.shade300,
                        ),
                      ),
                    Container(
                      width: 24,
                      height: 24,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: isActive ? _brandColor : Colors.grey.shade300,
                        border: isCurrent
                            ? Border.all(color: _brandColor, width: 2)
                            : null,
                      ),
                      child: Center(
                        child: isActive && !isCurrent
                            ? const Icon(Icons.check, size: 14, color: Colors.white)
                            : Text(
                                '${i + 1}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isActive ? Colors.white : Colors.grey.shade600,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                      ),
                    ),
                    if (i < _totalSteps - 1)
                      Expanded(
                        child: Container(
                          height: 2,
                          color: i < _currentStep ? _brandColor : Colors.grey.shade300,
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  stepLabels[i],
                  style: TextStyle(
                    fontSize: 10,
                    color: isCurrent ? _brandColor : Colors.grey.shade500,
                    fontWeight: isCurrent ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ],
            ),
          );
        }),
      ),
    );
  }

  // ── Step 1: Type Selection ──────────────────────────────────────

  Widget _buildStep1TypeSelection() {
    final types = [
      _ProviderTypeOption(
        type: 'restaurant',
        icon: Icons.restaurant,
        title: 'Restaurant',
        subtitle: 'Full-service dining establishment',
      ),
      _ProviderTypeOption(
        type: 'cloud_kitchen',
        icon: Icons.kitchen,
        title: 'Cloud Kitchen',
        subtitle: 'Delivery-only kitchen operation',
      ),
      _ProviderTypeOption(
        type: 'home_chef',
        icon: Icons.home_outlined,
        title: 'Home Chef',
        subtitle: 'Cook from your home kitchen',
      ),
      _ProviderTypeOption(
        type: 'hotel',
        icon: Icons.apartment,
        title: 'Hotel',
        subtitle: 'Hotel dining and room service',
      ),
      _ProviderTypeOption(
        type: 'catering',
        icon: Icons.celebration_outlined,
        title: 'Catering Service',
        subtitle: 'Events, parties & bulk orders',
      ),
    ];

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'What type of food provider are you?',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Select the category that best describes your business',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),
          ...types.map((opt) => _buildTypeCard(opt)),
        ],
      ),
    );
  }

  Widget _buildTypeCard(_ProviderTypeOption opt) {
    final selected = _providerType == opt.type;
    return GestureDetector(
      onTap: () => setState(() => _providerType = opt.type),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: selected ? _brandLight : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? _brandColor : Colors.grey.shade300,
            width: selected ? 2 : 1,
          ),
          boxShadow: selected
              ? [BoxShadow(color: _brandColor.withValues(alpha: 0.15), blurRadius: 8, offset: const Offset(0, 2))]
              : [],
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: selected ? _brandColor : Colors.grey.shade100,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                opt.icon,
                color: selected ? Colors.white : Colors.grey.shade600,
                size: 24,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    opt.title,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: selected ? _brandColor : AppTheme.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    opt.subtitle,
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ),
            if (selected)
              const Icon(Icons.check_circle, color: _brandColor, size: 22),
          ],
        ),
      ),
    );
  }

  // ── Step 2: Business Details ────────────────────────────────────

  Widget _buildStep2BusinessDetails() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKeys[0],
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Business Details',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Tell us about your food business',
              style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 24),
            _buildTextField(
              controller: _businessNameController,
              label: 'Business Name',
              hint: 'e.g. Green Leaf Kitchen',
              icon: Icons.storefront,
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _descriptionController,
              label: 'Description',
              hint: 'Describe your cuisine, specialties...',
              icon: Icons.description_outlined,
              maxLines: 3,
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _phoneController,
              label: 'Phone Number',
              hint: '10-digit mobile number',
              icon: Icons.phone,
              keyboardType: TextInputType.phone,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Required';
                if (v.trim().length < 10) return 'Enter valid phone';
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _emailController,
              label: 'Email Address',
              hint: 'business@example.com',
              icon: Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Required';
                if (!v.contains('@')) return 'Enter valid email';
                return null;
              },
            ),
          ],
        ),
      ),
    );
  }

  // ── Step 3: Location ────────────────────────────────────────────

  Widget _buildStep3Location() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKeys[1],
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Location',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Where is your business located?',
              style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 24),
            _buildTextField(
              controller: _addressController,
              label: 'Full Address',
              hint: 'Street address, area, landmark',
              icon: Icons.location_on_outlined,
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildTextField(
                    controller: _cityController,
                    label: 'City',
                    hint: 'e.g. Mumbai',
                    icon: Icons.location_city,
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildTextField(
                    controller: _stateController,
                    label: 'State',
                    hint: 'e.g. Maharashtra',
                    icon: Icons.map_outlined,
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _pincodeController,
              label: 'Pincode',
              hint: '6-digit pincode',
              icon: Icons.pin_drop_outlined,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(6)],
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Required';
                if (v.trim().length != 6) return 'Enter valid 6-digit pincode';
                return null;
              },
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline, size: 18, color: Colors.blue.shade700),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'GPS coordinates will be auto-detected from your device when you enable location services.',
                      style: TextStyle(fontSize: 12, color: Colors.blue.shade700),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Step 4: Documents ───────────────────────────────────────────

  Widget _buildStep4Documents() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Verification Documents',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Upload documents to verify your business',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),
          _buildTextField(
            controller: TextEditingController(text: _fssaiLicense ?? ''),
            label: 'FSSAI License Number',
            hint: 'e.g. 12345678901234',
            icon: Icons.badge_outlined,
            onChanged: (v) => _fssaiLicense = v,
          ),
          const SizedBox(height: 16),
          GestureDetector(
            onTap: () async {
              final picked = await showDatePicker(
                context: context,
                initialDate: _fssaiExpiry ?? DateTime.now().add(const Duration(days: 365)),
                firstDate: DateTime.now(),
                lastDate: DateTime.now().add(const Duration(days: 365 * 5)),
              );
              if (picked != null) setState(() => _fssaiExpiry = picked);
            },
            child: InputDecorator(
              decoration: InputDecoration(
                labelText: 'FSSAI Expiry Date',
                prefixIcon: const Icon(Icons.calendar_today, size: 20),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              ),
              child: Text(
                _fssaiExpiry != null
                    ? '${_fssaiExpiry!.day}/${_fssaiExpiry!.month}/${_fssaiExpiry!.year}'
                    : 'Select expiry date',
                style: TextStyle(
                  color: _fssaiExpiry != null ? AppTheme.textPrimary : Colors.grey,
                  fontSize: 15,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          _buildTextField(
            controller: TextEditingController(text: _gstNumber ?? ''),
            label: 'GST Number (optional)',
            hint: '22AAAAA0000A1Z5',
            icon: Icons.receipt_long_outlined,
            onChanged: (v) => _gstNumber = v,
          ),
          const SizedBox(height: 16),
          _buildTextField(
            controller: TextEditingController(text: _panNumber ?? ''),
            label: 'PAN Number (optional)',
            hint: 'ABCDE1234F',
            icon: Icons.credit_card_outlined,
            textCapitalization: TextCapitalization.characters,
            onChanged: (v) => _panNumber = v,
          ),
          const SizedBox(height: 24),
          // Document type selector
          DropdownButtonFormField<String>(
            initialValue: _documentType,
            decoration: InputDecoration(
              labelText: 'Document Type',
              prefixIcon: const Icon(Icons.folder_open, size: 20),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
            items: const [
              DropdownMenuItem(value: 'fssai', child: Text('FSSAI Certificate')),
              DropdownMenuItem(value: 'gst', child: Text('GST Certificate')),
              DropdownMenuItem(value: 'pan', child: Text('PAN Card')),
              DropdownMenuItem(value: 'aadhar', child: Text('Aadhaar Card')),
              DropdownMenuItem(value: 'trade_license', child: Text('Trade License')),
              DropdownMenuItem(value: 'shop_photo', child: Text('Shop/Business Photo')),
              DropdownMenuItem(value: 'other', child: Text('Other')),
            ],
            onChanged: (v) => setState(() => _documentType = v ?? 'fssai'),
          ),
          const SizedBox(height: 16),
          // Upload button
          GestureDetector(
            onTap: _pickDocument,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: _brandLight,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: _brandColor, width: 1, style: BorderStyle.solid),
              ),
              child: Column(
                children: [
                  Icon(Icons.cloud_upload_outlined, size: 36, color: _brandColor),
                  const SizedBox(height: 8),
                  Text(
                    'Tap to upload documents',
                    style: TextStyle(
                      color: _brandColor,
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                  ),
                  Text(
                    'JPG, PNG or PDF — Max 5MB each',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                  ),
                ],
              ),
            ),
          ),
          // Uploaded files list
          if (_documentFiles.isNotEmpty) ...[
            const SizedBox(height: 16),
            ...List.generate(_documentFiles.length, (i) {
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(Icons.insert_drive_file, color: _brandColor, size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _documentFiles[i].path.split('/').last,
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.close, size: 18, color: Colors.red.shade400),
                      onPressed: () => _removeDocument(i),
                    ),
                  ],
                ),
              );
            }),
          ],
        ],
      ),
    );
  }

  // ── Step 5: Bank Details ────────────────────────────────────────

  Widget _buildStep5BankDetails() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Bank Details',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Required for receiving payments (can be updated later)',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),
          _buildTextField(
            controller: _bankAccountController,
            label: 'Bank Account Number',
            hint: 'Enter account number',
            icon: Icons.account_balance_wallet_outlined,
            keyboardType: TextInputType.number,
          ),
          const SizedBox(height: 16),
          _buildTextField(
            controller: _bankIfscController,
            label: 'IFSC Code',
            hint: 'e.g. SBIN0001234',
            icon: Icons.code_outlined,
            textCapitalization: TextCapitalization.characters,
          ),
          const SizedBox(height: 16),
          _buildTextField(
            controller: _upiIdController,
            label: 'UPI ID (optional)',
            hint: 'yourname@upi',
            icon: Icons.qr_code,
          ),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.amber.shade50,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.lock_outline, size: 18, color: Colors.amber.shade800),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Your bank details are encrypted and stored securely. '
                    'They are only used for processing your payouts.',
                    style: TextStyle(fontSize: 12, color: Colors.amber.shade900),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Step 6: Review ──────────────────────────────────────────────

  Widget _buildStep6Review() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Review & Submit',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: AppTheme.textPrimary,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Verify your information before submitting',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),

          // Type
          _reviewSection('Provider Type', _formatType(_providerType)),
          // Business
          _reviewSection('Business Name', _businessNameController.text.trim()),
          _reviewSection('Description', _descriptionController.text.trim().isNotEmpty
              ? _descriptionController.text.trim()
              : '—'),
          _reviewSection('Phone', _phoneController.text.trim()),
          _reviewSection('Email', _emailController.text.trim()),
          // Location
          _reviewSection('Address', _addressController.text.trim()),
          _reviewSection('City', _cityController.text.trim()),
          _reviewSection('State', _stateController.text.trim()),
          _reviewSection('Pincode', _pincodeController.text.trim()),
          // Documents
          _reviewSection('FSSAI License', _fssaiLicense?.trim() ?? '—'),
          _reviewSection('FSSAI Expiry', _fssaiExpiry != null
              ? '${_fssaiExpiry!.day}/${_fssaiExpiry!.month}/${_fssaiExpiry!.year}'
              : '—'),
          _reviewSection('GST Number', _gstNumber?.trim() ?? '—'),
          _reviewSection('PAN Number', _panNumber?.trim() ?? '—'),
          _reviewSection('Documents Uploaded', '${_documentFiles.length} file(s)'),
          // Bank
          _reviewSection('Account Number', _bankAccountController.text.trim().isNotEmpty
              ? '••••${_bankAccountController.text.trim().substring(_bankAccountController.text.trim().length.clamp(0, 4))}'
              : '—'),
          _reviewSection('IFSC', _bankIfscController.text.trim().isNotEmpty
              ? _bankIfscController.text.trim()
              : '—'),

          const SizedBox(height: 20),
          // Terms
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Checkbox(
                value: _agreedToTerms,
                onChanged: (v) => setState(() => _agreedToTerms = v ?? false),
                activeColor: _brandColor,
              ),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _agreedToTerms = !_agreedToTerms),
                  child: Padding(
                    padding: const EdgeInsets.only(top: 12),
                    child: Text.rich(
                      TextSpan(
                        text: 'I agree to the ',
                        style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
                        children: [
                          TextSpan(
                            text: 'Terms & Conditions',
                            style: TextStyle(
                              color: _brandColor,
                              fontWeight: FontWeight.w600,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                          const TextSpan(text: ' and '),
                          TextSpan(
                            text: 'Food Safety Guidelines',
                            style: TextStyle(
                              color: _brandColor,
                              fontWeight: FontWeight.w600,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _reviewSection(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade500,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: AppTheme.textPrimary,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatType(String? type) {
    switch (type) {
      case 'restaurant': return 'Restaurant';
      case 'cloud_kitchen': return 'Cloud Kitchen';
      case 'home_chef': return 'Home Chef';
      case 'hotel': return 'Hotel';
      case 'catering': return 'Catering Service';
      default: return '—';
    }
  }

  // ── Bottom navigation ───────────────────────────────────────────

  Widget _buildBottomNav() {
    final isLastStep = _currentStep == _totalSteps - 1;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            if (_currentStep > 0)
              Expanded(
                child: OutlinedButton(
                  onPressed: _isSubmitting ? null : _prevStep,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _brandColor,
                    side: const BorderSide(color: _brandColor),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: const Text('Back'),
                ),
              ),
            if (_currentStep > 0) const SizedBox(width: 12),
            Expanded(
              flex: 2,
              child: ElevatedButton(
                onPressed: _isSubmitting
                    ? null
                    : isLastStep
                        ? _submitRegistration
                        : _nextStep,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _brandColor,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  elevation: 0,
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : Text(
                        isLastStep ? 'Submit Registration' : 'Continue',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Shared text field ───────────────────────────────────────────

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    String? hint,
    required IconData icon,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    int maxLines = 1,
    String? Function(String?)? validator,
    ValueChanged<String>? onChanged,
    TextCapitalization textCapitalization = TextCapitalization.none,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      validator: validator,
      onChanged: onChanged,
      textCapitalization: textCapitalization,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(icon, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _brandColor, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
    );
  }
}

// ── Data classes ──────────────────────────────────────────────────

class _ProviderTypeOption {
  final String type;
  final IconData icon;
  final String title;
  final String subtitle;

  const _ProviderTypeOption({
    required this.type,
    required this.icon,
    required this.title,
    required this.subtitle,
  });
}
