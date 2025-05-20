<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class EventSeats extends Component
{
    public Event $event;
    public $seats = [];
    public $selectedSeat = null;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadSeats();
    }

    public function loadSeats()
    {
        $this->seats = $this->event->seats()
            ->with('bookings.user')
            ->get()
            ->groupBy('row')
            ->map(function ($row) {
                return $row->sortBy('column')->values();
            });
    }

    public function selectSeat($seatId)
    {
        $this->selectedSeat = $this->event->seats()->with('bookings.user')->find($seatId);
    }

    public function render()
    {
        return view('livewire.admin.event-seats', [
            'rows' => $this->seats,
            'selectedSeat' => $this->selectedSeat,
        ]);
    }
} 