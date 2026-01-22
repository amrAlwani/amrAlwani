<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="text-5xl mb-4 block">✨</span>
            <h1 class="text-2xl font-bold text-gray-800">إنشاء حساب جديد</h1>
            <p class="text-gray-500 mt-2">انضم إلينا واستمتع بتجربة تسوق فريدة</p>
        </div>
        
        <!-- Register Form -->
        <form method="POST" action="<?= url('register') ?>" id="registerForm" autocomplete="off">
            <?= csrf_field() ?>
            
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">الاسم الكامل</label>
                <div class="relative">
                    <input type="text" 
                           id="name"
                           name="name" 
                           value="<?= old('name') ?>" 
                           required
                           autocomplete="name"
                           minlength="2"
                           maxlength="100"
                           pattern="^[\u0600-\u06FFa-zA-Z\s]+$"
                           title="الاسم يجب أن يحتوي على حروف فقط"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">👤</span>
                </div>
            </div>
            
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">البريد الإلكتروني</label>
                <div class="relative">
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="<?= old('email') ?>" 
                           required
                           autocomplete="email"
                           maxlength="255"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">📧</span>
                </div>
            </div>
            
            <!-- Phone -->
            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-medium mb-2">رقم الهاتف</label>
                <div class="relative">
                    <input type="tel" 
                           id="phone"
                           name="phone" 
                           value="<?= old('phone') ?>" 
                           required
                           autocomplete="tel"
                           placeholder="05xxxxxxxx"
                           pattern="^(05|5|9665)[0-9]{8}$"
                           title="أدخل رقم هاتف سعودي صالح"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">📱</span>
                </div>
                <p class="text-gray-400 text-xs mt-1">مثال: 0512345678</p>
            </div>
            
            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-2">كلمة المرور</label>
                <div class="relative">
                    <input type="password" 
                           id="password"
                           name="password" 
                           required
                           autocomplete="new-password"
                           minlength="8"
                           maxlength="100"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <button type="button" 
                            onclick="togglePassword('password', this)"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        👁️
                    </button>
                </div>
                
                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div id="strength1" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength2" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength3" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength4" class="h-1 flex-1 bg-gray-200 rounded"></div>
                    </div>
                    <p id="strengthText" class="text-xs text-gray-400">كلمة المرور يجب أن تكون 8 أحرف على الأقل</p>
                </div>
            </div>
            
            <!-- Confirm Password -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">تأكيد كلمة المرور</label>
                <div class="relative">
                    <input type="password" 
                           id="password_confirmation"
                           name="password_confirmation" 
                           required
                           autocomplete="new-password"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <button type="button" 
                            onclick="togglePassword('password_confirmation', this)"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        👁️
                    </button>
                </div>
                <p id="matchError" class="text-red-500 text-xs mt-1 hidden">كلمات المرور غير متطابقة</p>
            </div>
            
            <!-- Terms -->
            <div class="flex items-start mb-6">
                <input type="checkbox" id="terms" name="terms" required
                       class="w-4 h-4 mt-1 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <label for="terms" class="mr-2 text-gray-600 text-sm">
                    أوافق على 
                    <a href="#" class="text-primary-600 hover:underline">شروط الاستخدام</a>
                    و
                    <a href="#" class="text-primary-600 hover:underline">سياسة الخصوصية</a>
                </label>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" 
                    id="submitBtn"
                    class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-bold transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span>إنشاء حساب</span>
            </button>
        </form>
        
        <!-- Divider -->
        <div class="my-6 flex items-center">
            <hr class="flex-1 border-gray-300">
            <span class="px-4 text-gray-400 text-sm">أو</span>
            <hr class="flex-1 border-gray-300">
        </div>
        
        <!-- Login Link -->
        <p class="text-center text-gray-600">
            لديك حساب بالفعل؟
            <a href="<?= url('login') ?>" class="text-primary-600 font-medium hover:underline">
                سجل دخول
            </a>
        </p>
    </div>
    
    <!-- Security Notice -->
    <p class="text-center text-gray-400 text-sm mt-6">
        🔒 بياناتك محمية ومشفرة
    </p>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁️';
    }
}

// Password strength checker
const password = document.getElementById('password');
const confirmation = document.getElementById('password_confirmation');
const strengthBars = [
    document.getElementById('strength1'),
    document.getElementById('strength2'),
    document.getElementById('strength3'),
    document.getElementById('strength4')
];
const strengthText = document.getElementById('strengthText');
const matchError = document.getElementById('matchError');

password.addEventListener('input', function() {
    const value = this.value;
    let score = 0;
    
    if (value.length >= 8) score++;
    if (/[a-z]/.test(value)) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^a-zA-Z0-9]/.test(value)) score++;
    
    // Reset bars
    strengthBars.forEach(bar => bar.className = 'h-1 flex-1 bg-gray-200 rounded');
    
    // Color bars based on score
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
    const texts = ['ضعيفة جداً', 'ضعيفة', 'متوسطة', 'قوية'];
    const textColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-green-500'];
    
    for (let i = 0; i < Math.min(score, 4); i++) {
        strengthBars[i].className = `h-1 flex-1 ${colors[Math.min(score - 1, 3)]} rounded`;
    }
    
    if (value.length > 0) {
        strengthText.textContent = texts[Math.min(score - 1, 3)] || 'ضعيفة جداً';
        strengthText.className = `text-xs ${textColors[Math.min(score - 1, 3)] || 'text-red-500'}`;
    } else {
        strengthText.textContent = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        strengthText.className = 'text-xs text-gray-400';
    }
    
    checkMatch();
});

confirmation.addEventListener('input', checkMatch);

function checkMatch() {
    if (confirmation.value.length > 0) {
        if (password.value !== confirmation.value) {
            matchError.classList.remove('hidden');
            confirmation.classList.add('border-red-500');
        } else {
            matchError.classList.add('hidden');
            confirmation.classList.remove('border-red-500');
        }
    } else {
        matchError.classList.add('hidden');
        confirmation.classList.remove('border-red-500');
    }
}

// Prevent double submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (password.value !== confirmation.value) {
        e.preventDefault();
        matchError.classList.remove('hidden');
        return;
    }
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⏳</span> جاري إنشاء الحساب...';
});
</script>
