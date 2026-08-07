import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class ProfileSetupScreen extends StatefulWidget {
  const ProfileSetupScreen({super.key});

  @override
  State<ProfileSetupScreen> createState() => _ProfileSetupScreenState();
}

class _ProfileSetupScreenState extends State<ProfileSetupScreen> {
  final _nameController = TextEditingController();
  final _bioController = TextEditingController();
  final _cityController = TextEditingController();
  final _nameFocusNode = FocusNode();
  final _bioFocusNode = FocusNode();
  final _cityFocusNode = FocusNode();
  bool _isLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _bioController.dispose();
    _cityController.dispose();
    _nameFocusNode.dispose();
    _bioFocusNode.dispose();
    _cityFocusNode.dispose();
    super.dispose();
  }

  bool _validateForm() {
    final name = _nameController.text.trim();
    final city = _cityController.text.trim();

    if (name.isEmpty) {
      Fluttertoast.showToast(msg: 'Please enter your name');
      _nameFocusNode.requestFocus();
      return false;
    }
    if (name.length < 2) {
      Fluttertoast.showToast(msg: 'Name must be at least 2 characters');
      _nameFocusNode.requestFocus();
      return false;
    }
    if (city.isEmpty) {
      Fluttertoast.showToast(msg: 'Please enter your city');
      _cityFocusNode.requestFocus();
      return false;
    }
    return true;
  }

  Future<void> _saveProfile() async {
    if (!_validateForm()) return;

    setState(() => _isLoading = true);

    final apiClient = ApiClient();

    try {
      final response = await apiClient.updateProfile(
        name: _nameController.text.trim(),
        bio: _bioController.text.trim().isEmpty
            ? null
            : _bioController.text.trim(),
        city: _cityController.text.trim(),
      );

      if (!mounted) return;

      if (response.success) {
        Fluttertoast.showToast(msg: 'Profile saved successfully');
        context.go('/home');
      } else {
        Fluttertoast.showToast(
          msg: response.message ?? 'Failed to save profile',
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
        title: const Text('Setup Profile'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Avatar
            Center(
              child: Stack(
                children: [
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryLight.withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.person,
                      size: 50,
                      color: AppTheme.primary,
                    ),
                  ),
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      decoration: const BoxDecoration(
                        color: AppTheme.primary,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.camera_alt,
                        size: 18,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppTheme.spacingXl),
            // Name field
            TextField(
              controller: _nameController,
              focusNode: _nameFocusNode,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Name *',
                hintText: 'Enter your full name',
                prefixIcon: Icon(Icons.person_outline),
              ),
              textInputAction: TextInputAction.next,
              onSubmitted: (_) => _bioFocusNode.requestFocus(),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            // Bio field
            TextField(
              controller: _bioController,
              focusNode: _bioFocusNode,
              maxLines: 3,
              textCapitalization: TextCapitalization.sentences,
              decoration: const InputDecoration(
                labelText: 'Bio (optional)',
                hintText: 'Tell us about yourself',
                prefixIcon: Icon(Icons.info_outline),
                alignLabelWithHint: true,
              ),
              textInputAction: TextInputAction.next,
              onSubmitted: (_) => _cityFocusNode.requestFocus(),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            // City field
            TextField(
              controller: _cityController,
              focusNode: _cityFocusNode,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'City *',
                hintText: 'Enter your city',
                prefixIcon: Icon(Icons.location_city_outlined),
              ),
              textInputAction: TextInputAction.done,
              onSubmitted: (_) => _saveProfile(),
            ),
            const SizedBox(height: AppTheme.spacingXl),
            // Save button
            ElevatedButton(
              onPressed: _isLoading ? null : _saveProfile,
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
                  : const Text('Save Profile'),
            ),
            const SizedBox(height: AppTheme.spacingMd),
            // Skip button
            TextButton(
              onPressed: () => context.go('/home'),
              child: const Text(
                'Skip for now',
                style: TextStyle(
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
