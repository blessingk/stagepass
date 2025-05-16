<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\User;
use App\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_users' => User::count(),
            'total_events' => Event::count(),
            'total_purchases' => Booking::count(),
            'published_events' => Event::where('status', 'published')->count(),
            'draft_events' => Event::where('status', 'draft')->count(),
            'cancelled_events' => Event::where('status', 'cancelled')->count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'regular_users' => User::where('is_admin', false)->count(),
        ];

        $latest_events = Event::latest()->take(5)->get();
        $latest_users = User::latest()->take(5)->get();
        $latest_purchases = Booking::with(['user', 'event'])->latest()->take(5)->get();

        return view('livewire.admin.dashboard', compact('stats', 'latest_events', 'latest_users', 'latest_purchases'));
    }
} 