import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/theme_provider.dart';
import '../providers/auth_provider.dart';
import '../config/app_config.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final themeProvider = Provider.of<ThemeProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('الإعدادات'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Appearance Section
          _buildSectionHeader('المظهر'),
          _buildCard(
            context,
            children: [
              // Theme Mode
              ListTile(
                leading: Icon(
                  themeProvider.isDarkMode
                      ? Icons.dark_mode
                      : Icons.light_mode,
                ),
                title: const Text('المظهر'),
                subtitle: Text(
                  themeProvider.themeMode == ThemeMode.system
                      ? 'تلقائي (حسب النظام)'
                      : themeProvider.isDarkMode
                          ? 'داكن'
                          : 'فاتح',
                ),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () => _showThemeDialog(context, themeProvider),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Notifications Section
          _buildSectionHeader('الإشعارات'),
          _buildCard(
            context,
            children: [
              SwitchListTile(
                secondary: const Icon(Icons.notifications_outlined),
                title: const Text('إشعارات الطلبات'),
                subtitle: const Text('تنبيهات عند تحديث حالة الطلب'),
                value: true,
                onChanged: (value) {
                  // Toggle order notifications
                },
              ),
              const Divider(height: 1),
              SwitchListTile(
                secondary: const Icon(Icons.local_offer_outlined),
                title: const Text('العروض والخصومات'),
                subtitle: const Text('إشعارات العروض الجديدة'),
                value: true,
                onChanged: (value) {
                  // Toggle promo notifications
                },
              ),
              const Divider(height: 1),
              SwitchListTile(
                secondary: const Icon(Icons.email_outlined),
                title: const Text('رسائل البريد الإلكتروني'),
                subtitle: const Text('تلقي التحديثات عبر البريد'),
                value: false,
                onChanged: (value) {
                  // Toggle email notifications
                },
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Language Section
          _buildSectionHeader('اللغة والمنطقة'),
          _buildCard(
            context,
            children: [
              ListTile(
                leading: const Icon(Icons.language),
                title: const Text('اللغة'),
                subtitle: const Text('العربية'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () => _showLanguageDialog(context),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.attach_money),
                title: const Text('العملة'),
                subtitle: Text('${AppConfig.currencyCode} (${AppConfig.currency})'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () {
                  // Show currency picker
                },
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Account Section
          if (authProvider.isAuthenticated) ...[
            _buildSectionHeader('الحساب'),
            _buildCard(
              context,
              children: [
                ListTile(
                  leading: const Icon(Icons.lock_outline),
                  title: const Text('تغيير كلمة المرور'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // Navigate to change password
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.delete_outline, color: Colors.red),
                  title: const Text(
                    'حذف الحساب',
                    style: TextStyle(color: Colors.red),
                  ),
                  onTap: () => _showDeleteAccountDialog(context, authProvider),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],

          // About Section
          _buildSectionHeader('حول التطبيق'),
          _buildCard(
            context,
            children: [
              ListTile(
                leading: const Icon(Icons.info_outline),
                title: const Text('إصدار التطبيق'),
                subtitle: Text(AppConfig.appVersion),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.description_outlined),
                title: const Text('الشروط والأحكام'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () {
                  // Show terms
                },
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.privacy_tip_outlined),
                title: const Text('سياسة الخصوصية'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () {
                  // Show privacy policy
                },
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.star_outline),
                title: const Text('تقييم التطبيق'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () {
                  // Open app store
                },
              ),
            ],
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: Colors.grey[600],
        ),
      ),
    );
  }

  Widget _buildCard(BuildContext context, {required List<Widget> children}) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(children: children),
    );
  }

  void _showThemeDialog(BuildContext context, ThemeProvider themeProvider) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('المظهر'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            RadioListTile<ThemeMode>(
              title: const Text('تلقائي (حسب النظام)'),
              value: ThemeMode.system,
              groupValue: themeProvider.themeMode,
              onChanged: (value) {
                themeProvider.setThemeMode(value!);
                Navigator.of(ctx).pop();
              },
            ),
            RadioListTile<ThemeMode>(
              title: const Text('فاتح'),
              value: ThemeMode.light,
              groupValue: themeProvider.themeMode,
              onChanged: (value) {
                themeProvider.setThemeMode(value!);
                Navigator.of(ctx).pop();
              },
            ),
            RadioListTile<ThemeMode>(
              title: const Text('داكن'),
              value: ThemeMode.dark,
              groupValue: themeProvider.themeMode,
              onChanged: (value) {
                themeProvider.setThemeMode(value!);
                Navigator.of(ctx).pop();
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showLanguageDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('اللغة'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            RadioListTile<String>(
              title: const Text('العربية'),
              value: 'ar',
              groupValue: 'ar',
              onChanged: (value) {
                Navigator.of(ctx).pop();
              },
            ),
            RadioListTile<String>(
              title: const Text('English'),
              value: 'en',
              groupValue: 'ar',
              onChanged: (value) {
                Navigator.of(ctx).pop();
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showDeleteAccountDialog(
    BuildContext context,
    AuthProvider authProvider,
  ) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('حذف الحساب'),
        content: const Text(
          'هل أنت متأكد من حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه وستفقد جميع بياناتك.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () async {
              Navigator.of(ctx).pop();
              await authProvider.logout();
              if (context.mounted) {
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
            child: const Text(
              'حذف',
              style: TextStyle(color: Colors.red),
            ),
          ),
        ],
      ),
    );
  }
}
