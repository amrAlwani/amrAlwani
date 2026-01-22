    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="mr-2 text-xl font-bold text-white">SwiftCart</span>
                    </div>
                    <p class="text-sm">متجرك الإلكتروني الموثوق لتسوق سريع وآمن.</p>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="hover:text-white">الرئيسية</a></li>
                        <li><a href="products.php" class="hover:text-white">المنتجات</a></li>
                        <li><a href="categories.php" class="hover:text-white">التصنيفات</a></li>
                        <li><a href="contact.php" class="hover:text-white">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">حسابي</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="login.php" class="hover:text-white">تسجيل الدخول</a></li>
                        <li><a href="register.php" class="hover:text-white">إنشاء حساب</a></li>
                        <li><a href="orders.php" class="hover:text-white">طلباتي</a></li>
                        <li><a href="cart.php" class="hover:text-white">السلة</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">تواصل معنا</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            info@swiftcart.com
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            +966 50 123 4567
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm">
                <p>&copy; <?= date('Y') ?> SwiftCart. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
