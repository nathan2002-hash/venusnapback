<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Venusnap</title>
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
        .text-primary {
            color: #7c3aed;
        }
        .border-primary {
            border-color: #7c3aed;
        }
        .input-focus:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
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
        .background-image {
            background-image: url('https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        .card-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .success-message {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Background Section -->
    <div class="hidden lg:flex lg:w-1/2 background-image">
        <div class="w-full h-full overlay flex items-center justify-center p-12">
            <div class="text-white max-w-md">
                <h1 class="text-4xl font-bold mb-6">Reset Your Password</h1>
                <p class="text-xl mb-8">Create a new secure password to protect your Venusnap account and continue your wellness journey.</p>
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
                <p class="text-gray-600 mt-2">Create a new secure password for your account</p>
            </div>

            <!-- Validation Errors -->
            <div id="validation-errors" class="hidden mb-6 p-4 rounded-lg error-message fade-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="error-text"></span>
                </div>
            </div>

            <!-- Success Message -->
            <div id="success-message" class="hidden mb-6 p-4 rounded-lg success-message fade-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span id="success-text"></span>
                </div>
            </div>

            <form id="password-reset-form" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gray-500"></i>Email Address
                    </label>
                    <div class="relative">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg input-focus"
                            placeholder="you@example.com"
                            required
                            autofocus
                            value="{{ old('email', $request->email) }}"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                    <div id="email_error" class="hidden text-red-500 text-sm mt-2 ml-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span></span>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-gray-500"></i>New Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg input-focus"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="toggle-password" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>Password must be at least 8 characters long</p>
                    </div>
                    <div id="password_error" class="hidden text-red-500 text-sm mt-2 ml-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span></span>
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-gray-500"></i>Confirm New Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg input-focus"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="toggle-password-confirmation" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div id="password_confirmation_error" class="hidden text-red-500 text-sm mt-2 ml-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span></span>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div class="mb-6">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">Password Strength</span>
                        <span id="password-strength-text" class="text-sm font-medium">Weak</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="password-strength-bar" class="bg-red-500 h-2 rounded-full" style="width: 20%"></div>
                    </div>
                </div>

                <!-- Submit Button with Spinner -->
                <button
                    type="submit"
                    id="submit-button"
                    class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 flex items-center justify-center"
                >
                    <span id="button-text">Reset Password</span>
                    <span id="button-spinner" class="spinner hidden"></span>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-[#7c3aed] hover:text-[#6d28d9] font-medium transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Login
                </a>
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
            <div class="mt-8 text-center text-xs text-gray-500">
                <p>By continuing, you agree to our <a href="#" class="text-[#7c3aed] hover:underline">Terms of Service</a> and <a href="#" class="text-[#7c3aed] hover:underline">Privacy Policy</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('password-reset-form');
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const togglePasswordButton = document.getElementById('toggle-password');
            const togglePasswordConfirmationButton = document.getElementById('toggle-password-confirmation');
            const passwordStrengthBar = document.getElementById('password-strength-bar');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const validationErrors = document.getElementById('validation-errors');
            const successMessage = document.getElementById('success-message');

            // Toggle password visibility
            togglePasswordButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            togglePasswordConfirmationButton.addEventListener('click', function() {
                const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmationInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let strengthText = 'Weak';
                let strengthColor = 'bg-red-500';
                let width = 20;

                // Check password length
                if (password.length >= 8) {
                    strength += 1;
                }

                // Check for mixed case
                if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
                    strength += 1;
                }

                // Check for numbers
                if (password.match(/([0-9])/)) {
                    strength += 1;
                }

                // Check for special characters
                if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
                    strength += 1;
                }

                // Update strength indicator
                if (strength === 0) {
                    strengthText = 'Weak';
                    strengthColor = 'bg-red-500';
                    width = 20;
                } else if (strength === 1) {
                    strengthText = 'Weak';
                    strengthColor = 'bg-red-500';
                    width = 40;
                } else if (strength === 2) {
                    strengthText = 'Fair';
                    strengthColor = 'bg-yellow-500';
                    width = 60;
                } else if (strength === 3) {
                    strengthText = 'Good';
                    strengthColor = 'bg-blue-500';
                    width = 80;
                } else {
                    strengthText = 'Strong';
                    strengthColor = 'bg-green-500';
                    width = 100;
                }

                passwordStrengthBar.className = `${strengthColor} h-2 rounded-full transition-all duration-300`;
                passwordStrengthBar.style.width = `${width}%`;
                passwordStrengthText.textContent = strengthText;
            });

            // Form submission handler
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Reset error messages
                hideMessages();
                document.querySelectorAll('[id$="_error"]').forEach(el => {
                    el.classList.add('hidden');
                });

                // Get form values
                const email = document.getElementById('email').value;
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                let isValid = true;

                // Validate email
                if (!email) {
                    document.getElementById('email_error').querySelector('span').textContent = 'Please enter your email address';
                    document.getElementById('email_error').classList.remove('hidden');
                    isValid = false;
                } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                    document.getElementById('email_error').querySelector('span').textContent = 'Please enter a valid email address';
                    document.getElementById('email_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate password
                if (!password) {
                    document.getElementById('password_error').querySelector('span').textContent = 'Please enter a password';
                    document.getElementById('password_error').classList.remove('hidden');
                    isValid = false;
                } else if (password.length < 8) {
                    document.getElementById('password_error').querySelector('span').textContent = 'Password must be at least 8 characters';
                    document.getElementById('password_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate password confirmation
                if (password !== passwordConfirmation) {
                    document.getElementById('password_confirmation_error').querySelector('span').textContent = 'Passwords do not match';
                    document.getElementById('password_confirmation_error').classList.remove('hidden');
                    isValid = false;
                }

                if (isValid) {
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.classList.add('btn-disabled');
                    buttonText.textContent = 'Resetting Password...';
                    buttonSpinner.classList.remove('hidden');

                    // In a real implementation, you would submit the form via AJAX
                    // For this example, we'll simulate a successful reset
                    setTimeout(() => {
                        // Simulate successful password reset
                        showSuccess('Your password has been reset successfully!');
                        form.reset();
                        resetButtonState();

                        // Redirect to login after 2 seconds
                        setTimeout(() => {
                            window.location.href = "{{ route('login') }}";
                        }, 2000);
                    }, 1500);
                }
            });

            function showSuccess(message) {
                document.getElementById('success-text').textContent = message;
                successMessage.classList.remove('hidden');
                validationErrors.classList.add('hidden');
            }

            function showError(message) {
                document.getElementById('error-text').textContent = message;
                validationErrors.classList.remove('hidden');
                successMessage.classList.add('hidden');
            }

            function hideMessages() {
                validationErrors.classList.add('hidden');
                successMessage.classList.add('hidden');
            }

            function resetButtonState() {
                submitButton.disabled = false;
                submitButton.classList.remove('btn-disabled');
                buttonText.textContent = 'Reset Password';
                buttonSpinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
