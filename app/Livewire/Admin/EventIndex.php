<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

#[Layout('components.layouts.admin')]
class EventIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $dateRange = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $editingEvent = null;

    #[Rule('required|min:3|max:255')]
    public $name = '';

    #[Rule('required|min:10')]
    public $description = '';

    #[Rule('required|date|after:today')]
    public $date = '';

    #[Rule('required|min:3|max:255')]
    public $venue = '';

    #[Rule('required|integer|min:1|max:100')]
    public $rows = 10;

    #[Rule('required|integer|min:1|max:100')]
    public $columns = 10;

    #[Rule('required|in:draft,published,cancelled')]
    public $eventStatus = 'draft';

    #[Rule('required|numeric|min:0|max:1000')]
    public $price = 0;

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

    public function create()
    {
        $this->reset(['name', 'description', 'date', 'venue', 'rows', 'columns', 'eventStatus', 'price', 'editingEvent']);
        $this->showFormModal = true;
    }

    public function edit(Event $event)
    {
        $this->editingEvent = $event;
        $this->name = $event->name;
        $this->description = $event->description;
        $this->date = $event->date->format('Y-m-d\TH:i');
        $this->venue = $event->venue;
        $this->rows = $event->rows;
        $this->columns = $event->columns;
        $this->eventStatus = $event->status;
        $this->price = $event->price;
        $this->showFormModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        $isNew = !$this->editingEvent?->exists;

        $event = $this->editingEvent ?? new Event();
        $event->fill([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'venue' => $validated['venue'],
            'rows' => $validated['rows'],
            'columns' => $validated['columns'],
            'status' => $validated['eventStatus'],
            'price' => $validated['price'],
        ]);
        $event->save();

        if ($isNew) {
            $event->generateSeatMap();
        }

        $this->showFormModal = false;
        session()->flash('message', $isNew ? 'Event created successfully.' : 'Event updated successfully.');
    }

    public function confirmDelete(Event $event)
    {
        $this->editingEvent = $event;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->editingEvent) {
            $this->editingEvent->delete();
            session()->flash('message', 'Event deleted successfully.');
        }
        $this->showDeleteModal = false;
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
