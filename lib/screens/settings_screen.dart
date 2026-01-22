import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/theme_provider.dart';
import '../config/app_config.dart';

/// شاشة الإعدادات
class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notificationsEnabled = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الإعدادات'),
      ),
      body: ListView(
        padding: EdgeInsets.all(16.w(context)),
        children: [
          // قسم المظهر
          _buildSectionHeader(context, 'المظهر'),
          Consumer<ThemeProvider>(
            builder: (context, themeProvider, child) {
              return Card(
                child: Column(
                  children: [
                    RadioListTile<ThemeMode>(
                      title: const Text('الوضع الفاتح'),
                      secondary: const Icon(Icons.light_mode),
                      value: ThemeMode.light,
                      groupValue: themeProvider.themeMode,
                      onChanged: (value) {
                        themeProvider.setThemeMode(ThemeMode.light);
                      },
                    ),
                    const Divider(height: 1),
                    RadioListTile<ThemeMode>(
                      title: const Text('الوضع الداكن'),
                      secondary: const Icon(Icons.dark_mode),
                      value: ThemeMode.dark,
                      groupValue: themeProvider.themeMode,
                      onChanged: (value) {
                        themeProvider.setThemeMode(ThemeMode.dark);
                      },
                    ),
                    const Divider(height: 1),
                    RadioListTile<ThemeMode>(
                      title: const Text('تلقائي (حسب النظام)'),
                      secondary: const Icon(Icons.brightness_auto),
                      value: ThemeMode.system,
                      groupValue: themeProvider.themeMode,
                      onChanged: (value) {
                        themeProvider.setThemeMode(ThemeMode.system);
                      },
                    ),
                  ],
                ),
              );
            },
          ),

          SizedBox(height: 24.h(context)),

          // قسم الإشعارات
          _buildSectionHeader(context, 'الإشعارات'),
          Card(
            child: SwitchListTile(
              title: const Text('تفعيل الإشعارات'),
              subtitle: const Text('استلام إشعارات عن العروض والطلبات'),
              secondary: const Icon(Icons.notifications_outlined),
              value: _notificationsEnabled,
              onChanged: (value) {
                setState(() => _notificationsEnabled = value);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      value ? 'تم تفعيل الإشعارات' : 'تم إيقاف الإشعارات',
                    ),
                  ),
                );
              },
            ),
          ),

          SizedBox(height: 24.h(context)),

          // قسم اللغة
          _buildSectionHeader(context, 'اللغة'),
          Card(
            child: ListTile(
              leading: const Icon(Icons.language),
              title: const Text('لغة التطبيق'),
              subtitle: const Text('العربية'),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16),
              onTap: () {
                _showLanguageDialog(context);
              },
            ),
          ),

          SizedBox(height: 24.h(context)),

          // قسم الخصوصية والأمان
          _buildSectionHeader(context, 'الخصوصية والأمان'),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.lock_outline),
                  title: const Text('تغيير كلمة المرور'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    _showChangePasswordDialog(context);
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.security),
                  title: const Text('سياسة الخصوصية'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // فتح صفحة سياسة الخصوصية
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.description_outlined),
                  title: const Text('الشروط والأحكام'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // فتح صفحة الشروط والأحكام
                  },
                ),
              ],
            ),
          ),

          SizedBox(height: 24.h(context)),

          // قسم حول التطبيق
          _buildSectionHeader(context, 'حول التطبيق'),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.info_outline),
                  title: const Text('إصدار التطبيق'),
                  subtitle: Text(AppConfig.appVersion),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.star_outline),
                  title: const Text('تقييم التطبيق'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // فتح صفحة التقييم في المتجر
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.share_outlined),
                  title: const Text('مشاركة التطبيق'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // مشاركة رابط التطبيق
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.support_agent),
                  title: const Text('تواصل معنا'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    _showContactDialog(context);
                  },
                ),
              ],
            ),
          ),

          SizedBox(height: 24.h(context)),

          // قسم الحساب
          _buildSectionHeader(context, 'الحساب'),
          Card(
            child: ListTile(
              leading: const Icon(Icons.delete_forever, color: Colors.red),
              title: const Text(
                'حذف الحساب',
                style: TextStyle(color: Colors.red),
              ),
              onTap: () {
                _showDeleteAccountDialog(context);
              },
            ),
          ),

          SizedBox(height: 32.h(context)),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(BuildContext context, String title) {
    return Padding(
      padding: EdgeInsets.only(bottom: 8.h(context)),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleMedium?.copyWith(
          fontWeight: FontWeight.bold,
          color: Theme.of(context).colorScheme.primary,
        ),
      ),
    );
  }

  void _showLanguageDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('اختر اللغة'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              title: const Text('العربية'),
              leading: const Text('🇸🇦'),
              selected: true,
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              title: const Text('English'),
              leading: const Text('🇺🇸'),
              onTap: () {
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('سيتم دعم اللغة الإنجليزية قريباً')),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showChangePasswordDialog(BuildContext context) {
    final currentPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تغيير كلمة المرور'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: currentPasswordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'كلمة المرور الحالية',
              ),
            ),
            SizedBox(height: 8.h(context)),
            TextField(
              controller: newPasswordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'كلمة المرور الجديدة',
              ),
            ),
            SizedBox(height: 8.h(context)),
            TextField(
              controller: confirmPasswordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'تأكيد كلمة المرور',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم تغيير كلمة المرور بنجاح')),
              );
            },
            child: const Text('تغيير'),
          ),
        ],
      ),
    );
  }

  void _showContactDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تواصل معنا'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.email),
              title: const Text('البريد الإلكتروني'),
              subtitle: const Text('support@mystore.com'),
            ),
            ListTile(
              leading: const Icon(Icons.phone),
              title: const Text('الهاتف'),
              subtitle: const Text('+966 12 345 6789'),
            ),
            ListTile(
              leading: const Icon(Icons.location_on),
              title: const Text('العنوان'),
              subtitle: const Text('الرياض، المملكة العربية السعودية'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  void _showDeleteAccountDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('حذف الحساب'),
        content: const Text(
          'هل أنت متأكد من حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع بياناتك.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () {
              Navigator.pop(context);
              // تنفيذ حذف الحساب
            },
            child: const Text('حذف الحساب'),
          ),
        ],
      ),
    );
  }
}
