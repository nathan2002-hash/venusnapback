<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Venusnap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">
    <style>
        .btn-primary {
            background-color: #7c3aed;
        }
        .btn-primary:hover {
            background-color: #6d28d9;
        }
        .text-primary {
            color: #7c3aed;
        }
        .border-primary {
            border-color: #7c3aed;
        }
        .input-focus:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 1px #7c3aed;
        }
        /* Spinner styles */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .btn-disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-[#7c3aed] p-6 text-white">
           <center>
                <h1 class="text-2xl font-bold">Welcome Back</h1>
                <p class="text-[#d8b4fe] mt-1">Sign in to your Venusnap account</p>
           </center>
        </div>

        <form id="login-form" class="p-6" method="POST" action="{{ route('login') }}">
            @csrf <!-- CSRF token for Laravel -->

            <!-- Email Input -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus"
                        placeholder="you@example.com"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Password Input -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus"
                        placeholder="••••••••"
                        required
                    >
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 text-[#7c3aed] focus:ring-[#7c3aed] border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-[#7c3aed] hover:text-[#6d28d9]">Forgot password?</a>
                </div>
            </div>

            <!-- Submit Button with Spinner -->
            <button
                type="submit"
                id="submit-button"
                class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 flex items-center justify-center"
            >
                <span id="button-text">Sign In</span>
                <span id="button-spinner" class="spinner hidden"></span>
            </button>

            <!-- Sign Up Link -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Don't have an account? <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="text-[#7c3aed] font-medium hover:text-[#6d28d9]">Download an App</a></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');

            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email');
                const password = document.getElementById('password');

                if (!email.value || !password.value) {
                    e.preventDefault();
                    alert('Please fill in all fields');
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                submitButton.classList.add('btn-disabled');
                buttonText.textContent = 'Signing in...';
                buttonSpinner.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
