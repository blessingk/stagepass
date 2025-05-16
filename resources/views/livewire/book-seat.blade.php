<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">{{ $event->name }}</h2>
        <div class="text-sm text-gray-500">
            {{ $event->date->format('F j, Y g:i A') }}
        </div>
    </div>

    @if ($message)
        <div @class([
            'p-4 rounded-md',
            'bg-green-50 text-green-700' => $messageType === 'success',
            'bg-red-50 text-red-700' => $messageType === 'error'
        ])>
            {{ $message }}
        </div>
    @endif

    <div class="relative">
        <!-- Stage -->
        <div class="w-full h-12 bg-gray-800 rounded-lg mb-8 flex items-center justify-center">
            <span class="text-white">STAGE</span>
        </div>

        <!-- Seat Grid -->
        <div class="grid gap-4">
            @foreach($seats as $row => $rowSeats)
                <div class="flex items-center space-x-2">
                    <span class="w-8 text-center font-medium">{{ $row }}</span>
                    <div class="flex-1 grid grid-cols-{{ $event->columns }} gap-2">
                        @foreach($rowSeats as $seat)
                            <button
                                wire:click="selectSeat({{ $seat->id }})"
                                @class([
                                    'w-8 h-8 rounded transition-colors relative group',
                                    'bg-green-200 hover:bg-green-300 cursor-pointer' => $seat->status === 'available',
                                    'bg-red-200 cursor-not-allowed' => $seat->status === 'booked'
                                ])
                                @disabled($seat->status !== 'available')
                                title="Row {{ $seat->row }}, Seat {{ $seat->column }}"
                            >
                                {{ $seat->column }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center space-x-4 justify-center mt-6">
        <div class="flex items-center">
            <div class="w-6 h-6 bg-green-200 rounded mr-2"></div>
            <span>Available</span>
        </div>
        <div class="flex items-center">
            <div class="w-6 h-6 bg-red-200 rounded mr-2"></div>
            <span>Booked</span>
        </div>
    </div>

    <!-- Booking Confirmation Modal -->
    <x-modal name="confirm-booking" :show="false">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                Confirm Booking
            </h3>

            @if($selectedSeat)
                <p class="mb-4">
                    Are you sure you want to book seat Row {{ $selectedSeat->row }}, Seat {{ $selectedSeat->column }}?
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-button wire:click="$dispatch('close-modal', 'confirm-booking')">
                        Cancel
                    </x-button>
                    <x-button wire:click="book" wire:loading.attr="disabled" class="bg-blue-500 text-white">
                        <span wire:loading.remove>Confirm Booking</span>
                        <span wire:loading>Processing...</span>
                    </x-button>
                </div>
            @endif
        </div>
    </x-modal>
</div>
