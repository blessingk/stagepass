<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Seat;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;
    private Event $event;
    private User $user;
    private array $seats;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);

        // Create test event
        $this->event = Event::factory()->create([
            'rows' => 5,
            'columns' => 5,
            'price' => 100,
            'status' => Event::STATUS_PUBLISHED
        ]);

        // Create seats
        for ($row = 1; $row <= 5; $row++) {
            for ($col = 1; $col <= 5; $col++) {
                $this->seats[] = Seat::create([
                    'event_id' => $this->event->id,
                    'row' => $row,
                    'column' => $col,
                    'status' => Seat::STATUS_AVAILABLE
                ]);
            }
        }

        $this->user = User::factory()->create();
    }

    public function test_can_reserve_available_seats()
    {
        $seatId = $this->seats[0]->id;

        $booking = $this->bookingService->reserveSeats(
            $this->event,
            $seatId,
            $this->user
        );

        $this->assertNotNull($booking);
        $this->assertEquals($this->event->id, $booking->event_id);
        $this->assertEquals($this->user->id, $booking->user_id);
        $this->assertEquals(number_format(100, 2), $booking->total_amount);

        $seat = Seat::find($seatId);
        $this->assertEquals(Seat::STATUS_RESERVED, $seat->status);
        $this->assertNotNull($seat->reservation_expires_at);

    }

    public function test_cannot_reserve_already_reserved_seats()
    {
        $seatId = $this->seats[0]->id;

        // First reservation
        $this->bookingService->reserveSeats(
            $this->event,
            $seatId,
            $this->user
        );

        // Second reservation attempt
        $this->expectException(\Exception::class);
        $this->bookingService->reserveSeats(
            $this->event,
            $seatId,
            User::factory()->create()
        );
    }

    public function test_can_confirm_booking()
    {
        $seatId = $this->seats[0]->id;
        $booking = $this->bookingService->reserveSeats(
            $this->event,
            $seatId,
            $this->user
        );

        $confirmedBooking = $this->bookingService->confirmBooking(
            $booking,
            'test_payment',
            'test_123'
        );

        $this->assertEquals('completed', $confirmedBooking->payment_status);

        $seat = Seat::find($seatId);
        $this->assertEquals(Seat::STATUS_RESERVED, $seat->status);
        $this->assertNotNull($seat->reservation_expires_at);
    }

    public function test_expired_reservations_are_released()
    {
        $seatId  = $this->seats[0]->id;
        $booking = $this->bookingService->reserveSeats(
            $this->event,
            $seatId,
            $this->user
        );

        // Manually expire the reservation
        Seat::where('id', $seatId)->update([
            'reservation_expires_at' => now()->subMinutes(6)
        ]);

        $releasedCount = $this->bookingService->releaseExpiredReservations();

        $this->assertEquals(1, $releasedCount);

        $seat = Seat::find($seatId);
        $this->assertEquals(Seat::STATUS_AVAILABLE, $seat->status);
        $this->assertNull($seat->reservation_expires_at);

        $booking->refresh();
        $this->assertEquals(Booking::PAYMENT_STATUS_PENDING, $booking->payment_status);
    }
}
