import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_colors.dart';
import '../config/app_config.dart';
import '../widgets/custom_button.dart';

class IntroductionScreen extends StatefulWidget {
  const IntroductionScreen({Key? key}) : super(key: key);

  @override
  State<IntroductionScreen> createState() => _IntroductionScreenState();
}

class _IntroductionScreenState extends State<IntroductionScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<OnboardingItem> _items = [
    OnboardingItem(
      icon: Icons.shopping_bag_outlined,
      title: 'تسوق بسهولة',
      description: 'تصفح آلاف المنتجات واختر ما يناسبك بكل سهولة',
    ),
    OnboardingItem(
      icon: Icons.local_shipping_outlined,
      title: 'توصيل سريع',
      description: 'نوصل طلبك لباب بيتك في أسرع وقت ممكن',
    ),
    OnboardingItem(
      icon: Icons.payment_outlined,
      title: 'دفع آمن',
      description: 'طرق دفع متعددة وآمنة لراحتك',
    ),
    OnboardingItem(
      icon: Icons.support_agent_outlined,
      title: 'دعم متواصل',
      description: 'فريق دعم متوفر على مدار الساعة لمساعدتك',
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _onPageChanged(int page) {
    setState(() {
      _currentPage = page;
    });
  }

  Future<void> _completeIntroduction() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(AppConfig.introSeenKey, true);
    
    if (!mounted) return;
    Navigator.of(context).pushReplacementNamed('/login');
  }

  void _nextPage() {
    if (_currentPage < _items.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    } else {
      _completeIntroduction();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            // Skip Button
            Align(
              alignment: Alignment.topLeft,
              child: TextButton(
                onPressed: _completeIntroduction,
                child: const Text('تخطي'),
              ),
            ),
            
            // Pages
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                onPageChanged: _onPageChanged,
                itemCount: _items.length,
                itemBuilder: (context, index) {
                  return _buildPage(_items[index]);
                },
              ),
            ),
            
            // Indicators
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 24),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  _items.length,
                  (index) => _buildIndicator(index == _currentPage),
                ),
              ),
            ),
            
            // Buttons
            Padding(
              padding: const EdgeInsets.all(24),
              child: CustomButton(
                text: _currentPage == _items.length - 1 ? 'ابدأ الآن' : 'التالي',
                onPressed: _nextPage,
                icon: _currentPage == _items.length - 1
                    ? Icons.check
                    : Icons.arrow_forward,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPage(OnboardingItem item) {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 150,
            height: 150,
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              item.icon,
              size: 80,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(height: 48),
          Text(
            item.title,
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          Text(
            item.description,
            style: TextStyle(
              fontSize: 16,
              color: AppColors.textSecondary,
              height: 1.5,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildIndicator(bool isActive) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(horizontal: 4),
      width: isActive ? 24 : 8,
      height: 8,
      decoration: BoxDecoration(
        color: isActive ? AppColors.primary : AppColors.border,
        borderRadius: BorderRadius.circular(4),
      ),
    );
  }
}

class OnboardingItem {
  final IconData icon;
  final String title;
  final String description;

  OnboardingItem({
    required this.icon,
    required this.title,
    required this.description,
  });
}
