import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final themeProvider = Provider.of<ThemeProvider>(context);
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('حسابي'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            onPressed: () {
              Navigator.of(context).pushNamed('/settings');
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Profile Header
            if (authProvider.isAuthenticated && user != null) ...[
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Avatar
                    CircleAvatar(
                      radius: 50,
                      backgroundColor: Theme.of(context).primaryColor.withOpacity(0.1),
                      child: Text(
                        user.name.isNotEmpty ? user.name[0].toUpperCase() : 'U',
                        style: TextStyle(
                          fontSize: 36,
                          fontWeight: FontWeight.bold,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    
                    // Name
                    Text(
                      user.name,
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    
                    // Email
                    Text(
                      user.email,
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 16),
                    
                    // Edit Profile Button
                    OutlinedButton.icon(
                      onPressed: () {
                        // Navigate to edit profile
                      },
                      icon: const Icon(Icons.edit_outlined),
                      label: const Text('تعديل الملف الشخصي'),
                    ),
                  ],
                ),
              ),
            ] else ...[
              // Guest User
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    Icon(
                      Icons.person_outline,
                      size: 64,
                      color: Colors.grey[400],
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'مرحباً بك',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'سجل دخولك للوصول لجميع الميزات',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.of(context).pushNamed('/login');
                        },
                        icon: const Icon(Icons.login),
                        label: const Text('تسجيل الدخول'),
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.of(context).pushNamed('/register');
                        },
                        icon: const Icon(Icons.person_add),
                        label: const Text('إنشاء حساب'),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 24),

            // Menu Items
            _buildSection(
              context,
              title: 'طلباتي',
              items: [
                _MenuItem(
                  icon: Icons.receipt_long_outlined,
                  title: 'طلباتي',
                  onTap: () => Navigator.of(context).pushNamed('/orders'),
                ),
                _MenuItem(
                  icon: Icons.favorite_outline,
                  title: 'المفضلة',
                  onTap: () => Navigator.of(context).pushNamed('/favorites'),
                ),
                _MenuItem(
                  icon: Icons.location_on_outlined,
                  title: 'العناوين',
                  onTap: () => Navigator.of(context).pushNamed('/addresses'),
                ),
              ],
            ),
            const SizedBox(height: 16),

            _buildSection(
              context,
              title: 'الإعدادات',
              items: [
                _MenuItem(
                  icon: Icons.notifications_outlined,
                  title: 'الإشعارات',
                  onTap: () => Navigator.of(context).pushNamed('/notifications'),
                ),
                _MenuItem(
                  icon: themeProvider.isDarkMode
                      ? Icons.light_mode_outlined
                      : Icons.dark_mode_outlined,
                  title: 'الوضع الليلي',
                  trailing: Switch(
                    value: themeProvider.isDarkMode,
                    onChanged: (_) => themeProvider.toggleTheme(),
                  ),
                ),
                _MenuItem(
                  icon: Icons.language_outlined,
                  title: 'اللغة',
                  subtitle: 'العربية',
                  onTap: () {
                    // Change language
                  },
                ),
              ],
            ),
            const SizedBox(height: 16),

            _buildSection(
              context,
              title: 'المساعدة',
              items: [
                _MenuItem(
                  icon: Icons.help_outline,
                  title: 'المساعدة والدعم',
                  onTap: () {},
                ),
                _MenuItem(
                  icon: Icons.info_outline,
                  title: 'عن التطبيق',
                  onTap: () {},
                ),
                _MenuItem(
                  icon: Icons.privacy_tip_outlined,
                  title: 'سياسة الخصوصية',
                  onTap: () {},
                ),
              ],
            ),

            // Logout Button
            if (authProvider.isAuthenticated) ...[
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('تسجيل الخروج'),
                        content: const Text('هل تريد تسجيل الخروج من حسابك؟'),
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
                              'تسجيل الخروج',
                              style: TextStyle(color: Colors.red),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                  icon: const Icon(Icons.logout),
                  label: const Text('تسجيل الخروج'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    foregroundColor: Colors.white,
                  ),
                ),
              ),
            ],
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildSection(
    BuildContext context, {
    required String title,
    required List<_MenuItem> items,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              title,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          ...items.map((item) => _buildMenuItem(context, item)),
        ],
      ),
    );
  }

  Widget _buildMenuItem(BuildContext context, _MenuItem item) {
    return ListTile(
      leading: Icon(item.icon, color: Colors.grey[600]),
      title: Text(item.title),
      subtitle: item.subtitle != null ? Text(item.subtitle!) : null,
      trailing: item.trailing ??
          (item.onTap != null
              ? const Icon(Icons.arrow_forward_ios, size: 16)
              : null),
      onTap: item.onTap,
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String title;
  final String? subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  _MenuItem({
    required this.icon,
    required this.title,
    this.subtitle,
    this.trailing,
    this.onTap,
  });
}
