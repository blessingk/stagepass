<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">{{ $event->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $event->date->format('F j, Y g:i A') }} at {{ $event->venue }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Price per seat</p>
                <p class="text-lg font-semibold">£{{ number_format($event->price, 2) }}</p>
            </div>
        </div>
    </div>

    @if ($message)
        <div @class([
            'p-4 rounded-md mb-6',
            'bg-green-50 text-green-700' => $messageType === 'success',
            'bg-red-50 text-red-700' => $messageType === 'error'
        ])>
            {{ $message }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Seat Map -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="relative">
                    <!-- Stage Area -->
                    <div class="mb-12">
                        <!-- Stage Platform -->
                        <div class="w-full h-16 bg-gray-800 rounded-lg flex items-center justify-center relative">
                            <span class="text-white font-medium text-lg">STAGE</span>
                            <!-- Stage Curtain Effect -->
                            <div class="absolute inset-0 bg-gradient-to-b from-gray-900 to-gray-800 opacity-50 rounded-lg"></div>
                        </div>
                        <!-- Stage Front -->
                        <div class="w-full h-4 bg-gray-700 rounded-b-lg -mt-2"></div>
                    </div>

                    <!-- Seat Grid -->
                    <div class="grid gap-6">
                        @php
                            $rowLabels = range('A', 'Z');
                            $currentRow = 0;
                            $columnsPerRow = [8, 10, 10, 8, 6, 8, 10, 10, 8]; // Define columns for each row
                        @endphp
                        @foreach($seats as $row => $rowSeats)
                            <div class="flex items-center space-x-4">
                                <!-- Row Label -->
                                <div class="w-12 flex items-center justify-center">
                                    <span class="text-lg font-semibold text-gray-700">{{ $rowLabels[$currentRow] }}</span>
                                </div>
                                <!-- Seats -->
                                <div class="flex-1 flex justify-center">
                                    <div class="grid gap-3" style="grid-template-columns: repeat({{ $columnsPerRow[$currentRow] ?? 8 }}, minmax(0, 1fr));">
                                        @foreach($rowSeats as $seat)
                                            @if($seat['column'] <= ($columnsPerRow[$currentRow] ?? 8))
                                                <button
                                                    wire:click="toggleSeat({{ $seat['id'] }})"
                                                    wire:key="seat-{{ $seat['id'] }}"
                                                    @class([
                                                        'w-12 h-12 rounded-lg transition-all relative group flex items-center justify-center',
                                                        'bg-green-200 hover:bg-green-300 cursor-pointer ring-2 ring-transparent hover:scale-105' => $seat['status'] === 'available' && !in_array($seat['id'], $selectedSeats),
                                                        'bg-green-400 ring-2 ring-green-500 scale-105' => in_array($seat['id'], $selectedSeats),
                                                        'bg-red-200 cursor-not-allowed' => $seat['status'] === 'booked',
                                                        'bg-yellow-200 cursor-not-allowed' => $seat['status'] === 'reserved',
                                                    ])
                                                    @disabled($seat['status'] !== 'available' && !in_array($seat['id'], $selectedSeats))
                                                    title="Row {{ $rowLabels[$currentRow] }}, Seat {{ $seat['column'] }}"
                                                >
                                                    <span class="text-sm font-medium">{{ $seat['column'] }}</span>
                                                    @if($seat['status'] !== 'available')
                                                        <div class="absolute hidden group-hover:block bg-gray-800 text-white text-xs p-2 rounded shadow-lg -top-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10">
                                                            {{ $seat['status'] === 'booked' ? 'Booked' : 'Reserved' }}
                                                        </div>
                                                    @endif
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @php $currentRow++; @endphp
                        @endforeach
                    </div>

                    <!-- Aisle Markers -->
                    <div class="flex justify-between mt-8 px-12">
                        <div class="text-center">
                            <div class="w-24 h-1 bg-gray-300 mx-auto"></div>
                            <span class="text-sm text-gray-500 mt-1">Aisle</span>
                        </div>
                        <div class="text-center">
                            <div class="w-24 h-1 bg-gray-300 mx-auto"></div>
                            <span class="text-sm text-gray-500 mt-1">Aisle</span>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center space-x-8 justify-center mt-8 pt-8 border-t">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-200 rounded-lg mr-2"></div>
                        <span class="text-sm font-medium">Available</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-400 rounded-lg ring-2 ring-green-500 mr-2"></div>
                        <span class="text-sm font-medium">Selected</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-yellow-200 rounded-lg mr-2"></div>
                        <span class="text-sm font-medium">Reserved</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-200 rounded-lg mr-2"></div>
                        <span class="text-sm font-medium">Booked</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Booking Summary -->
        <div class="lg:col-span-1">
            <div 
                x-data="{ sticky: false }"
                x-init="
                    window.addEventListener('scroll', () => {
                        sticky = window.scrollY > 100;
                    });
                "
                :class="{ 'lg:sticky lg:top-6': sticky }"
                class="bg-white rounded-lg shadow-sm p-6 transition-all duration-200"
            >
                <h3 class="text-lg font-medium text-gray-900 mb-4">Booking Summary</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-4 border-b">
                        <div>
                            <p class="text-sm text-gray-500">Selected Seats</p>
                            <p class="text-lg font-medium text-gray-900">
                                {{ count($selectedSeats) }} seat{{ count($selectedSeats) !== 1 ? 's' : '' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Amount</p>
                            <p class="text-2xl font-bold text-gray-900">£{{ number_format($totalAmount, 2) }}</p>
                        </div>
                    </div>

                    @if(!empty($selectedSeatsList))
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">Selected Seat Numbers:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedSeatsList as $seat)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $seat['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button
                        wire:click="reserveSeats"
                        wire:loading.attr="disabled"
                        @class([
                            'w-full rounded-lg px-4 py-3 text-center text-sm font-semibold text-white transition-colors mt-6',
                            'bg-indigo-600 hover:bg-indigo-700' => !empty($selectedSeats),
                            'bg-gray-300 cursor-not-allowed' => empty($selectedSeats)
                        ])
                        @disabled(empty($selectedSeats))
                    >
                        <span wire:loading.remove>
                            Reserve Selected Seats
                        </span>
                        <span wire:loading>
                            Processing...
                        </span>
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-4">
                        Seats will be reserved for 5 minutes after selection
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div>
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Seats Reserved!</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Your seats have been reserved for 5 minutes. Please complete your booking by proceeding to payment.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <a
                                href="{{ route('booking.payment', ['booking' => $bookingId]) }}"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2"
                            >
                                Proceed to Payment
                            </a>
                            <button
                                type="button"
                                wire:click="$set('showConfirmation', false)"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
