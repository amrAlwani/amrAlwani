import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/auth_provider.dart';
import '../models/order.dart';
import '../services/api_service.dart';
import '../utils/formatters.dart';

/// شاشة الطلبات
class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  List<Order> _orders = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await ApiService().get('orders.php?action=list');
      
      if (response['success'] == true && response['data'] != null) {
        final List<dynamic> ordersData = response['data'];
        _orders = ordersData.map((json) => Order.fromJson(json)).toList();
      } else {
        _error = response['message'] ?? 'فشل في تحميل الطلبات';
      }
    } catch (e) {
      _error = 'حدث خطأ في الاتصال';
      debugPrint('Error loading orders: $e');
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('طلباتي'),
      ),
      body: Consumer<AuthProvider>(
        builder: (context, auth, child) {
          if (!auth.isAuthenticated) {
            return _buildNotLoggedIn(context);
          }

          if (_isLoading) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }

          if (_error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64.sp(context),
                    color: Theme.of(context).colorScheme.error,
                  ),
                  SizedBox(height: 16.h(context)),
                  Text(_error!),
                  SizedBox(height: 16.h(context)),
                  ElevatedButton(
                    onPressed: _loadOrders,
                    child: const Text('إعادة المحاولة'),
                  ),
                ],
              ),
            );
          }

          if (_orders.isEmpty) {
            return _buildEmptyOrders(context);
          }

          return RefreshIndicator(
            onRefresh: _loadOrders,
            child: ListView.builder(
              padding: EdgeInsets.all(16.w(context)),
              itemCount: _orders.length,
              itemBuilder: (context, index) {
                return _buildOrderCard(context, _orders[index]);
              },
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
            Icons.shopping_bag_outlined,
            size: 80.sp(context),
            color: Theme.of(context).colorScheme.outline,
          ),
          SizedBox(height: 16.h(context)),
          Text(
            'سجل دخولك لعرض طلباتك',
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

  Widget _buildEmptyOrders(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.receipt_long_outlined,
            size: 80.sp(context),
            color: Theme.of(context).colorScheme.outline,
          ),
          SizedBox(height: 16.h(context)),
          Text(
            'لا توجد طلبات',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              color: Theme.of(context).colorScheme.outline,
            ),
          ),
          SizedBox(height: 8.h(context)),
          Text(
            'ابدأ التسوق الآن',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Theme.of(context).colorScheme.outline,
            ),
          ),
          SizedBox(height: 24.h(context)),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.of(context).pushNamed('/products');
            },
            icon: const Icon(Icons.shopping_bag_outlined),
            label: const Text('تصفح المنتجات'),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderCard(BuildContext context, Order order) {
    return Card(
      margin: EdgeInsets.only(bottom: 16.h(context)),
      child: InkWell(
        onTap: () => _showOrderDetails(context, order),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: EdgeInsets.all(16.w(context)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // رقم الطلب والتاريخ
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'طلب #${order.id}',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  _buildStatusChip(context, order.status),
                ],
              ),
              SizedBox(height: 8.h(context)),
              
              // تاريخ الطلب
              Row(
                children: [
                  Icon(
                    Icons.calendar_today_outlined,
                    size: 16.sp(context),
                    color: Theme.of(context).colorScheme.outline,
                  ),
                  SizedBox(width: 4.w(context)),
                  Text(
                    Formatters.formatDate(order.createdAt),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Theme.of(context).colorScheme.outline,
                    ),
                  ),
                ],
              ),
              
              SizedBox(height: 12.h(context)),
              const Divider(),
              SizedBox(height: 12.h(context)),
              
              // عدد المنتجات والإجمالي
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.inventory_2_outlined,
                        size: 20.sp(context),
                        color: Theme.of(context).colorScheme.primary,
                      ),
                      SizedBox(width: 8.w(context)),
                      Text(
                        '${order.items.length} منتج',
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                  Text(
                    Formatters.formatPrice(order.total),
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ),
                ],
              ),
              
              SizedBox(height: 12.h(context)),
              
              // حالة الدفع
              Row(
                children: [
                  Icon(
                    order.paymentStatus == 'paid'
                        ? Icons.check_circle
                        : Icons.pending,
                    size: 16.sp(context),
                    color: order.paymentStatus == 'paid'
                        ? Colors.green
                        : Colors.orange,
                  ),
                  SizedBox(width: 4.w(context)),
                  Text(
                    order.paymentStatusText,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: order.paymentStatus == 'paid'
                          ? Colors.green
                          : Colors.orange,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(BuildContext context, String status) {
    Color color;
    IconData icon;
    
    switch (status) {
      case 'pending':
        color = Colors.orange;
        icon = Icons.pending;
        break;
      case 'processing':
        color = Colors.blue;
        icon = Icons.sync;
        break;
      case 'shipped':
        color = Colors.purple;
        icon = Icons.local_shipping;
        break;
      case 'delivered':
        color = Colors.green;
        icon = Icons.check_circle;
        break;
      case 'cancelled':
        color = Colors.red;
        icon = Icons.cancel;
        break;
      default:
        color = Colors.grey;
        icon = Icons.help_outline;
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: 12.w(context),
        vertical: 4.h(context),
      ),
      decoration: BoxDecoration(
        color: color.withAlpha(30),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14.sp(context), color: color),
          SizedBox(width: 4.w(context)),
          Text(
            _getStatusText(status),
            style: TextStyle(
              color: color,
              fontSize: 12.sp(context),
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return 'قيد الانتظار';
      case 'processing':
        return 'قيد المعالجة';
      case 'shipped':
        return 'تم الشحن';
      case 'delivered':
        return 'تم التوصيل';
      case 'cancelled':
        return 'ملغي';
      default:
        return status;
    }
  }

  void _showOrderDetails(BuildContext context, Order order) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (context, scrollController) {
          return SingleChildScrollView(
            controller: scrollController,
            padding: EdgeInsets.all(24.w(context)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // المقبض
                Center(
                  child: Container(
                    width: 40.w(context),
                    height: 4.h(context),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.outline,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                SizedBox(height: 24.h(context)),
                
                // رقم الطلب
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'تفاصيل الطلب #${order.id}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    _buildStatusChip(context, order.status),
                  ],
                ),
                SizedBox(height: 8.h(context)),
                Text(
                  Formatters.formatDate(order.createdAt),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
                
                SizedBox(height: 24.h(context)),
                const Divider(),
                SizedBox(height: 16.h(context)),
                
                // المنتجات
                Text(
                  'المنتجات',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 12.h(context)),
                
                ...order.items.map((item) => Padding(
                  padding: EdgeInsets.only(bottom: 12.h(context)),
                  child: Row(
                    children: [
                      Container(
                        width: 60.w(context),
                        height: 60.w(context),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: item.product?.image != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  item.product!.image!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Icon(
                                    Icons.image_outlined,
                                    color: Theme.of(context).colorScheme.outline,
                                  ),
                                ),
                              )
                            : Icon(
                                Icons.image_outlined,
                                color: Theme.of(context).colorScheme.outline,
                              ),
                      ),
                      SizedBox(width: 12.w(context)),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item.product?.name ?? 'منتج غير متوفر',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            Text(
                              '${item.quantity} × ${Formatters.formatPrice(item.price)}',
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                color: Theme.of(context).colorScheme.outline,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        Formatters.formatPrice(item.total),
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                )),
                
                SizedBox(height: 16.h(context)),
                const Divider(),
                SizedBox(height: 16.h(context)),
                
                // الملخص
                _buildSummaryRow(context, 'المجموع الفرعي', Formatters.formatPrice(order.subtotal)),
                _buildSummaryRow(context, 'الشحن', Formatters.formatPrice(order.shipping)),
                _buildSummaryRow(context, 'الضريبة', Formatters.formatPrice(order.tax)),
                SizedBox(height: 8.h(context)),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'الإجمالي',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      Formatters.formatPrice(order.total),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ],
                ),
                
                // عنوان الشحن
                if (order.shippingAddress != null) ...[
                  SizedBox(height: 24.h(context)),
                  const Divider(),
                  SizedBox(height: 16.h(context)),
                  Text(
                    'عنوان الشحن',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 8.h(context)),
                  Text(order.shippingAddress!.name),
                  Text(order.shippingAddress!.phone),
                  Text(order.shippingAddress!.address),
                  Text('${order.shippingAddress!.city}, ${order.shippingAddress!.country}'),
                ],
                
                SizedBox(height: 32.h(context)),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSummaryRow(BuildContext context, String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 4.h(context)),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Theme.of(context).colorScheme.outline,
            ),
          ),
          Text(value),
        ],
      ),
    );
  }
}
