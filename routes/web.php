<?php

use App\Http\Controllers\BookingController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\EventIndex;
use App\Livewire\Admin\EventSeats;
use App\Livewire\Admin\PurchaseIndex;
use App\Livewire\Admin\UserIndex;
use App\Livewire\BookSeat;
use App\Livewire\EventList;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Public routes
Route::get('/', EventList::class)->name('home');

// Auth required routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified', 'admin'])
        ->name('dashboard');

    // Settings routes
    Route::prefix('settings')
        ->name('settings.')
        ->group(function () {
            Route::redirect('/', 'settings/profile');
            Route::get('/profile', fn () => Volt::render('settings.profile'))->name('profile');
            Route::get('/password', fn () => Volt::render('settings.password'))->name('password');
            Route::get('/appearance', fn () => Volt::render('settings.appearance'))->name('appearance');
        });

    // Event booking routes
    Route::prefix('events')
        ->name('event.')
        ->group(function () {
            Route::get('/{event}', BookSeat::class)->name('show');
            Route::get('/{event}/book', BookSeat::class)->name('book');
            Route::post('/{event}/book', [BookingController::class, 'store'])->name('book.store');
            Route::post('/{event}/reserve', [BookingController::class, 'reserve'])->name('reserve');
            Route::post('/{event}/release', [BookingController::class, 'release'])->name('release');
        });

    Route::get('/booking/{booking}/payment', [BookingController::class, 'payment'])
        ->name('booking.payment');

    Route::post('/booking/{booking}/confirm', [BookingController::class, 'confirm'])
        ->name('booking.confirm');
});

// Admin routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        // Event management routes
        Route::prefix('events')
            ->name('events.')
            ->group(function () {
                Route::get('/', EventIndex::class)->name('index');
                Route::get('/{event}/seats', EventSeats::class)->name('seats');
            });

        // User management routes
        Route::get('/users', UserIndex::class)->name('users.index');

        // Purchase management routes
        Route::get('/purchases', PurchaseIndex::class)->name('purchases.index');
    });

// Authentication routes
require __DIR__.'/auth.php';


