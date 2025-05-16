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
    public array $selectedSeats = [];
    public array $selectedSeatDetails = [];
    public $message = '';
    public $messageType = '';
    public $loading = false;
    public $showConfirmation = false;
    public $bookingId = null;
    protected $seats = [];

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadSeats();
    }

    #[Polling(interval: 5000)] // Poll every 5 seconds
    public function loadSeats()
    {
        try {
            $seats = $this->event->seats()
                ->select('id', 'row', 'column', 'status', 'reservation_expires_at')
                ->orderBy('row')
                ->orderBy('column')
                ->get();

            $this->seats = $seats->groupBy('row')->toArray();
        } catch (\Exception $e) {
            $this->seats = [];
            $this->message = 'Error loading seats. Please try again.';
            $this->messageType = 'error';
        }
    }

    public function getSeatsProperty()
    {
        return collect($this->seats ?? []);
    }

    public function toggleSeat($seatId)
    {
        if (in_array($seatId, $this->selectedSeats)) {
            $this->selectedSeats = array_diff($this->selectedSeats, [$seatId]);
            unset($this->selectedSeatDetails[$seatId]);
        } else {
            $seat = $this->event->seats()->find($seatId);
            if ($seat && $seat->isAvailable()) {
                $this->selectedSeats[] = $seatId;
                $this->selectedSeatDetails[$seatId] = [
                    'row' => $seat->row,
                    'column' => $seat->column
                ];
            }
        }
    }

    public function getSelectedSeatsListProperty()
    {
        return collect($this->selectedSeatDetails)->map(function ($details, $id) {
            return [
                'id' => $id,
                'label' => "Row {$details['row']}, Seat {$details['column']}"
            ];
        })->values();
    }

    public function getTotalAmountProperty()
    {
        return count($this->selectedSeats) * $this->event->price;
    }

    public function reserveSeats()
    {
        if (empty($this->selectedSeats)) {
            $this->addError('seats', 'Please select at least one seat.');
            return;
        }

        $this->loading = true;

        try {
            DB::transaction(function () {
                // Check if all seats are still available
                $seats = Seat::whereIn('id', $this->selectedSeats)
                    ->where('status', Seat::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() !== count($this->selectedSeats)) {
                    throw new \Exception('One or more selected seats are no longer available.');
                }

                // Create booking
                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'event_id' => $this->event->id,
                    'status' => Booking::STATUS_RESERVED,
                    'total_amount' => $this->totalAmount,
                    'payment_status' => Booking::PAYMENT_STATUS_PENDING,
                ]);

                // Reserve seats
                foreach ($seats as $seat) {
                    $seat->update([
                        'status' => Seat::STATUS_RESERVED,
                        'reservation_expires_at' => now()->addMinutes(5),
                        'booking_id' => $booking->id
                    ]);
                }

                $this->bookingId = $booking->id;
                $this->showConfirmation = true;
                $this->message = 'Seats reserved successfully! Please complete your booking within 5 minutes.';
                $this->messageType = 'success';
            });
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'error';
        }

        $this->loading = false;
        $this->loadSeats();
    }

    public function render()
    {
        if (empty($this->seats)) {
            $this->loadSeats();
        }

        return view('livewire.book-seat', [
            'totalAmount' => $this->totalAmount,
            'selectedSeatsList' => $this->selectedSeatsList,
            'seats' => $this->seats
        ]);
    }
}
