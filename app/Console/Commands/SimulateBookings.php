<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SimulateBookings extends Command
{
    protected $signature = 'booking:simulate {event_id} {seat_id} {num_users=5}';
    protected $description = 'Simulate concurrent bookings for the same seat';

    public function handle(BookingService $bookingService)
    {
        $eventId = $this->argument('event_id');
        $seatId = $this->argument('seat_id');
        $numUsers = $this->argument('num_users');

        $event = Event::findOrFail($eventId);
        $users = User::factory()->count($numUsers)->create();

        $processes = [];
        foreach ($users as $user) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                $this->error('Could not fork process');
                return 1;
            } else if ($pid) {
                // Parent process
                $processes[] = $pid;
            } else {
                // Child process
                try {
                    $booking = $bookingService->reserveSeats($event, [$seatId], $user);
                    Log::info("User {$user->id} successfully reserved seat {$seatId}");
                    
                    // Simulate payment processing
                    sleep(rand(1, 3));
                    
                    $bookingService->confirmBooking(
                        $booking,
                        'test_payment',
                        'test_' . uniqid()
                    );
                    Log::info("User {$user->id} successfully confirmed booking {$booking->id}");
                } catch (\Exception $e) {
                    Log::info("User {$user->id} failed to book seat {$seatId}: {$e->getMessage()}");
                }
                exit(0);
            }
        }

        // Wait for all child processes to complete
        foreach ($processes as $pid) {
            pcntl_waitpid($pid, $status);
        }

        $this->info('Simulation completed. Check the logs for results.');
        return 0;
    }
} 