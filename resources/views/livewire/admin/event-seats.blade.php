<div>
    <div class="mb-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">{{ $event->name }} - Seat Map</h1>
                <p class="mt-2 text-sm text-gray-700">
                    {{ $event->venue }} - {{ $event->date->format('M d, Y H:i') }}
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('admin.events.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-gray-500">
                    Back to Events
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Seat Map -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-6">
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-gray-100 rounded mr-2"></div>
                            <span>Available</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-100 rounded mr-2"></div>
                            <span>Booked</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-yellow-100 rounded mr-2"></div>
                            <span>Reserved</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2" style="grid-template-columns: repeat({{ $event->columns }}, minmax(0, 1fr));">
                    @foreach($rows as $rowNumber => $row)
                        @foreach($row as $seat)
                            <button
                                wire:click="selectSeat({{ $seat->id }})"
                                @class([
                                    'aspect-square rounded-md text-xs font-medium flex items-center justify-center transition-colors',
                                    'bg-gray-100 hover:bg-gray-200 text-gray-900' => !$seat->booking,
                                    'bg-green-100 hover:bg-green-200 text-green-900' => $seat->booking?->status === 'confirmed',
                                    'bg-yellow-100 hover:bg-yellow-200 text-yellow-900' => $seat->booking?->status === 'reserved',
                                    'ring-2 ring-indigo-500' => $selectedSeat?->id === $seat->id,
                                ])
                            >
                                {{ $rowNumber }}-{{ $seat->column }}
                            </button>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Seat Details -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Seat Details</h3>
                @if($selectedSeat)
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <div class="mt-1 text-sm text-gray-900">Row {{ $selectedSeat->row }}, Seat {{ $selectedSeat->column }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-1">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-gray-100 text-gray-800' => !$selectedSeat->booking,
                                    'bg-green-100 text-green-800' => $selectedSeat->booking?->status === 'confirmed',
                                    'bg-yellow-100 text-yellow-800' => $selectedSeat->booking?->status === 'reserved',
                                ])>
                                    {{ $selectedSeat->booking ? ucfirst($selectedSeat->booking->status) : 'Available' }}
                                </span>
                            </div>
                        </div>

                        @if($selectedSeat->booking)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Booked By</label>
                                <div class="mt-1 text-sm text-gray-900">{{ $selectedSeat->booking->user->name }}</div>
                                <div class="mt-0.5 text-xs text-gray-500">{{ $selectedSeat->booking->user->email }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Booking Date</label>
                                <div class="mt-1 text-sm text-gray-900">{{ $selectedSeat->booking->created_at->format('M d, Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500">Select a seat to view details</p>
                @endif
            </div>

            <!-- Event Summary -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Summary</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Seats</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $event->rows * $event->columns }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Booked Seats</label>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $event->seats()->whereHas('booking', function($query) {
                                $query->where('status', 'confirmed');
                            })->count() }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reserved Seats</label>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $event->seats()->whereHas('booking', function($query) {
                                $query->where('status', 'reserved');
                            })->count() }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Available Seats</label>
                        <div class="mt-1 text-sm text-gray-900">
                            {{ $event->seats()->whereDoesntHave('booking')->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 