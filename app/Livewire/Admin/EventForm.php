<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

#[Layout('components.layouts.admin')]
class EventForm extends Component
{
    public ?Event $event = null;

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
    public $status = 'draft';

    public function mount(?Event $event = null)
    {
        if ($event && $event->exists) {
            $this->event = $event;
            $this->name = $event->name;
            $this->description = $event->description;
            $this->date = $event->date->format('Y-m-d\TH:i');
            $this->venue = $event->venue;
            $this->rows = $event->rows;
            $this->columns = $event->columns;
            $this->status = $event->status;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        $isNew = !$this->event?->exists;

        $event = $this->event ?? new Event();
        $event->fill([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'venue' => $validated['venue'],
            'rows' => $validated['rows'],
            'columns' => $validated['columns'],
            'status' => $validated['status'],
        ]);
        $event->save();

        if ($isNew) {
            $event->generateSeatMap();
        }

        session()->flash('message', $isNew ? 'Event created successfully.' : 'Event updated successfully.');
        $this->redirect(route('admin.events.index'));
    }

    public function render()
    {
        return view('livewire.admin.event-form');
    }
}
