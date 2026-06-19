@extends('auth.layouts.auth-layout')

@section('title', 'Register')

@section('content')
<!-- Register Panel -->
<div class="panel-gradient glass-panel rounded-[32px] p-10 w-full max-w-md relative z-10">
    
    <!-- Logo & Branding -->
    <div class="text-center mb-6">
        <!-- AgriData Logo -->
        <div class="flex items-center justify-center mb-4">
            <div class="w-12 h-12 bg-primary-800 rounded-xl flex items-center justify-center mr-3">
                <i data-lucide="leaf" class="text-white w-7 h-7"></i>
            </div>
            <h1 class="text-3xl font-bold text-primary-800">AgriData</h1>
        </div>
        
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Account</h2>
        <p class="text-gray-600 text-sm">Join AgriData to start your journey</p>
    </div>
    
    <!-- Register Form -->
    <form id="registerForm" class="space-y-4">
        @csrf
        
        <!-- Name Input -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="input-glow w-full pl-12 pr-4 py-3 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300"
                    placeholder="John Doe"
                    required
                >
            </div>
        </div>
        
        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="input-glow w-full pl-12 pr-4 py-3 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300"
                    placeholder="farmer@example.com"
                    required
                >
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
                    class="input-glow w-full pl-12 pr-4 py-3 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300"
                    placeholder="••••••••"
                    required
                >
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center"
                    onclick="togglePassword('password', 'togglePassword')"
                >
                    <i data-lucide="eye" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition" id="togglePassword"></i>
                </button>
            </div>
        </div>
        
        <!-- Confirm Password Input -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                </div>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="input-glow w-full pl-12 pr-4 py-3 bg-white/80 border border-gray-300 rounded-xl focus:outline-none transition-all duration-300"
                    placeholder="••••••••"
                    required
                >
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center"
                    onclick="togglePassword('password_confirmation', 'toggleConfirmPassword')"
                >
                    <i data-lucide="eye" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition" id="toggleConfirmPassword"></i>
                </button>
            </div>
        </div>
        
        <!-- Terms & Conditions -->
        <div class="flex items-start">
            <input 
                type="checkbox" 
                id="terms" 
                name="terms"
                class="w-4 h-4 mt-1 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                required
            >
            <label for="terms" class="ml-2 text-sm text-gray-700">
                I agree to the 
                <a href="#" class="text-primary-600 hover:text-primary-800 font-medium">Terms of Service</a>
                and 
                <a href="#" class="text-primary-600 hover:text-primary-800 font-medium">Privacy Policy</a>
            </label>
        </div>
        
        <!-- Submit Button -->
        <button 
            type="submit" 
            class="btn-gradient w-full text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg"
        >
            Create Account
        </button>
    </form>
    
    <!-- Login Link -->
    <div class="mt-5 text-center">
        <p class="text-gray-600">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-800 font-semibold transition">
                Login here
            </a>
        </p>
    </div>
    
    <!-- Back to Home -->
    <div class="mt-3 text-center">
        <a href="/" class="text-sm text-gray-500 hover:text-gray-700 transition flex items-center justify-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
            Back to home
        </a>
    </div>
</div>

<!-- Success Modal (Hidden by default) -->
<div id="successModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="check-circle" class="w-10 h-10 text-green-600"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Registration Successful!</h3>
        <p class="text-gray-600 mb-6">Your account has been created. Please login to continue.</p>
        <a href="{{ route('login') }}" class="btn-gradient inline-block text-white font-semibold py-3 px-8 rounded-xl shadow-lg">
            Go to Login
        </a>
    </div>
</div>

<!-- Error Modal (Hidden by default) -->
<div id="errorModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="x-circle" class="w-10 h-10 text-red-600"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Registration Failed</h3>
        <p id="errorMessage" class="text-gray-600 mb-6"></p>
        <button onclick="closeErrorModal()" class="btn-gradient inline-block text-white font-semibold py-3 px-8 rounded-xl shadow-lg">
            Try Again
        </button>
    </div>
</div>

<!-- Custom Scripts -->
<script>
// Toggle Password Visibility
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.setAttribute('data-lucide', 'eye-off');
    } else {
        passwordInput.type = 'password';
        toggleIcon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}

// Form Validation & Submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const terms = document.getElementById('terms').checked;
    
    // Validation
    if (!name || !email || !password || !passwordConfirmation) {
        showError('Please fill in all required fields');
        return;
    }
    
    if (!terms) {
        showError('Please agree to Terms of Service and Privacy Policy');
        return;
    }
    
    if (password.length < 8) {
        showError('Password must be at least 8 characters long');
        return;
    }
    
    if (password !== passwordConfirmation) {
        showError('Passwords do not match');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address');
        return;
    }
    
    // If all validation passes, show success modal
    showSuccessModal();
});

// Show Success Modal
function showSuccessModal() {
    document.getElementById('successModal').classList.remove('hidden');
    lucide.createIcons();
}

// Show Error Modal
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
    lucide.createIcons();
}

// Close Error Modal
function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('errorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeErrorModal();
    }
});
</script>
@endsection