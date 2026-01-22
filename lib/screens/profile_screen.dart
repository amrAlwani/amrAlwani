import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/auth_provider.dart';
import '../utils/validators.dart';

/// شاشة الملف الشخصي
class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  bool _isEditing = false;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  void _loadUserData() {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (auth.user != null) {
      _nameController.text = auth.user!.name;
      _phoneController.text = auth.user!.phone ?? '';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الملف الشخصي'),
        actions: [
          if (!_isEditing)
            IconButton(
              icon: const Icon(Icons.edit),
              onPressed: () => setState(() => _isEditing = true),
            ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, auth, child) {
          if (!auth.isAuthenticated) {
            return _buildNotLoggedIn(context);
          }

          return SingleChildScrollView(
            padding: EdgeInsets.all(16.w(context)),
            child: Column(
              children: [
                // صورة المستخدم
                CircleAvatar(
                  radius: 50.w(context),
                  backgroundColor: Theme.of(context).colorScheme.primary,
                  backgroundImage: auth.user?.avatar != null
                      ? NetworkImage(auth.user!.avatar!)
                      : null,
                  child: auth.user?.avatar == null
                      ? Icon(
                          Icons.person,
                          size: 50.sp(context),
                          color: Colors.white,
                        )
                      : null,
                ),
                SizedBox(height: 16.h(context)),
                
                // اسم المستخدم
                Text(
                  auth.user?.name ?? 'مستخدم',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  auth.user?.email ?? '',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
                
                SizedBox(height: 32.h(context)),

                // نموذج التعديل
                if (_isEditing)
                  _buildEditForm(context, auth)
                else
                  _buildProfileInfo(context, auth),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildNotLoggedIn(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.person_outline,
            size: 80.sp(context),
            color: Theme.of(context).colorScheme.outline,
          ),
          SizedBox(height: 16.h(context)),
          Text(
            'لم تقم بتسجيل الدخول',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          SizedBox(height: 24.h(context)),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pushNamed('/login');
            },
            child: const Text('تسجيل الدخول'),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileInfo(BuildContext context, AuthProvider auth) {
    return Column(
      children: [
        _buildInfoTile(
          context,
          icon: Icons.person_outline,
          title: 'الاسم',
          value: auth.user?.name ?? '-',
        ),
        _buildInfoTile(
          context,
          icon: Icons.email_outlined,
          title: 'البريد الإلكتروني',
          value: auth.user?.email ?? '-',
        ),
        _buildInfoTile(
          context,
          icon: Icons.phone_outlined,
          title: 'رقم الهاتف',
          value: auth.user?.phone ?? 'غير محدد',
        ),
        _buildInfoTile(
          context,
          icon: Icons.calendar_today_outlined,
          title: 'تاريخ التسجيل',
          value: _formatDate(auth.user?.createdAt),
        ),
        
        SizedBox(height: 32.h(context)),
        
        // أزرار الإجراءات
        ListTile(
          leading: const Icon(Icons.shopping_bag_outlined),
          title: const Text('طلباتي'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () => Navigator.of(context).pushNamed('/orders'),
        ),
        ListTile(
          leading: const Icon(Icons.favorite_outline),
          title: const Text('المفضلة'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () => Navigator.of(context).pushNamed('/favorites'),
        ),
        ListTile(
          leading: const Icon(Icons.settings_outlined),
          title: const Text('الإعدادات'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () => Navigator.of(context).pushNamed('/settings'),
        ),
        
        SizedBox(height: 24.h(context)),
        
        // زر تسجيل الخروج
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _showLogoutConfirmation(context, auth),
            icon: const Icon(Icons.logout, color: Colors.red),
            label: const Text('تسجيل الخروج', style: TextStyle(color: Colors.red)),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Colors.red),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildEditForm(BuildContext context, AuthProvider auth) {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          TextFormField(
            controller: _nameController,
            textInputAction: TextInputAction.next,
            decoration: const InputDecoration(
              labelText: 'الاسم',
              prefixIcon: Icon(Icons.person_outline),
            ),
            validator: Validators.validateName,
          ),
          SizedBox(height: 16.h(context)),
          TextFormField(
            controller: _phoneController,
            decoration: const InputDecoration(
              labelText: 'رقم الهاتف',
              prefixIcon: Icon(Icons.phone_outlined),
            ),
            keyboardType: TextInputType.phone,
            validator: Validators.validatePhoneOptional,
          ),
          SizedBox(height: 24.h(context)),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () {
                    setState(() => _isEditing = false);
                    _loadUserData();
                  },
                  child: const Text('إلغاء'),
                ),
              ),
              SizedBox(width: 16.w(context)),
              Expanded(
                child: FilledButton(
                  onPressed: _isLoading ? null : () => _saveProfile(auth),
                  child: _isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('حفظ'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoTile(BuildContext context, {
    required IconData icon,
    required String title,
    required String value,
  }) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 8.h(context)),
      child: Row(
        children: [
          Icon(icon, color: Theme.of(context).colorScheme.primary),
          SizedBox(width: 16.w(context)),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
              ),
              Text(
                value,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime? date) {
    if (date == null) return '-';
    return '${date.day}/${date.month}/${date.year}';
  }

  Future<void> _saveProfile(AuthProvider auth) async {
    if (!_formKey.currentState!.validate()) return;

    // التحقق الإضافي من أنواع البيانات
    final nameResult = Validators.validateType(_nameController.text, 'name');
    if (!nameResult.isValid) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(nameResult.error ?? 'خطأ في الاسم')),
      );
      return;
    }

    if (_phoneController.text.isNotEmpty) {
      final phoneResult = Validators.validateType(_phoneController.text, 'phone');
      if (!phoneResult.isValid) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(phoneResult.error ?? 'خطأ في رقم الهاتف')),
        );
        return;
      }
    }

    setState(() => _isLoading = true);

    try {
      // تنظيف البيانات قبل الإرسال
      await auth.updateProfile(
        name: Validators.sanitize(_nameController.text.trim()),
        phone: _phoneController.text.trim(),
      );
      
      if (mounted) {
        setState(() {
          _isEditing = false;
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم تحديث الملف الشخصي')),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('خطأ: $e')),
        );
      }
    }
  }

  void _showLogoutConfirmation(BuildContext context, AuthProvider auth) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تسجيل الخروج'),
        content: const Text('هل أنت متأكد من تسجيل الخروج؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () async {
              Navigator.pop(context);
              await auth.logout();
              if (context.mounted) {
                Navigator.of(context).pushNamedAndRemoveUntil(
                  '/login',
                  (route) => false,
                );
              }
            },
            child: const Text('تسجيل الخروج'),
          ),
        ],
      ),
    );
  }
}
