<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SimulateSeatBooking extends Command
{
    protected $signature = 'booking:simulate {seatId} {--users=100} {--delay=0}';
    protected $description = 'Simulate many concurrent bookings for the same seat';

    public function handle()
    {
        $seatId = $this->argument('seatId');
        $users = (int) $this->option('users');
        $delay = (int) $this->option('delay');

        $seat = Seat::findOrFail($seatId);
        $this->info("Simulating {$users} concurrent booking attempts for seat {$seat->getLabel()} in event '{$seat->event->name}'");

        if ($delay > 0) {
            $this->info("Adding {$delay}ms delay between attempts for visibility");
        }

        $processes = [];
        $successCount = 0;
        $failCount = 0;
        $startTime = microtime(true);

        // Create a pool of test users if they don't exist
        $this->createTestUsers($users);

        // Simulate concurrent requests using parallel processes
        for ($i = 1; $i <= $users; $i++) {
            $userId = $i;

            try {
                DB::transaction(function () use ($seatId, $userId, $seat) {
                    // Attempt to reserve the seat first
                    $seat = Seat::where('id', $seatId)
                        ->where(function ($query) {
                            $query->where('status', Seat::STATUS_AVAILABLE)
                                ->orWhere(function ($q) {
                                    $q->where('status', Seat::STATUS_RESERVED)
                                        ->where('reservation_expires_at', '<', now());
                                });
                        })
                        ->lockForUpdate()
                        ->first();

                    if (!$seat) {
                        throw new \Exception('Seat is not available');
                    }

                    // Create booking
                    $booking = Booking::create([
                        'user_id' => $userId,
                        'event_id' => $seat->event_id,
                        'seat_id' => $seat->id,
                        'status' => Booking::STATUS_CONFIRMED,
                        'total_amount' => 100.00, // Example price
                        'payment_status' => Booking::PAYMENT_STATUS_PAID,
                        'payment_method' => 'simulation'
                    ]);

                    // Update seat status
                    $seat->update([
                        'status' => Seat::STATUS_BOOKED,
                        'reservation_expires_at' => null
                    ]);

                    Log::info("Booking successful", [
                        'user_id' => $userId,
                        'seat_id' => $seatId,
                        'booking_id' => $booking->id
                    ]);

                    $successCount++;
                });
            } catch (Throwable $e) {
                Log::warning("Booking failed", [
                    'user_id' => $userId,
                    'seat_id' => $seatId,
                    'error' => $e->getMessage()
                ]);
                $failCount++;
            }

            if ($delay > 0) {
                usleep($delay * 1000); // Convert ms to microseconds
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info("Simulation completed in {$duration} seconds");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Successful Bookings', $successCount],
                ['Failed Bookings', $failCount],
                ['Total Attempts', $users],
                ['Duration (seconds)', $duration],
                ['Rate (attempts/second)', round($users / $duration, 2)]
            ]
        );
    }

    private function createTestUsers(int $count): void
    {
        $existingCount = User::count();
        $needed = $count - $existingCount;

        if ($needed <= 0) {
            return;
        }

        $this->info("Creating {$needed} test users...");

        for ($i = $existingCount + 1; $i <= $count; $i++) {
            User::create([
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
                'password' => bcrypt('password'),
            ]);
        }
    }
}
