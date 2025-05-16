<?php

use App\Models\Event;
use App\Models\Seat;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->addDays(7),
            'venue' => 'Test Venue',
            'rows' => 5,
            'columns' => 5,
            'status' => 'published'
        ]);
    }

    public function test_event_can_be_created()
    {
        $this->assertDatabaseHas('events', [
            'name' => 'Test Event',
            'description' => 'Test Description',
            'venue' => 'Test Venue',
            'rows' => 5,
            'columns' => 5,
            'status' => 'published'
        ]);
    }

    public function test_event_has_correct_relationships()
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->event->seats);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->event->bookings);
    }

    public function test_event_can_generate_seat_map()
    {
        $this->event->generateSeatMap();
        
        $totalSeats = $this->event->rows * $this->event->columns;
        $this->assertEquals($totalSeats, $this->event->seats()->count());

        // Check if all seats are properly created
        $this->assertEquals($totalSeats, $this->event->seats()
            ->where('status', 'available')
            ->count());

        // Verify seat positions
        for ($row = 1; $row <= $this->event->rows; $row++) {
            for ($col = 1; $col <= $this->event->columns; $col++) {
                $this->assertDatabaseHas('seats', [
                    'event_id' => $this->event->id,
                    'row' => $row,
                    'column' => $col,
                    'status' => 'available'
                ]);
            }
        }
    }

    public function test_event_can_get_available_seats()
    {
        $this->event->generateSeatMap();
        
        // Initially all seats should be available
        $this->assertEquals(
            $this->event->rows * $this->event->columns,
            $this->event->availableSeats()->count()
        );

        // Mark one seat as booked
        $seat = $this->event->seats()->first();
        $seat->update(['status' => 'booked']);

        // Available seats count should decrease
        $this->assertEquals(
            ($this->event->rows * $this->event->columns) - 1,
            $this->event->availableSeats()->count()
        );
    }

    public function test_event_date_is_cast_to_datetime()
    {
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->event->date);
    }

    public function test_event_soft_delete()
    {
        $eventId = $this->event->id;
        
        $this->event->delete();
        
        // Event should not be found in normal queries
        $this->assertNull(Event::find($eventId));
        
        // Event should be found in trashed queries
        $this->assertNotNull(Event::withTrashed()->find($eventId));
    }

    public function test_cascading_delete_to_seats()
    {
        $this->event->generateSeatMap();
        $seatIds = $this->event->seats()->pluck('id');
        
        $this->event->delete();
        
        // All associated seats should be soft deleted
        foreach ($seatIds as $seatId) {
            $this->assertSoftDeleted('seats', ['id' => $seatId]);
        }
    }
} 