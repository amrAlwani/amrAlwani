<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="text-5xl mb-4 block">👤</span>
            <h1 class="text-2xl font-bold text-gray-800">تسجيل الدخول</h1>
            <p class="text-gray-500 mt-2">أهلاً بك مرة أخرى!</p>
        </div>
        
        <!-- Login Form -->
        <form method="POST" action="<?= url('login') ?>" id="loginForm" autocomplete="off">
            <?= csrf_field() ?>
            
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
            
            <!-- Password -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-gray-700 font-medium">كلمة المرور</label>
                    <a href="<?= url('forgot-password') ?>" class="text-primary-600 text-sm hover:underline">
                        نسيت كلمة المرور؟
                    </a>
                </div>
                <div class="relative">
                    <input type="password" 
                           id="password"
                           name="password" 
                           required
                           autocomplete="current-password"
                           maxlength="100"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <button type="button" 
                            onclick="togglePassword('password', this)"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        👁️
                    </button>
                </div>
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center mb-6">
                <input type="checkbox" id="remember" name="remember" 
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <label for="remember" class="mr-2 text-gray-600">تذكرني</label>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" 
                    id="submitBtn"
                    class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-bold transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span>تسجيل الدخول</span>
            </button>
        </form>
        
        <!-- Divider -->
        <div class="my-6 flex items-center">
            <hr class="flex-1 border-gray-300">
            <span class="px-4 text-gray-400 text-sm">أو</span>
            <hr class="flex-1 border-gray-300">
        </div>
        
        <!-- Register Link -->
        <p class="text-center text-gray-600">
            ليس لديك حساب؟
            <a href="<?= url('register') ?>" class="text-primary-600 font-medium hover:underline">
                سجل الآن
            </a>
        </p>
    </div>
    
    <!-- Security Notice -->
    <p class="text-center text-gray-400 text-sm mt-6">
        🔒 اتصالك آمن ومشفر
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

// Prevent double submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⏳</span> جاري التسجيل...';
});
</script>
