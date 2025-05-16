<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Polling;

class BookSeat extends Component
{
    public Event $event;
    public ?Seat $selectedSeat = null;
    public $seats = [];
    public $message = '';
    public $messageType = '';
    public $loading = false;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadSeats();
    }

    #[Polling(interval: 5000)] // Poll every 5 seconds
    public function loadSeats()
    {
        $this->seats = $this->event->seats()
            ->select('id', 'row', 'column', 'status')
            ->orderBy('row')
            ->orderBy('column')
            ->get()
            ->groupBy('row');
    }

    public function selectSeat($seatId)
    {
        $this->selectedSeat = Seat::find($seatId);
        $this->dispatch('open-modal', 'confirm-booking');
    }

    public function book()
    {
        if (!$this->selectedSeat) {
            $this->addError('seat', 'Please select a seat first.');
            return;
        }

        $this->loading = true;

        try {
            DB::transaction(function () {
                $seat = Seat::where('id', $this->selectedSeat->id)
                    ->where('status', Seat::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    throw new \Exception('This seat is no longer available.');
                }

                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'event_id' => $this->event->id,
                    'seat_id' => $seat->id,
                    'status' => Booking::STATUS_CONFIRMED,
                    'total_amount' => 100.00,
                    'payment_status' => Booking::PAYMENT_STATUS_PAID,
                    'payment_method' => 'credit_card'
                ]);

                $seat->update([
                    'status' => Seat::STATUS_BOOKED
                ]);

                $this->message = 'Seat booked successfully!';
                $this->messageType = 'success';
            });
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'error';
        }

        $this->loading = false;
        $this->loadSeats();
        $this->dispatch('close-modal', 'confirm-booking');
    }

    public function render()
    {
        return view('livewire.book-seat');
    }
}
