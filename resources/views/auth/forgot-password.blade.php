<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover:not(.btn-disabled) {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        .btn-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo Section -->
        <div class="text-center mb-8 fade-in">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full shadow-lg">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Reset Your Password</h1>
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
            <form id="password-reset-form">
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

                <!-- reCAPTCHA Badge -->
                <div class="mb-6 text-center">
                    <div class="grecaptcha-badge inline-block" data-style="inline"></div>
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
                <a href="#" class="text-purple-600 hover:text-purple-800 font-medium transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Login
                </a>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="text-center mt-8 text-sm text-gray-500 fade-in">
            <p>Need help? <a href="#" class="text-purple-600 hover:text-purple-800 transition-colors duration-200">Contact Support</a></p>
            <p class="mt-2">
                <a href="#" class="text-purple-600 hover:text-purple-800 transition-colors duration-200 mr-4">Terms of Service</a>
                <a href="#" class="text-purple-600 hover:text-purple-800 transition-colors duration-200">Privacy Policy</a>
            </p>
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
            const statusMessage = document.getElementById('status-message');
            const statusText = document.getElementById('status-text');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');

            // Hide messages initially
            hideMessages();

            // Form submission handler
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Execute reCAPTCHA before form submission
                grecaptcha.ready(function() {
                    grecaptcha.execute('YOUR_RECAPTCHA_SITE_KEY', {action: 'password_reset'}).then(function(token) {
                        // Add token to form data and proceed with validation
                        validateAndSubmitForm(token);
                    }).catch(function(error) {
                        console.error('reCAPTCHA error:', error);
                        // Fallback: proceed without reCAPTCHA but show warning
                        showError('Security check failed. Please try again.');
                    });
                });
            });

            function validateAndSubmitForm(recaptchaToken) {
                // Reset error messages
                hideMessages();
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

                    // Simulate API call (replace with actual API call)
                    setTimeout(() => {
                        // In a real implementation, you would make a fetch request to your backend
                        // For demo purposes, we'll simulate a successful response
                        simulatePasswordResetRequest(email, recaptchaToken);
                    }, 1500);
                }
            }

            function simulatePasswordResetRequest(email, recaptchaToken) {
                // This is a simulation - replace with actual API call
                console.log('Sending password reset request for:', email);
                console.log('reCAPTCHA token:', recaptchaToken);

                // Simulate successful response
                const success = Math.random() > 0.2; // 80% success rate for demo

                if (success) {
                    showStatus('Password reset link has been sent to your email!');
                    form.reset();
                } else {
                    showError('We could not find an account with that email address. Please try again.');
                }

                resetButtonState();
            }

            function showStatus(message) {
                statusText.textContent = message;
                statusMessage.classList.remove('hidden');
                errorMessage.classList.add('hidden');

                // Auto-hide success message after 5 seconds
                setTimeout(() => {
                    statusMessage.classList.add('hidden');
                }, 5000);
            }

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
                statusMessage.classList.add('hidden');
            }

            function hideMessages() {
                statusMessage.classList.add('hidden');
                errorMessage.classList.add('hidden');
            }

            function resetButtonState() {
                submitButton.disabled = false;
                submitButton.classList.remove('btn-disabled');
                buttonText.textContent = 'Send Reset Link';
                buttonSpinner.classList.add('hidden');
            }

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
