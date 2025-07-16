@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Buy Points</h1>
        <p class="text-gray-600 mt-2">Upgrade your experience with premium points</p>
    </div>

    <!-- Current Points -->
    <div class="bg-blue-50 rounded-lg p-4 mb-6 flex items-center">
        <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
        </svg>
        <span class="font-medium">Your current points: <span class="text-blue-600">{{ $userPoints }}</span></span>
    </div>

    <!-- Package Selector -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Choose a package</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($packages as $package)
    <div class="package-option relative">
        <input
            type="radio"
            name="package"
            id="package-{{ $package['id'] }}"
            value="{{ $package['id'] }}"
            class="hidden peer"
            data-points="{{ $package['points'] }}"
            data-price="{{ $package['price'] }}"
            {{ $loop->first ? 'checked' : '' }}
        >
        <label
            for="package-{{ $package['id'] }}"
            class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50"
        >
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-lg">{{ $package['points'] }} points</h3>
                    <p class="text-gray-600 text-sm">Most popular</p>
                </div>
                <span class="text-blue-600 font-bold">${{ number_format($package['price'], 2) }}</span>
            </div>
            <div class="mt-3 flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm text-gray-600">Bonus: {{ $package['bonus'] ?? 0 }} extra points</span>
            </div>
        </label>
        <div class="absolute top-2 right-2 hidden peer-checked:block">
            <div class="bg-blue-500 text-white rounded-full p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>
    @endforeach

            <!-- Custom Amount Option -->
            <div class="package-option">
                <input
                    type="radio"
                    name="package"
                    id="package-custom"
                    value="custom"
                    class="hidden peer"
                >
                <label
                    for="package-custom"
                    class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50 h-full"
                >
                    <div class="flex flex-col h-full">
                        <div class="flex-grow">
                            <h3 class="font-bold text-lg">Custom amount</h3>
                            <div class="mt-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">$</span>
                                    <input
                                        type="number"
                                        id="custom-amount"
                                        min="5"
                                        step="1"
                                        placeholder="Enter amount"
                                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                                        disabled
                                    >
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum $5</p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600" id="custom-points-estimate">≈ 0 points</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold mb-6 text-gray-700">Payment Details</h2>

        <!-- Stripe Elements Container -->
        <div id="card-element" class="mb-4"></div>
        <div id="card-errors" class="text-red-500 text-sm mb-4"></div>

        <!-- Summary -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Selected package:</span>
                <span class="font-medium" id="selected-package-name">1000 points</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Price:</span>
                <span class="font-medium" id="selected-package-price">$10.00</span>
            </div>
            <div class="border-t border-gray-200 my-2"></div>
            <div class="flex justify-between font-bold text-lg">
                <span>Total:</span>
                <span id="total-amount">$10.00</span>
            </div>
        </div>

        <!-- Submit Button -->
        <button
            id="submit-button"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Pay Now
        </button>

        <!-- Security Badges -->
        <div class="mt-6 flex justify-center space-x-6">
            <img src="https://stripe.com/img/v3/payments/overview/photos/payment-method-visa.svg" alt="Visa" class="h-8">
            <img src="https://stripe.com/img/v3/payments/overview/photos/payment-method-mastercard.svg" alt="Mastercard" class="h-8">
            <img src="https://stripe.com/img/v3/payments/overview/photos/payment-method-amex.svg" alt="Amex" class="h-8">
        </div>
    </div>
</div>

<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripe = Stripe('{{ env("STRIPE_KEY") }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                '::placeholder': {
                    color: '#aab7c4'
                }
            }
        }
    });
    cardElement.mount('#card-element');

    // Package Selection Logic
    const packageInputs = document.querySelectorAll('input[name="package"]');
    const customAmountInput = document.getElementById('custom-amount');
    const customPointsEstimate = document.getElementById('custom-points-estimate');
    const packageNameDisplay = document.getElementById('selected-package-name');
    const packagePriceDisplay = document.getElementById('selected-package-price');
    const totalAmountDisplay = document.getElementById('total-amount');

    // Points conversion rate (100 points per $1)
    const POINTS_RATE = 100;

    function updateSummary(points, price) {
        packageNameDisplay.textContent = `${points} points`;
        packagePriceDisplay.textContent = `$${price.toFixed(2)}`;
        totalAmountDisplay.textContent = `$${price.toFixed(2)}`;
    }

    // Handle package selection changes
    packageInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'custom') {
                customAmountInput.disabled = false;
                customAmountInput.focus();
                // Calculate points based on custom amount
                customAmountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    const points = Math.floor(amount * POINTS_RATE);
                    customPointsEstimate.textContent = `≈ ${points} points`;
                    updateSummary(points, amount);
                });
            } else {
                customAmountInput.disabled = true;
                customAmountInput.value = '';
                const points = parseInt(this.dataset.points);
                const price = parseFloat(this.dataset.price);
                updateSummary(points, price);
            }
        });
    });

    // Initialize with first package selected
    const firstPackage = document.querySelector('input[name="package"]:checked');
    updateSummary(
        parseInt(firstPackage.dataset.points),
        parseFloat(firstPackage.dataset.price)
    );

    // Handle form submission
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitButton.disabled = true;

        // Get selected package or custom amount
        const selectedPackage = document.querySelector('input[name="package"]:checked');
        let points, amount;

        if (selectedPackage.value === 'custom') {
            amount = parseFloat(customAmountInput.value);
            points = Math.floor(amount * POINTS_RATE);
        } else {
            points = parseInt(selectedPackage.dataset.points);
            amount = parseFloat(selectedPackage.dataset.price);
        }

        // Create payment intent
        const { clientSecret } = await fetch('/create-payment-intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                points: points,
                amount: amount,
                user_id: {{ request()->query('user_id') }}
            })
        }).then(res => res.json());

        // Confirm payment
        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: { card: cardElement }
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
        } else {
            window.location.href = `/payment-success?payment_intent_id=${paymentIntent.id}`;
        }
    });
</script>

<style>
    .package-option {
        position: relative;
        transition: all 0.2s;
    }

    .package-option label {
        display: block;
        transition: all 0.2s;
    }

    .package-option input:checked + label {
        border-color: #3B82F6;
        box-shadow: 0 0 0 1px #3B82F6;
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
        border-color: #3B82F6;
        box-shadow: 0 0 0 1px #3B82F6;
    }

    .StripeElement--invalid {
        border-color: #ef4444;
    }
</style>
@endsection
