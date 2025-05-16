<div>
    {{-- The Master doesn't talk, he acts. --}}
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">{{ $user?->exists ? 'Edit User' : 'Create User' }}</h2>
        <form wire:submit.prevent="save">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" id="name" wire:model.live="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" id="email" wire:model.live="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password {{ $user ? '(leave blank to keep current)' : '*' }}</label>
                <input type="password" id="password" wire:model.live="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="password_confirmation" wire:model.live="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4 flex items-center">
                <input type="checkbox" id="is_admin" wire:model.live="is_admin" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <label for="is_admin" class="ml-2 block text-sm text-gray-700">Admin</label>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">{{ $user?->exists ? 'Update' : 'Create' }}</button>
            </div>
        </form>
    </div>
</div>
