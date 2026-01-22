<?php
/**
 * Footer Layout - تذييل الموقع الرئيسي
 */
?>
<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- عن المتجر -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white"><?= APP_NAME ?? 'SwiftCart' ?></span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    متجر إلكتروني متكامل يوفر لك أفضل المنتجات بأسعار منافسة مع خدمة توصيل سريعة وآمنة.
                </p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="p-2 bg-gray-800 rounded-lg hover:bg-blue-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                        </svg>
                    </a>
                    <a href="#" class="p-2 bg-gray-800 rounded-lg hover:bg-blue-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="#" class="p-2 bg-gray-800 rounded-lg hover:bg-green-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- روابط سريعة -->
            <div>
                <h3 class="text-white font-bold mb-4">روابط سريعة</h3>
                <ul class="space-y-2">
                    <li><a href="<?= BASE_URL ?>/products.php" class="text-gray-400 hover:text-white transition">المنتجات</a></li>
                    <li><a href="<?= BASE_URL ?>/categories.php" class="text-gray-400 hover:text-white transition">التصنيفات</a></li>
                    <li><a href="<?= BASE_URL ?>/offers.php" class="text-gray-400 hover:text-white transition">العروض</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php" class="text-gray-400 hover:text-white transition">من نحن</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php" class="text-gray-400 hover:text-white transition">اتصل بنا</a></li>
                </ul>
            </div>
            
            <!-- خدمة العملاء -->
            <div>
                <h3 class="text-white font-bold mb-4">خدمة العملاء</h3>
                <ul class="space-y-2">
                    <li><a href="<?= BASE_URL ?>/faq.php" class="text-gray-400 hover:text-white transition">الأسئلة الشائعة</a></li>
                    <li><a href="<?= BASE_URL ?>/shipping.php" class="text-gray-400 hover:text-white transition">سياسة الشحن</a></li>
                    <li><a href="<?= BASE_URL ?>/returns.php" class="text-gray-400 hover:text-white transition">الإرجاع والاستبدال</a></li>
                    <li><a href="<?= BASE_URL ?>/privacy.php" class="text-gray-400 hover:text-white transition">سياسة الخصوصية</a></li>
                    <li><a href="<?= BASE_URL ?>/terms.php" class="text-gray-400 hover:text-white transition">الشروط والأحكام</a></li>
                </ul>
            </div>
            
            <!-- تواصل معنا -->
            <div>
                <h3 class="text-white font-bold mb-4">تواصل معنا</h3>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span dir="ltr">+966 50 000 0000</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>info@swiftcart.com</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>الرياض، المملكة العربية السعودية</span>
                    </li>
                </ul>
                
                <!-- النشرة البريدية -->
                <div class="mt-6">
                    <h4 class="text-white font-medium mb-2">اشترك في نشرتنا البريدية</h4>
                    <form class="flex gap-2">
                        <input type="email" 
                               placeholder="بريدك الإلكتروني"
                               class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            اشتراك
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- وسائل الدفع -->
        <div class="mt-12 pt-8 border-t border-gray-800">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-400 text-sm">
                    © <?= date('Y') ?> <?= APP_NAME ?? 'SwiftCart' ?>. جميع الحقوق محفوظة.
                </p>
                <div class="flex items-center gap-4">
                    <span class="text-gray-500 text-sm">وسائل الدفع:</span>
                    <div class="flex gap-2">
                        <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                            <span class="text-xs font-bold text-blue-700">VISA</span>
                        </div>
                        <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                            <span class="text-xs font-bold text-red-600">MC</span>
                        </div>
                        <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                            <span class="text-xs font-bold text-purple-600">mada</span>
                        </div>
                        <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                            <span class="text-xs font-bold text-green-600">Apple</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
