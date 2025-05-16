<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold mb-2">{{ $event->name }}</h1>
                        <p class="text-gray-600 mb-4">{{ $event->description }}</p>
                        <div class="flex gap-4 text-sm text-gray-500">
                            <div>
                                <span class="font-medium">Date:</span>
                                {{ $event->date->format('F j, Y g:i A') }}
                            </div>
                            <div>
                                <span class="font-medium">Price:</span>
                                £{{ number_format($event->price, 2) }} per seat
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <livewire:seat-map :event="$event" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 