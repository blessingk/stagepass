<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class EventIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $dateRange = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function deleteEvent(Event $event)
    {
        $event->delete();
        session()->flash('message', 'Event deleted successfully.');
    }

    public function render()
    {
        $events = Event::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('venue', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->dateRange, function ($query) {
                if ($this->dateRange === 'upcoming') {
                    $query->where('date', '>', now());
                } elseif ($this->dateRange === 'past') {
                    $query->where('date', '<', now());
                }
            })
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('livewire.admin.event-index', [
            'events' => $events
        ]);
    }
}
