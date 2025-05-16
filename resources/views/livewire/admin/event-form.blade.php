<div>
    <div class="space-y-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $event?->exists ? 'Edit Event' : 'Create Event' }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Fill in the details for the event. All fields marked with * are required.
                    </p>
                </div>
            </div>

            <div class="mt-5 md:col-span-2 md:mt-0">
                <form wire:submit.prevent="save">
                    <div class="shadow sm:overflow-hidden sm:rounded-md">
                        <div class="space-y-6 bg-white px-4 py-5 sm:p-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="name" id="name"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                                <div class="mt-1">
                                    <textarea wire:model.live="description" id="description" rows="3"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date and Time *</label>
                                <div class="mt-1">
                                    <input type="datetime-local" wire:model.live="date" id="date"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Venue -->
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700">Venue *</label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="venue" id="venue"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('venue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Seat Configuration -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="rows" class="block text-sm font-medium text-gray-700">Number of Rows *</label>
                                    <div class="mt-1">
                                        <input type="number" wire:model.live="rows" id="rows"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    @error('rows') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="columns" class="block text-sm font-medium text-gray-700">Seats per Row *</label>
                                    <div class="mt-1">
                                        <input type="number" wire:model.live="columns" id="columns"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    @error('columns') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <div class="mt-1">
                                    <select wire:model.live="status" id="status"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                            <a href="{{ route('admin.events.index') }}"
                                class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Cancel
                            </a>
                            <button type="submit"
                                class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ $event?->exists ? 'Update Event' : 'Create Event' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($event?->exists)
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Seat Map Preview</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Current configuration: {{ $rows }} rows × {{ $columns }} seats per row = {{ $rows * $columns }} total seats
                </p>
                <div class="grid gap-2" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
                    @for($row = 1; $row <= $rows; $row++)
                        @for($col = 1; $col <= $columns; $col++)
                            <div class="w-full aspect-square bg-gray-200 rounded flex items-center justify-center text-xs">
                                {{ $row }}-{{ $col }}
                            </div>
                        @endfor
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>
