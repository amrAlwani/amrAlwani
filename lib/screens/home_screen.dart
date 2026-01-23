import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/products_provider.dart';
import '../providers/cart_provider.dart';
import '../widgets/product_card.dart';
import '../widgets/category_chip.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    final productsProvider = Provider.of<ProductsProvider>(context, listen: false);
    await productsProvider.fetchProducts(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('متجري'),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () {
              Navigator.of(context).pushNamed('/products');
            },
          ),
          Consumer<CartProvider>(
            builder: (context, cart, child) {
              return Stack(
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined),
                    onPressed: () {
                      Navigator.of(context).pushNamed('/cart');
                    },
                  ),
                  if (cart.itemCount > 0)
                    Positioned(
                      right: 8.w(context),
                      top: 8.h(context),
                      child: Container(
                        padding: EdgeInsets.all(4.w(context)),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.error,
                          shape: BoxShape.circle,
                        ),
                        child: Text(
                          '${cart.itemCount}',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10.sp(context),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome Banner
              Container(
                margin: EdgeInsets.all(16.w(context)),
                padding: EdgeInsets.all(20.w(context)),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Theme.of(context).colorScheme.primary,
                      Theme.of(context).colorScheme.secondary,
                    ],
                  ),
                  borderRadius: BorderRadius.circular(16.w(context)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'مرحباً بك!',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 24.sp(context),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 8.h(context)),
                    Text(
                      'اكتشف أحدث المنتجات والعروض',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 14.sp(context),
                      ),
                    ),
                  ],
                ),
              ),

              // Section Title
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.w(context)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'المنتجات',
                      style: TextStyle(
                        fontSize: 18.sp(context),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pushNamed('/products');
                      },
                      child: const Text('عرض الكل'),
                    ),
                  ],
                ),
              ),

              // Products Grid
              Consumer<ProductsProvider>(
                builder: (context, productsProvider, _) {
                  if (productsProvider.isLoading) {
                    return SizedBox(
                      height: 300.h(context),
                      child: const Center(child: CircularProgressIndicator()),
                    );
                  }

                  if (productsProvider.products.isEmpty) {
                    return SizedBox(
                      height: 200.h(context),
                      child: const Center(
                        child: Text('لا توجد منتجات'),
                      ),
                    );
                  }

                  return GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    padding: EdgeInsets.all(16.w(context)),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      childAspectRatio: 0.65,
                      crossAxisSpacing: 12.w(context),
                      mainAxisSpacing: 12.h(context),
                    ),
                    itemCount: productsProvider.products.length > 6 
                        ? 6 
                        : productsProvider.products.length,
                    itemBuilder: (context, index) {
                      final product = productsProvider.products[index];
                      return ProductCard(product: product);
                    },
                  );
                },
              ),

              SizedBox(height: 80.h(context)),
            ],
          ),
        ),
      ),

      // Bottom Navigation
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        type: BottomNavigationBarType.fixed,
        onTap: (index) {
          setState(() => _currentIndex = index);
          switch (index) {
            case 0:
              break;
            case 1:
              Navigator.of(context).pushNamed('/products');
              break;
            case 2:
              Navigator.of(context).pushNamed('/favorites');
              break;
            case 3:
              Navigator.of(context).pushNamed('/orders');
              break;
            case 4:
              Navigator.of(context).pushNamed('/profile');
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'الرئيسية',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.grid_view_outlined),
            activeIcon: Icon(Icons.grid_view),
            label: 'المنتجات',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.favorite_outline),
            activeIcon: Icon(Icons.favorite),
            label: 'المفضلة',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.receipt_long_outlined),
            activeIcon: Icon(Icons.receipt_long),
            label: 'طلباتي',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            activeIcon: Icon(Icons.person),
            label: 'حسابي',
          ),
        ],
      ),
    );
  }
}
