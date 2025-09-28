<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Venusnap</title>
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
        /* Country dropdown styles */
        .country-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 10;
            display: none;
        }
        .country-option {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }
        .country-option:hover {
            background-color: #f9fafb;
        }
        .country-option:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-[#7c3aed] p-6 text-white">
           <center>
                <h1 class="text-2xl font-bold">Create Account</h1>
                <p class="text-[#d8b4fe] mt-1">Sign up to get started with Venusnap</p>
           </center>
        </div>

        <form id="register-form" class="p-6" method="POST" action="{{ route('register') }}">
            @csrf <!-- CSRF token for Laravel -->

            <!-- Full Name Input -->
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus"
                        placeholder="John Doe"
                        required
                        autofocus
                    >
                </div>
                <div id="full_name_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Email Input -->
            <div class="mb-4">
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
                    >
                </div>
                <div id="email_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Country Selection -->
            <div class="mb-4">
                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="country"
                        name="country"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus cursor-pointer"
                        placeholder="Select your country"
                        readonly
                        required
                    >
                    <div id="country-dropdown" class="country-dropdown"></div>
                </div>
                <input type="hidden" id="country_code" name="country_code">
                <div id="country_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Phone Number Input -->
            <div class="mb-4">
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-y-0 left-0 pl-10 flex items-center pointer-events-none">
                        <span id="phone-prefix" class="text-gray-500">+</span>
                    </div>
                    <input
                        type="tel"
                        id="phone_number"
                        name="phone_number"
                        class="block w-full pl-16 pr-3 py-2 border border-gray-300 rounded-lg input-focus"
                        placeholder="123456789"
                        required
                    >
                </div>
                <div id="phone_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Password Input -->
            <div class="mb-4">
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
                <div id="password_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Confirm Password Input -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus"
                        placeholder="••••••••"
                        required
                    >
                </div>
                <div id="password_confirmation_error" class="text-red-500 text-xs mt-1 hidden"></div>
            </div>

            <!-- Policy Links -->
            <div class="mb-4 space-y-2">
                <div class="flex items-center text-sm text-[#7c3aed] cursor-pointer hover:text-[#6d28d9]" id="terms-link">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Terms & Conditions</span>
                </div>
                <div class="flex items-center text-sm text-[#7c3aed] cursor-pointer hover:text-[#6d28d9]" id="privacy-link">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Privacy Policy</span>
                </div>
            </div>

            <!-- Terms Checkboxes -->
            <div class="mb-4">
                <div class="flex items-start mb-2">
                    <input
                        id="accept_terms"
                        name="accept_terms"
                        type="checkbox"
                        class="h-4 w-4 text-[#7c3aed] focus:ring-[#7c3aed] border-gray-300 rounded mt-1"
                    >
                    <label for="accept_terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="https://www.venusnap.com/terms/conditions" class="text-[#7c3aed] hover:text-[#6d28d9] font-medium">Terms and Conditions</a>
                    </label>
                </div>
                <div class="flex items-start">
                    <input
                        id="accept_privacy"
                        name="accept_privacy"
                        type="checkbox"
                        class="h-4 w-4 text-[#7c3aed] focus:ring-[#7c3aed] border-gray-300 rounded mt-1"
                    >
                    <label for="accept_privacy" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="https://www.venusnap.com/privacy/policy" class="text-[#7c3aed] hover:text-[#6d28d9] font-medium">Privacy Policy</a>
                    </label>
                </div>
                <div id="acceptance_error" class="text-red-500 text-xs mt-1 hidden">
                    Please accept both Terms and Privacy Policy to continue
                </div>
            </div>

            <!-- Submit Button with Spinner -->
            <button
                type="submit"
                id="submit-button"
                class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 flex items-center justify-center"
            >
                <span id="button-text">Sign Up</span>
                <span id="button-spinner" class="spinner hidden"></span>
            </button>

            <!-- Sign In Link -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Already have an account? <a href="{{ route('login') }}" class="text-[#7c3aed] font-medium hover:text-[#6d28d9]">Sign In</a></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('register-form');
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');
            const countryInput = document.getElementById('country');
            const countryDropdown = document.getElementById('country-dropdown');
            const countryCodeInput = document.getElementById('country_code');
            const phonePrefix = document.getElementById('phone-prefix');
            const termsLink = document.getElementById('terms-link');
            const privacyLink = document.getElementById('privacy-link');

            // Country data
            const countries = [
                { name: "United States", code: "US", phoneCode: "1" },
                { name: "United Kingdom", code: "GB", phoneCode: "44" },
                { name: "Canada", code: "CA", phoneCode: "1" },
                { name: "Australia", code: "AU", phoneCode: "61" },
                { name: "Germany", code: "DE", phoneCode: "49" },
                { name: "France", code: "FR", phoneCode: "33" },
                { name: "India", code: "IN", phoneCode: "91" },
                { name: "Japan", code: "JP", phoneCode: "81" },
                { name: "Brazil", code: "BR", phoneCode: "55" },
                { name: "South Africa", code: "ZA", phoneCode: "27" }
            ];

            // Populate country dropdown
            function populateCountryDropdown() {
                countryDropdown.innerHTML = '';
                countries.forEach(country => {
                    const option = document.createElement('div');
                    option.className = 'country-option';
                    option.textContent = country.name;
                    option.dataset.code = country.code;
                    option.dataset.phoneCode = country.phoneCode;
                    option.addEventListener('click', function() {
                        countryInput.value = country.name;
                        countryCodeInput.value = country.phoneCode;
                        phonePrefix.textContent = `+${country.phoneCode}`;
                        countryDropdown.style.display = 'none';
                    });
                    countryDropdown.appendChild(option);
                });
            }

            // Initialize country dropdown
            populateCountryDropdown();

            // Show country dropdown when clicking on country input
            countryInput.addEventListener('click', function() {
                countryDropdown.style.display = 'block';
            });

            // Hide country dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!countryInput.contains(e.target) && !countryDropdown.contains(e.target)) {
                    countryDropdown.style.display = 'none';
                }
            });

            // Policy links
            termsLink.addEventListener('click', function() {
                window.open('https://www.venusnap.com/terms/conditions', '_blank');
            });

            privacyLink.addEventListener('click', function() {
                window.open('https://www.venusnap.com/privacy/policy', '_blank');
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Reset error messages
                document.querySelectorAll('[id$="_error"]').forEach(el => {
                    el.classList.add('hidden');
                });

                // Get form values
                const fullName = document.getElementById('full_name').value;
                const email = document.getElementById('email').value;
                const country = document.getElementById('country').value;
                const phoneNumber = document.getElementById('phone_number').value;
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;
                const acceptTerms = document.getElementById('accept_terms').checked;
                const acceptPrivacy = document.getElementById('accept_privacy').checked;

                let isValid = true;

                // Validate full name
                if (!fullName) {
                    document.getElementById('full_name_error').textContent = 'Please enter your full name';
                    document.getElementById('full_name_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate email
                if (!email) {
                    document.getElementById('email_error').textContent = 'Please enter your email';
                    document.getElementById('email_error').classList.remove('hidden');
                    isValid = false;
                } else if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                    document.getElementById('email_error').textContent = 'Please enter a valid email';
                    document.getElementById('email_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate country
                if (!country) {
                    document.getElementById('country_error').textContent = 'Please select your country';
                    document.getElementById('country_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate phone number
                if (!phoneNumber) {
                    document.getElementById('phone_error').textContent = 'Please enter your phone number';
                    document.getElementById('phone_error').classList.remove('hidden');
                    isValid = false;
                } else if (!/^[0-9]{8,15}$/.test(phoneNumber)) {
                    document.getElementById('phone_error').textContent = 'Please enter a valid phone number';
                    document.getElementById('phone_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate password
                if (!password) {
                    document.getElementById('password_error').textContent = 'Please enter a password';
                    document.getElementById('password_error').classList.remove('hidden');
                    isValid = false;
                } else if (password.length < 8) {
                    document.getElementById('password_error').textContent = 'Password must be at least 8 characters';
                    document.getElementById('password_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate password confirmation
                if (password !== passwordConfirmation) {
                    document.getElementById('password_confirmation_error').textContent = 'Passwords do not match';
                    document.getElementById('password_confirmation_error').classList.remove('hidden');
                    isValid = false;
                }

                // Validate acceptance
                if (!acceptTerms || !acceptPrivacy) {
                    document.getElementById('acceptance_error').classList.remove('hidden');
                    isValid = false;
                }

                if (isValid) {
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.classList.add('btn-disabled');
                    buttonText.textContent = 'Creating account...';
                    buttonSpinner.classList.remove('hidden');

                    // In a real implementation, you would submit the form to your backend here
                    // For now, we'll simulate a successful registration after 2 seconds
                    setTimeout(function() {
                        alert('Registration successful!');
                        // In a real implementation, you would redirect to the login page
                        // window.location.href = "{{ route('login') }}";
                    }, 2000);
                }
            });
        });
    </script>
</body>
</html>
