import 'package:flutter/material.dart';

class CheckoutScreen extends StatelessWidget {
  const CheckoutScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('إتمام الطلب'),
      ),
      body: const Center(
        child: Text('شاشة إتمام الطلب'),
      ),
    );
  }
}
