import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../providers/auth_provider.dart';
import '../utils/formatters.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({Key? key}) : super(key: key);

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final TextEditingController _couponController = TextEditingController();
  bool _isApplyingCoupon = false;

  @override
  void initState() {
    super.initState();
    _loadCart();
  }

  @override
  void dispose() {
    _couponController.dispose();
    super.dispose();
  }

  Future<void> _loadCart() async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    await cartProvider.loadCart();
  }

  Future<void> _applyCoupon() async {
    if (_couponController.text.isEmpty) return;

    setState(() => _isApplyingCoupon = true);

    try {
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      final success = await cartProvider.applyCoupon(_couponController.text);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(success ? 'تم تطبيق كود الخصم بنجاح' : cartProvider.error ?? 'كود الخصم غير صالح'),
          backgroundColor: success ? Colors.green : Colors.red,
        ),
      );
    } finally {
      if (mounted) setState(() => _isApplyingCoupon = false);
    }
  }

  void _proceedToCheckout() {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    if (!authProvider.isAuthenticated) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('تسجيل الدخول مطلوب'),
          content: const Text('يجب تسجيل الدخول لإكمال عملية الشراء'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(),
              child: const Text('إلغاء'),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(ctx).pop();
                Navigator.of(context).pushNamed('/login');
              },
              child: const Text('تسجيل الدخول'),
            ),
          ],
        ),
      );
      return;
    }

    Navigator.of(context).pushNamed('/checkout');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('سلة التسوق'),
        actions: [
          Consumer<CartProvider>(
            builder: (context, cartProvider, _) {
              if (cartProvider.items.isEmpty) return const SizedBox.shrink();
              
              return IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('تفريغ السلة'),
                      content: const Text('هل تريد حذف جميع المنتجات من السلة؟'),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.of(ctx).pop(),
                          child: const Text('إلغاء'),
                        ),
                        TextButton(
                          onPressed: () {
                            cartProvider.clearCart();
                            Navigator.of(ctx).pop();
                          },
                          child: const Text(
                            'حذف الكل',
                            style: TextStyle(color: Colors.red),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              );
            },
          ),
        ],
      ),
      body: Consumer<CartProvider>(
        builder: (context, cartProvider, _) {
          if (cartProvider.isLoading) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('جاري تحميل السلة...'),
                ],
              ),
            );
          }

          if (cartProvider.items.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.shopping_cart_outlined,
                    size: 80,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'السلة فارغة',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'أضف منتجات لبدء التسوق',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: () => Navigator.of(context).pushNamed('/products'),
                    icon: const Icon(Icons.shopping_bag_outlined),
                    label: const Text('تسوق الآن'),
                  ),
                ],
              ),
            );
          }

          return Column(
            children: [
              // Cart Items
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _loadCart,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: cartProvider.items.length,
                    itemBuilder: (context, index) {
                      final item = cartProvider.items[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Card(
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Row(
                              children: [
                                // Product Image
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(8),
                                  child: Container(
                                    width: 80,
                                    height: 80,
                                    color: Colors.grey[200],
                                    child: item.product?.image != null
                                        ? Image.network(
                                            item.product!.image!,
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) => const Icon(Icons.image),
                                          )
                                        : const Icon(Icons.image, size: 40),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                // Product Details
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item.product?.name ?? 'منتج',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 16,
                                        ),
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        Formatters.formatPrice(item.price),
                                        style: TextStyle(
                                          color: Theme.of(context).primaryColor,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      // Quantity Controls
                                      Row(
                                        children: [
                                          IconButton(
                                            onPressed: () {
                                              if (item.quantity > 1) {
                                                cartProvider.updateQuantity(
                                                  item.productId,
                                                  item.quantity - 1,
                                                );
                                              } else {
                                                cartProvider.removeFromCart(item.productId);
                                              }
                                            },
                                            icon: const Icon(Icons.remove_circle_outline),
                                            iconSize: 24,
                                          ),
                                          Text(
                                            '${item.quantity}',
                                            style: const TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          IconButton(
                                            onPressed: () {
                                              cartProvider.updateQuantity(
                                                item.productId,
                                                item.quantity + 1,
                                              );
                                            },
                                            icon: const Icon(Icons.add_circle_outline),
                                            iconSize: 24,
                                          ),
                                          const Spacer(),
                                          IconButton(
                                            onPressed: () {
                                              cartProvider.removeFromCart(item.productId);
                                            },
                                            icon: const Icon(Icons.delete_outline, color: Colors.red),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),

              // Coupon & Summary Section
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 10,
                      offset: const Offset(0, -4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Coupon Input
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _couponController,
                            decoration: InputDecoration(
                              hintText: 'كود الخصم',
                              prefixIcon: const Icon(Icons.local_offer_outlined),
                              filled: true,
                              fillColor: Colors.grey[100],
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 12,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        ElevatedButton(
                          onPressed: _isApplyingCoupon ? null : _applyCoupon,
                          child: _isApplyingCoupon
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('تطبيق'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Price Summary
                    _buildPriceRow('المجموع الفرعي', cartProvider.subtotal),
                    if (cartProvider.discount > 0)
                      _buildPriceRow(
                        'الخصم',
                        -cartProvider.discount,
                        isDiscount: true,
                      ),
                    _buildPriceRow('الشحن', cartProvider.shipping),
                    const Divider(height: 24),
                    _buildPriceRow(
                      'الإجمالي',
                      cartProvider.total,
                      isTotal: true,
                    ),
                    const SizedBox(height: 16),

                    // Checkout Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _proceedToCheckout,
                        icon: const Icon(Icons.shopping_cart_checkout),
                        label: const Text('إتمام الطلب'),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildPriceRow(
    String label,
    double amount, {
    bool isTotal = false,
    bool isDiscount = false,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: isTotal ? 16 : 14,
              fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              color: isTotal ? null : Colors.grey[600],
            ),
          ),
          Text(
            Formatters.formatPrice(amount.abs()),
            style: TextStyle(
              fontSize: isTotal ? 18 : 14,
              fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              color: isDiscount
                  ? Colors.green
                  : isTotal
                      ? Theme.of(context).primaryColor
                      : null,
            ),
          ),
        ],
      ),
    );
  }
}
