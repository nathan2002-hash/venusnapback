<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Points</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: white;
        }
        .StripeElement--focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 1px #7c3aed;
        }
        .StripeElement--invalid {
            border-color: #ef4444;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-[#7c3aed] p-6 text-white">
           <center>
                <h1 class="text-2xl font-bold">Purchase Points</h1>
                <p class="text-[#d8b4fe] mt-1">Enter the points you want to buy</p>
           </center>
        </div>

        <form id="payment-form"> <!-- Added form element -->
            <div class="p-6">
                <!-- Points Input -->
                <div class="mb-6">
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-2">Points Amount</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <input
                            type="number"
                            id="points"
                            min="1000"
                            step="1000"
                            value="1000"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-[#7c3aed] focus:border-[#7c3aed]"
                            placeholder="Enter points"
                            required
                        >
                    </div>
                </div>

                <!-- Price Calculation -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Points:</span>
                        <span class="font-medium" id="points-display">1,000</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Price per 1000 points:</span>
                        <span class="font-medium">$1.00</span>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total:</span>
                        <span class="text-primary" id="total-amount">$1.00</span>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Payment Details</h2>
                    <div id="card-element" class="mb-4"></div>
                    <div id="card-errors" role="alert" class="text-red-500 text-sm"></div>
                </div>

                <!-- Submit Button -->
                <button
                    id="submit-button"
                    class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 flex items-center justify-center"
                    type="submit"
                >
                    <span id="button-text">Pay Now</span>
                    <span id="button-spinner" class="spinner hidden"></span>
                </button>

                <!-- Security Badges -->
                <div class="mt-6 text-center text-gray-500 text-sm">
                    <p>Your payment is secure and encrypted.</p>
                    <p>We accept all major credit cards.</p>
                </div>
                <center>
                    <div id="amex-logo" class="mt-6 flex justify-center space-x-6" style="width: 230px; height: 50px;">
                        <img src="https://www.americanexpress.com/content/dam/amex/us/merchant/supplies-uplift/product/images/4_Card_color_horizontal.png" width="100%" height="100%" alt="American Express Accepted Here" border="0">
                    </div>
                </center>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
     <div id="success-modal" class="modal">
        <div class="modal-content">
            <div class="mb-4">
                <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Payment Successful!</h2>
            <p class="text-gray-600 mb-6">Your points have been added to your account.</p>
            <a href="https://app.venusnap.com" id="success-button" class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 inline-block text-center">
                <!-- Text will be set dynamically -->
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Stripe
            const stripe = Stripe('{{ $stripekey }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card');
            cardElement.mount('#card-element');

            // DOM elements
            const pointsInput = document.getElementById('points');
            const pointsDisplay = document.getElementById('points-display');
            const totalAmount = document.getElementById('total-amount');
            const submitButton = document.getElementById('submit-button');
            const form = document.getElementById('payment-form');
            const cardErrors = document.getElementById('card-errors');
            const successModal = document.getElementById('success-modal');

            // Points to price calculation (1000 points = $1)
            const POINTS_RATE = 0.001; // $0.001 per point (1000 points = $1)

            function updatePrice() {
                const points = parseInt(pointsInput.value) || 0;
                const amount = (points * POINTS_RATE).toFixed(2);

                pointsDisplay.textContent = points.toLocaleString();
                totalAmount.textContent = `$${amount}`;
            }

            function isMobileDevice() {
                return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            }

            // Set success button text based on device
            function setSuccessButtonText() {
                const successButton = document.getElementById('success-button');
                successButton.textContent = isMobileDevice() ? 'Go to App' : 'Open your app to see the points';
            }
            // Live price calculation
            pointsInput.addEventListener('input', updatePrice);
            updatePrice(); // Initialize

            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');
            // Form submission
            form.addEventListener('submit', async (e) => {
               e.preventDefault();

                // Disable button and show processing state
                submitButton.disabled = true;
                submitButton.classList.add('btn-disabled');
                buttonText.textContent = 'Processing...';
                buttonSpinner.classList.remove('hidden');
                cardErrors.textContent = '';

                const points = parseInt(pointsInput.value);
                const amount = (points * POINTS_RATE).toFixed(2);

                try {
                    // Create payment intent
                    const response = await fetch('/create-payment-intent', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            points: points,
                            amount: amount,
                            purpose: "Points Purchase",
                            description: `Purchase of ${points} points`
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to create payment intent');
                    }

                    const { clientSecret, payment_id } = await response.json();

                    // Confirm payment
                    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: 'Customer Name' // You might want to collect this
                            }
                        }
                    });

                    if (error) {
                        throw error;
                    }

                    // Verify payment with backend
                    const verification = await fetch('/confirm-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json' // Important to prevent redirects
                        },
                        //credentials: 'include',
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id
                        })
                    });

                    if (!verification.ok) {
                        throw new Error('Payment verification failed');
                    }

                    // Show success modal instead of redirecting
                    //successModal.style.display = 'flex';
                    function showSuccessModal() {
                        setSuccessButtonText();
                        successModal.style.display = 'flex';
                    }
                    // You can still keep the redirect in the URL if needed for analytics
                    window.history.pushState({}, '', `/payment-success?payment_intent_id=${paymentIntent.id}`);

                } catch (error) {
                    console.error('Payment error:', error);
                    cardErrors.textContent = error.message || 'An error occurred during payment';

                    // Re-enable button and reset state
                    submitButton.disabled = false;
                    submitButton.classList.remove('btn-disabled');
                    buttonText.textContent = 'Pay Now';
                    buttonSpinner.classList.add('hidden');

                    // Scroll to errors if they exist
                    if (error.message) {
                        cardErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
</body>
</html>
