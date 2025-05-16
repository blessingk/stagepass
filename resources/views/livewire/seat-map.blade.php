<div class="p-6">
    @if($errorMessage)
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            {{ $errorMessage }}
        </div>
    @endif

    @if($successMessage)
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ $successMessage }}
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-2">Select Your Seats</h2>
        <p class="text-gray-600">Click on available seats to select them. Selected seats will be reserved for 5 minutes.</p>
    </div>

    <div class="mb-8">
        <div class="w-full bg-gray-200 p-4 text-center mb-8 rounded">STAGE</div>

        <div class="grid gap-4">
            @foreach($seats as $row => $rowSeats)
                <div class="flex justify-center gap-2">
                    <div class="w-8 text-center text-gray-500 flex items-center">
                        Row {{ $row }}
                    </div>
                    @foreach($rowSeats as $seat)
                        <button
                            wire:click="toggleSeat({{ $seat->id }})"
                            @class([
                                'w-8 h-8 rounded transition-colors',
                                'bg-green-500 hover:bg-green-600 cursor-pointer' => $seat->isAvailable() && !in_array($seat->id, $selectedSeats),
                                'bg-blue-500 hover:bg-blue-600' => in_array($seat->id, $selectedSeats),
                                'bg-red-500 cursor-not-allowed' => $seat->isBooked(),
                                'bg-yellow-500 cursor-not-allowed' => $seat->isReserved(),
                            ])
                            @if(!$seat->isAvailable())
                                disabled
                            @endif
                        >
                            {{ $seat->column }}
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-between items-center">
        <div class="flex gap-4">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-sm">Available</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                <span class="text-sm">Selected</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span class="text-sm">Reserved</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-sm">Booked</span>
            </div>
        </div>

        <div class="text-right">
            <p class="text-lg mb-2">
                Selected: <span class="font-bold">{{ count($selectedSeats) }}</span> seats
            </p>
            <p class="text-xl mb-4">
                Total: <span class="font-bold">£{{ number_format(count($selectedSeats) * $event->price, 2) }}</span>
            </p>
            <button
                wire:click="reserveSeats"
                @class([
                    'px-6 py-2 rounded text-white transition-colors',
                    'bg-blue-500 hover:bg-blue-600' => !empty($selectedSeats),
                    'bg-gray-300 cursor-not-allowed' => empty($selectedSeats)
                ])
                @if(empty($selectedSeats))
                    disabled
                @endif
            >
                Reserve Selected Seats
            </button>
        </div>
    </div>

    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Complete Your Booking</h3>
                <p class="mb-4">Your seats have been reserved for 5 minutes. Please complete the payment to confirm your booking.</p>
                <div class="flex justify-end gap-4">
                    <button
                        wire:click="$set('showConfirmation', false)"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800"
                    >
                        Cancel
                    </button>
                    <a
                        href="{{ route('booking.payment', ['booking' => session('booking_id')]) }}"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Proceed to Payment
                    </a>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Refresh seat map every 5 seconds
        setInterval(() => {
            @this.emit('refreshSeats')
        }, 5000)
    </script>
</div> 