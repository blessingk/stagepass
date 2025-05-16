<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Polling;

class EventSeatMap extends Component
{
    public Event $event;
    public $seats = [];
    public $search = '';
    public $filter = 'all';

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadSeats();
    }

    #[Polling(interval: 5000)] // Poll every 5 seconds
    public function loadSeats()
    {
        $query = $this->event->seats()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereRaw("CONCAT('Row ', row, ', Seat ', column) LIKE ?", ['%' . $this->search . '%']);
                });
            })
            ->when($this->filter !== 'all', function ($query) {
                $query->where('status', $this->filter);
            })
            ->orderBy('row')
            ->orderBy('column');

        $this->seats = $query->get()->groupBy('row');
    }

    public function showSeat($seatId)
    {
        $this->dispatch('open-modal', 'seat-details');
        $this->selectedSeat = $this->event->seats()->findOrFail($seatId);
    }

    public function render()
    {
        return view('livewire.admin.event-seat-map');
    }
}
