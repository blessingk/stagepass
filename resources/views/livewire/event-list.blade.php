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
                        <!-- Book Tickets Button in its own row -->
                        <div class="flex justify-center mb-6">
                            <a
                                href="{{ route('event.book', $event) }}"
                                class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors"
                            >
                                Book Tickets
                            </a>
                        </div>
                        <!-- Legend at the top -->
                        <div class="flex items-center justify-center space-x-6 mb-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-200 rounded mr-1.5"></div>
                                <span class="text-xs font-medium">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-yellow-200 rounded mr-1.5"></div>
                                <span class="text-xs font-medium">Reserved</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-200 rounded mr-1.5"></div>
                                <span class="text-xs font-medium">Booked</span>
                            </div>
                        </div>

                        <!-- Event Status -->
                        <div class="mb-6 text-center">
                            <span @class([
                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                'bg-green-100 text-green-800' => $event->status === 'published',
                                'bg-yellow-100 text-yellow-800' => $event->status === 'draft',
                                'bg-red-100 text-red-800' => $event->status === 'cancelled'
                            ])>
                            </span>
                        </div>

                        <div class="text-sm font-medium text-gray-500 mb-4">
                            Seat Map
                        </div>

                        <!-- Seat Grid -->
                        <div class="grid gap-3">
                            @php
                                $rowLabels = range('A', 'Z');
                                $currentRow = 0;
                                $columnsPerRow = [8, 10, 10, 8, 6, 8, 10, 10, 8]; // Match the booking page configuration
                            @endphp
                            @foreach($event->seats->groupBy('row') as $row => $rowSeats)
                                <div class="flex items-center space-x-2">
                                    <!-- Row Label -->
                                    <div class="w-8 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">{{ $rowLabels[$currentRow] }}</span>
                                    </div>
                                    <!-- Seats -->
                                    <div class="flex-1 flex justify-center">
                                        <div class="grid gap-2" style="grid-template-columns: repeat({{ $columnsPerRow[$currentRow] ?? 8 }}, minmax(0, 1fr));">
                                            @foreach($rowSeats as $seat)
                                                @if($seat->column <= ($columnsPerRow[$currentRow] ?? 8))
                                                    <div
                                                        @class([
                                                            'w-6 h-6 rounded transition-colors relative group',
                                                            'bg-green-200' => $seat->status === 'available',
                                                            'bg-red-200' => $seat->status === 'booked',
                                                            'bg-yellow-200' => $seat->status === 'reserved',
                                                        ])
                                                        title="Row {{ $rowLabels[$currentRow] }}, Seat {{ $seat->column }}"
                                                    >
                                                        @if($seat->status !== 'available')
                                                            <div class="absolute hidden group-hover:block bg-gray-800 text-white text-xs p-1.5 rounded shadow-lg -top-6 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10">
                                                                {{ $seat->status === 'booked' ? 'Booked' : 'Reserved' }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @php $currentRow++; @endphp
                            @endforeach
                        </div>
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
