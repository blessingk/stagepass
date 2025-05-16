<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Polling;

#[Layout('components.layouts.guest')]
class EventList extends Component
{
    public $events;
    public $selectedEvent = null;

    #[Polling(interval: 5000)]
    public function loadEvents()
    {
        $this->events = Event::where('status', 'published')
            ->where('date', '>', now())
            ->orderBy('date')
            ->with(['seats' => function ($query) {
                $query->select('id', 'event_id', 'row', 'column', 'status')
                    ->orderBy('row')
                    ->orderBy('column');
            }])
            ->get();
    }

    public function mount()
    {
        $this->loadEvents();
    }

    public function selectEvent($eventId)
    {
        $this->selectedEvent = $this->events->find($eventId);
    }

    public function render()
    {
        return view('livewire.event-list');
    }
} 