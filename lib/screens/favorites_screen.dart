import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constant/responsive_size.dart';
import '../providers/favorites_provider.dart';
import '../widgets/product_card.dart';

/// شاشة المفضلة
class FavoritesScreen extends StatelessWidget {
  const FavoritesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('المفضلة'),
        actions: [
          Consumer<FavoritesProvider>(
            builder: (context, favorites, child) {
              if (favorites.count == 0) return const SizedBox.shrink();
              return IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () {
                  _showClearConfirmation(context, favorites);
                },
              );
            },
          ),
        ],
      ),
      body: Consumer<FavoritesProvider>(
        builder: (context, favorites, child) {
          if (favorites.isLoading) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }

          if (favorites.count == 0) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.favorite_outline,
                    size: 80.sp(context),
                    color: Theme.of(context).colorScheme.outline,
                  ),
                  SizedBox(height: 16.h(context)),
                  Text(
                    'لا توجد منتجات في المفضلة',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: Theme.of(context).colorScheme.outline,
                    ),
                  ),
                  SizedBox(height: 8.h(context)),
                  Text(
                    'أضف منتجات للمفضلة لتجدها هنا',
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

          return GridView.builder(
            padding: EdgeInsets.all(16.w(context)),
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.65,
              crossAxisSpacing: 12.w(context),
              mainAxisSpacing: 12.h(context),
            ),
            itemCount: favorites.count,
            itemBuilder: (context, index) {
              return ProductCard(
                product: favorites.favorites[index],
                showFavoriteButton: true,
              );
            },
          );
        },
      ),
    );
  }

  void _showClearConfirmation(BuildContext context, FavoritesProvider favorites) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('مسح المفضلة'),
        content: const Text('هل أنت متأكد من حذف جميع المنتجات من المفضلة؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () {
              favorites.clearFavorites();
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم مسح المفضلة')),
              );
            },
            child: const Text('حذف الكل'),
          ),
        ],
      ),
    );
  }
}
