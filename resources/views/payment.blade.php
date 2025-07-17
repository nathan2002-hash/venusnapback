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
        /* Points Card Styles */
        .points-card {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            border-radius: 0;
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .points-card::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        .points-label {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }
        .points-value {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
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
        <!-- Points Card Header -->
        <div class="points-card">
            <div class="relative z-10">
                <div class="points-label">Hello, {{ Auth::user()->name }}</div>
                <div class="points-value">{{ number_format(Auth::user()->points) }}</div>
                <div class="points-label">POINTS AVAILABLE</div>
            </div>
        </div>

        <form id="payment-form">
            <div class="p-6">
                <!-- Points Input -->
                <div class="mb-6">
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-2">Enter points to purchase</label>
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
                <div class="mt-4 flex justify-center space-x-4">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" alt="Visa" class="h-8 object-contain">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" alt="Mastercard" class="h-8 object-contain">
                    <img src="https://www.americanexpress.com/content/dam/amex/us/merchant/supplies-uplift/product/images/4_Card_color_horizontal.png" alt="American Express" class="h-8 object-contain">
                </div>
                <div class="mt-2 text-center text-sm">
                    <a href="/payment-security" class="text-primary hover:underline inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        How your payments are handled
                    </a>
                </div>
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
            <a href="https://app.venusnap.com" class="w-full btn-primary text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2 inline-block text-center">
                Go to App
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
            const buttonText = document.getElementById('button-text');
            const buttonSpinner = document.getElementById('button-spinner');
            const form = document.getElementById('payment-form');
            const cardErrors = document.getElementById('card-errors');
            const successModal = document.getElementById('success-modal');

            // Points to price calculation (1000 points = $1)
            const POINTS_RATE = 0.001;

            function updatePrice() {
                const points = parseInt(pointsInput.value) || 0;
                const amount = (points * POINTS_RATE).toFixed(2);
                pointsDisplay.textContent = points.toLocaleString();
                totalAmount.textContent = `$${amount}`;
            }

            pointsInput.addEventListener('input', updatePrice);
            updatePrice();

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitButton.disabled = true;
                submitButton.classList.add('btn-disabled');
                buttonText.textContent = 'Processing...';
                buttonSpinner.classList.remove('hidden');
                cardErrors.textContent = '';

                const points = parseInt(pointsInput.value);
                const amount = (points * POINTS_RATE).toFixed(2);

                try {
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

                    if (!response.ok) throw new Error('Failed to create payment intent');

                    const { clientSecret, payment_id } = await response.json();
                    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                        payment_method: { card: cardElement }
                    });

                    if (error) throw error;

                    const verification = await fetch('/confirm-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ payment_intent_id: paymentIntent.id })
                    });

                    if (!verification.ok) throw new Error('Payment verification failed');

                    successModal.style.display = 'flex';
                    window.history.pushState({}, '', `/payment-success?payment_intent_id=${paymentIntent.id}`);

                } catch (error) {
                    console.error('Payment error:', error);
                    cardErrors.textContent = error.message || 'An error occurred during payment';
                    submitButton.disabled = false;
                    submitButton.classList.remove('btn-disabled');
                    buttonText.textContent = 'Pay Now';
                    buttonSpinner.classList.add('hidden');
                    if (error.message) cardErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
</body>
</html>
