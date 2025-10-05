<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Venusnap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-primary {
            background-color: #7c3aed;
            transition: all 0.3s ease;
        }
        .btn-primary:hover:not(.btn-disabled) {
            background-color: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(124, 58, 237, 0.2);
        }
        .btn-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
        }
        .background-image {
            background-image: url('https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .overlay {
            background: rgba(15, 23, 42, 0.8);
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Background Section -->
    <div class="hidden lg:flex lg:w-1/2 background-image">
        <div class="w-full h-full overlay flex items-center justify-center p-12">
            <div class="text-white max-w-md">
                <h1 class="text-4xl font-bold mb-6">Reset Your Password</h1>
                <p class="text-xl mb-8">Create a new secure password to protect your Venusnap account and continue your creative journey.</p>
                <div class="flex space-x-4">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-purple-300 mr-2"></i>
                        <span>Secure & Encrypted</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Form Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8 fade-in">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#7c3aed] rounded-full shadow-lg mb-4">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Reset Password</h1>
                <p class="text-gray-600 mt-2">Enter your email and we'll send you a reset link</p>
            </div>

            <!-- Status Messages -->
            <div id="status-message" class="hidden mb-6 p-4 rounded-lg bg-green-100 text-green-700 border border-green-200 fade-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span id="status-text"></span>
                </div>
            </div>

            <!-- Error Messages -->
            <div id="error-message" class="hidden mb-6 p-4 rounded-lg bg-red-100 text-red-700 border border-red-200 fade-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="error-text"></span>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card bg-white rounded-xl p-8 fade-in">
                <form id="password-reset-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-gray-500"></i>Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autofocus
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="you@example.com"
                        >
                        <div id="email_error" class="hidden text-red-500 text-sm mt-2 ml-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <span></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        id="submit-button"
                        class="btn-primary w-full py-3 px-4 rounded-lg text-white font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                        <span id="button-text">Send Reset Link</span>
                        <span id="button-spinner" class="hidden ml-2">
                            <i class="fas fa-spinner spinner"></i>
                        </span>
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="/login" class="text-[#7c3aed] hover:text-[#6d28d9] font-medium transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Login
                    </a>
                </div>
            </div>

            <!-- Download App Section -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600 mb-4">Get the full experience with our mobile app</p>
                <a
                    href="https://play.google.com/store/apps/details?id=com.venusnap.app"
                    class="inline-flex items-center justify-center bg-gray-800 text-white font-medium py-3 px-6 rounded-lg hover:bg-gray-900 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2"
                    target="_blank"
                >
                    <i class="fab fa-google-play text-xl mr-2"></i>
                    <div class="text-left">
                        <div class="text-xs">GET IT ON</div>
                        <div class="text-lg font-bold">Google Play</div>
                    </div>
                </a>
            </div>

            <!-- Footer Links -->
            <div class="text-center mt-8 text-sm text-gray-500 fade-in">
                <p>Need help? <a href="mailto:support@venusnap.com" class="text-[#7c3aed] hover:text-[#6d28d9] transition-colors duration-200">Contact Support</a></p>
                <p class="mt-2">
                    <a href="/terms/of/service" class="text-[#7c3aed] hover:text-[#6d28d9] transition-colors duration-200 mr-4">Terms of Service</a>
                    <a href="/privacy/policy" class="text-[#7c3aed] hover:text-[#6d28d9] transition-colors duration-200">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('password-reset-form');
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email_error');

            // Form submission handler
            form.addEventListener('submit', function(e) {
                // Reset error messages
                emailError.classList.add('hidden');

                // Get form values
                const email = emailInput.value;

                let isValid = true;

                // Validate email
                if (!email) {
                    emailError.querySelector('span').textContent = 'Please enter your email address';
                    emailError.classList.remove('hidden');
                    isValid = false;
                } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                    emailError.querySelector('span').textContent = 'Please enter a valid email address';
                    emailError.classList.remove('hidden');
                    isValid = false;
                }

                if (isValid) {
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.classList.add('btn-disabled');
                    buttonText.textContent = 'Sending reset link...';
                    buttonSpinner.classList.remove('hidden');

                    // Form will now submit naturally to your Laravel backend
                    // The loading state will persist until the page reloads
                } else {
                    e.preventDefault(); // Only prevent default if validation fails
                }
            });

            // Add input event listeners to clear errors when user starts typing
            emailInput.addEventListener('input', function() {
                if (!emailError.classList.contains('hidden')) {
                    emailError.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
