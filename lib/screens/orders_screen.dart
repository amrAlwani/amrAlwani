import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/auth_provider.dart';
import '../utils/formatters.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('طلباتي'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'الكل'),
            Tab(text: 'نشطة'),
            Tab(text: 'مكتملة'),
            Tab(text: 'ملغية'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: List.generate(4, (tabIndex) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.receipt_long_outlined,
                  size: 64.sp(context),
                  color: Colors.grey,
                ),
                SizedBox(height: 16.h(context)),
                Text(
                  'لا توجد طلبات',
                  style: TextStyle(
                    fontSize: 16.sp(context),
                    color: Colors.grey,
                  ),
                ),
                SizedBox(height: 24.h(context)),
                ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pushNamed('/products');
                  },
                  child: const Text('تسوق الآن'),
                ),
              ],
            ),
          );
        }),
      ),
    );
  }
}
