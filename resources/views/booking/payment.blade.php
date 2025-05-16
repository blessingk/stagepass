<x-app-layout>
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
                        <form action="{{ route('booking.confirm', $booking) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Card Number</label>
                                    <input
                                        type="text"
                                        name="card_number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input
                                            type="text"
                                            name="expiry"
                                            placeholder="MM/YY"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">CVC</label>
                                        <input
                                            type="text"
                                            name="cvc"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                    </div>
                                </div>

                                <input type="hidden" name="payment_method" value="card">
                                <input type="hidden" name="payment_id" value="test_{{ uniqid() }}">

                                <div class="mt-6">
                                    <button
                                        type="submit"
                                        class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        Pay £{{ number_format($booking->total_amount, 2) }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="text-sm text-gray-500">
                        <p>Note: This is a test payment form. In a production environment, you would integrate with a real payment provider.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 