import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../../theme/app_theme.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notificationsEnabled = true;
  String _selectedLanguage = 'English';

  final _languages = ['English', 'Hindi', 'Marathi', 'Tamil', 'Telugu'];

  void _showLanguagePicker() {
    showModalBottomSheet(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                color: AppTheme.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const Padding(
              padding: EdgeInsets.all(AppTheme.spacingMd),
              child: Text(
                'Select Language',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              ),
            ),
            ..._languages.map((lang) => RadioListTile<String>(
                  title: Text(lang),
                  value: lang,
                  groupValue: _selectedLanguage,
                  onChanged: (value) {
                    setState(() => _selectedLanguage = value!);
                    Navigator.pop(context);
                    Fluttertoast.showToast(
                      msg: 'Language changed to $value',
                    );
                  },
                  activeColor: AppTheme.primary,
                )),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        children: [
          // Preferences
          const Text(
            'Preferences',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: AppTheme.spacingSm),
          Card(
            child: Column(
              children: [
                SwitchListTile(
                  secondary: const Icon(Icons.notifications,
                      color: AppTheme.textSecondary),
                  title: const Text('Push Notifications'),
                  subtitle: const Text('Receive booking and trip updates'),
                  value: _notificationsEnabled,
                  onChanged: (value) {
                    setState(() => _notificationsEnabled = value);
                  },
                  activeColor: AppTheme.primary,
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.language,
                      color: AppTheme.textSecondary),
                  title: const Text('Language'),
                  subtitle: Text(_selectedLanguage),
                  trailing: const Icon(Icons.chevron_right,
                      color: AppTheme.textLight),
                  onTap: _showLanguagePicker,
                ),
              ],
            ),
          ),
          const SizedBox(height: AppTheme.spacingLg),

          // About
          const Text(
            'About',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: AppTheme.spacingSm),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.lock_outline,
                      color: AppTheme.textSecondary),
                  title: const Text('Privacy Policy'),
                  trailing: const Icon(Icons.chevron_right,
                      color: AppTheme.textLight),
                  onTap: () {
                    // TODO: Open privacy policy
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.description_outlined,
                      color: AppTheme.textSecondary),
                  title: const Text('Terms of Service'),
                  trailing: const Icon(Icons.chevron_right,
                      color: AppTheme.textLight),
                  onTap: () {
                    // TODO: Open terms of service
                  },
                ),
                const Divider(height: 1),
                const ListTile(
                  leading: Icon(Icons.info_outline,
                      color: AppTheme.textSecondary),
                  title: Text('App Version'),
                  subtitle: Text('1.0.0'),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppTheme.spacingLg),

          // Support
          const Text(
            'Support',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: AppTheme.textSecondary,
            ),
          ),
          const SizedBox(height: AppTheme.spacingSm),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.help_outline,
                      color: AppTheme.textSecondary),
                  title: const Text('Help Center'),
                  trailing: const Icon(Icons.chevron_right,
                      color: AppTheme.textLight),
                  onTap: () {
                    // TODO: Open help center
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.email_outlined,
                      color: AppTheme.textSecondary),
                  title: const Text('Contact Us'),
                  trailing: const Icon(Icons.chevron_right,
                      color: AppTheme.textLight),
                  onTap: () {
                    // TODO: Open contact form
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: AppTheme.spacingLg),
        ],
      ),
    );
  }
}
