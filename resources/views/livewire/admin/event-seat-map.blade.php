<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">{{ $event->name }} - Seat Map</h2>
        <div class="text-sm text-gray-500">
            Total Seats: {{ $event->rows * $event->columns }}
        </div>
    </div>

    <div class="flex items-center space-x-4 mb-6">
        <div class="flex-1">
            <input
                wire:model.live="search"
                type="search"
                placeholder="Search seats or bookings..."
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            >
        </div>
        <select
            wire:model.live="filter"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
            <option value="all">All Seats</option>
            <option value="available">Available</option>
            <option value="reserved">Reserved</option>
            <option value="booked">Booked</option>
        </select>
    </div>

    <div class="flex items-center space-x-4 mb-6">
        <div class="flex items-center">
            <div class="w-6 h-6 bg-gray-200 rounded mr-2"></div>
            <span>Available</span>
        </div>
        <div class="flex items-center">
            <div class="w-6 h-6 bg-yellow-200 rounded mr-2"></div>
            <span>Reserved</span>
        </div>
        <div class="flex items-center">
            <div class="w-6 h-6 bg-red-200 rounded mr-2"></div>
            <span>Booked</span>
        </div>
    </div>

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
                                wire:click="showSeat({{ $seat->id }})"
                                @class([
                                    'w-8 h-8 rounded transition-colors relative group',
                                    'bg-gray-200 hover:bg-gray-300' => $seat->status === 'available',
                                    'bg-yellow-200 hover:bg-yellow-300' => $seat->status === 'reserved',
                                    'bg-red-200 hover:bg-red-300' => $seat->status === 'booked',
                                ])
                                title="Row {{ $seat->row }}, Seat {{ $seat->column }}"
                            >
                                {{ $seat->column }}
                                @if($seat->status !== 'available')
                                    <div class="absolute hidden group-hover:block bg-gray-800 text-white text-xs p-2 rounded shadow-lg -top-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10">
                                        {{ $seat->getLabel() }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Seat Details Modal -->
    @if($showSeatDetails && $selectedSeat)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Seat Details
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Location</p>
                                        <p class="font-medium">{{ $selectedSeat->getLabel() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="font-medium">{{ ucfirst($selectedSeat->status) }}</p>
                                    </div>
                                    @if($selectedSeat->status === 'reserved')
                                        <div>
                                            <p class="text-sm text-gray-500">Reservation Expires</p>
                                            <p class="font-medium">{{ $selectedSeat->reservation_expires_at->diffForHumans() }}</p>
                                        </div>
                                    @endif
                                    @if($selectedSeat->booking)
                                        <div>
                                            <p class="text-sm text-gray-500">Booked By</p>
                                            <p class="font-medium">{{ $selectedSeat->booking->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $selectedSeat->booking->user->email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Booking Status</p>
                                            <p class="font-medium">{{ ucfirst($selectedSeat->booking->status) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Payment Status</p>
                                            <p class="font-medium">{{ ucfirst($selectedSeat->booking->payment_status) }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if($selectedSeat->status !== 'available')
                            <button wire:click="releaseSeat" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Release Seat
                            </button>
                        @endif
                        <button wire:click="$set('showSeatDetails', false)" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
