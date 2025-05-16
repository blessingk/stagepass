<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
            Upcoming Events
        </h2>
        <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
            Select an event to view available seats and book your tickets
        </p>
    </div>

    <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        @foreach($events as $event)
            <div @class([
                'bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-200',
                'ring-2 ring-blue-500' => $selectedEvent?->id === $event->id
            ])>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900">{{ $event->name }}</h3>
                    <p class="mt-2 text-gray-500">{{ $event->description }}</p>
                    <div class="mt-4 text-sm text-gray-600">
                        <p>{{ $event->date->format('F j, Y g:i A') }}</p>
                        <p>{{ $event->venue }}</p>
                    </div>
                    <button
                        wire:click="selectEvent({{ $event->id }})"
                        class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                        View Seats
                    </button>
                </div>

                @if($selectedEvent?->id === $event->id)
                    <div class="p-6 bg-gray-50 border-t">
                        <div class="text-sm font-medium text-gray-500 mb-4">
                            Seat Map
                        </div>
                        
                        <!-- Stage -->
                        <div class="w-full h-8 bg-gray-800 rounded mb-4 flex items-center justify-center">
                            <span class="text-white text-xs">STAGE</span>
                        </div>

                        <!-- Seat Grid -->
                        <div class="grid gap-2">
                            @foreach($event->seats->groupBy('row') as $row => $rowSeats)
                                <div class="flex items-center space-x-1">
                                    <span class="w-4 text-center text-xs font-medium">{{ $row }}</span>
                                    <div class="flex-1 grid grid-cols-{{ $event->columns }} gap-1">
                                        @foreach($rowSeats as $seat)
                                            <div
                                                @class([
                                                    'w-4 h-4 rounded-sm',
                                                    'bg-green-200' => $seat->status === 'available',
                                                    'bg-red-200' => $seat->status === 'booked'
                                                ])
                                                title="Row {{ $seat->row }}, Seat {{ $seat->column }}"
                                            ></div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <div class="flex items-center space-x-4 justify-center mt-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-200 rounded-sm mr-2"></div>
                                <span class="text-xs">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-200 rounded-sm mr-2"></div>
                                <span class="text-xs">Booked</span>
                            </div>
                        </div>

                        <a
                            href="{{ route('event.book', $event) }}"
                            class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                        >
                            Book Tickets
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if($events->isEmpty())
        <div class="text-center mt-12">
            <p class="text-gray-500">No upcoming events available.</p>
        </div>
    @endif
</div> 