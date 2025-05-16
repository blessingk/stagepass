<x-layouts.app>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-6">Complete Your Booking</h1>

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Booking Details</h2>
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="grid gap-4">
                                <div>
                                    <span class="font-medium">Event:</span>
                                    {{ $booking->event->name }}
                                </div>
                                <div>
                                    <span class="font-medium">Date:</span>
                                    {{ $booking->event->date->format('F j, Y g:i A') }}
                                </div>
                                <div>
                                    <span class="font-medium">Seats:</span>
                                    {{ $booking->seats->count() }} seats
                                    ({{ $booking->seats->pluck('row', 'column')->map(function($row, $col) {
                                        return "Row {$row}, Col {$col}";
                                    })->join(', ') }})
                                </div>
                                <div>
                                    <span class="font-medium">Total Amount:</span>
                                    £{{ number_format($booking->total_amount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
                        <form action="{{ route('booking.confirm', $booking) }}" method="POST" id="payment-form" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="cardholder_name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
                                    <input
                                        type="text"
                                        id="cardholder_name"
                                        name="cardholder_name"
                                        placeholder="John Doe"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                </div>

                                <div>
                                    <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                    <div class="relative mt-1">
                                        <input
                                            type="text"
                                            id="card_number"
                                            name="card_number"
                                            placeholder="1234 5678 9012 3456"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            maxlength="19"
                                            required
                                        >
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <label for="expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input
                                            type="text"
                                            id="expiry"
                                            name="expiry"
                                            placeholder="MM/YY"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            maxlength="5"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label for="cvc" class="block text-sm font-medium text-gray-700">CVC</label>
                                        <div class="relative mt-1">
                                            <input
                                                type="text"
                                                id="cvc"
                                                name="cvc"
                                                placeholder="123"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                maxlength="4"
                                                required
                                            >
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="payment_method" value="card">
                                <input type="hidden" name="payment_id" value="test_{{ uniqid() }}">

                                <div class="flex items-center justify-between mt-6">
                                    <a href="{{ route('event.show', $booking->event) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                        ← Back to Event
                                    </a>
                                    <button
                                        type="submit"
                                        id="submit-button"
                                        class="bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span class="flex items-center">
                                            <span>Pay £{{ number_format($booking->total_amount, 2) }}</span>
                                            <svg id="loading-spinner" class="hidden ml-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-8 space-y-4">
                        <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                            <svg class="h-6 w-6 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div class="text-sm text-gray-600">
                                <p class="font-medium text-gray-900">Secure Payment</p>
                                <p>Your payment information is encrypted and secure. We never store your full card details.</p>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500">
                            <p class="font-medium text-gray-900 mb-2">Test Mode</p>
                            <p>This is a test payment form. In a production environment, you would integrate with a real payment provider like Stripe or PayPal.</p>
                            <p class="mt-2">For testing, you can use these card numbers:</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>4242 4242 4242 4242 (Visa)</li>
                                <li>Any future expiry date</li>
                                <li>Any 3-digit CVC</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('payment-form');
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('expiry');
            const cvc = document.getElementById('cvc');
            const submitButton = document.getElementById('submit-button');
            const loadingSpinner = document.getElementById('loading-spinner');

            // Format card number with spaces
            cardNumber.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                let formattedValue = '';
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }
                e.target.value = formattedValue;
            });

            // Format expiry date
            expiry.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2);
                }
                e.target.value = value;
            });

            // Only allow numbers in CVC
            cvc.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                submitButton.disabled = true;
                loadingSpinner.classList.remove('hidden');
            });
        });
    </script>
    @endpush
</x-layouts.app> 