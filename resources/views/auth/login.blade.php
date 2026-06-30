@extends('auth.layouts.auth-layout')

@section('title', 'Login')

@section('content')
<!-- Login Panel -->
<div class="panel-gradient glass-panel rounded-[32px] p-10 w-full max-w-md relative z-10">
    
    <!-- Logo & Branding -->
    <div class="text-center mb-8">
        <!-- AgriData Logo -->
        <div class="flex items-center justify-center mb-4">
            <div class="w-12 h-12 bg-primary-800 rounded-xl flex items-center justify-center mr-3">
                <i data-lucide="leaf" class="text-white w-7 h-7"></i>
            </div>
            <h1 class="text-3xl font-bold text-primary-800">AgriData</h1>
        </div>
        
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Login</h2>
        <p class="text-gray-600 text-sm">Sign in to access your farming dashboard</p>
    </div>
    
    @if ($errors->any())
        <div style="background-color: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Login Form -->
    <form action="{{ route('login') }}" method="POST" class="space-y-5">
        @csrf
        
        <!-- Email/Username Input -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email / Username
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="input-glow w-full pl-12 pr-4 py-3.5 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300 @error('email') border-red-500 @enderror"
                    placeholder="farmer@example.com"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Password Input -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="input-glow w-full pl-12 pr-4 py-3.5 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300"
                    placeholder="••••••••"
                    required
                >
                <!-- Show/Hide Password Toggle (Optional) -->
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center"
                    onclick="togglePassword()"
                >
                    <i data-lucide="eye" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition" id="toggleIcon"></i>
                </button>
            </div>
        </div>
        
        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="remember" 
                    name="remember"
                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                >
                <label for="remember" class="ml-2 text-sm text-gray-700">
                    Remember me
                </label>
            </div>
            <a href="#" class="text-sm text-primary-600 hover:text-primary-800 font-medium transition">
                Forgot password?
            </a>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit" 
            class="btn-gradient w-full text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg"
        >
            Sign In
        </button>
    </form>
    
    <!-- Register Link -->
    <div class="mt-6 text-center">
        <p class="text-gray-600">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary-600 hover:text-primary-800 font-semibold transition">
                Register
            </a>
        </p>
    </div>
    
    <!-- Back to Home -->
    <div class="mt-4 text-center">
        <a href="/" class="text-sm text-gray-500 hover:text-gray-700 transition flex items-center justify-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
            Back to Landing Page
        </a>
    </div>
</div>

<!-- Custom Script untuk Toggle Password -->
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.setAttribute('data-lucide', 'eye-off');
    } else {
        passwordInput.type = 'password';
        toggleIcon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}
</script>
@endsection