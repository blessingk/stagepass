<?php

namespace App\Console\Commands;

use App\Models\Seat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seats:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release all expired seat reservations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired seat reservations...');

        try {
            DB::transaction(function () {
                $expiredSeats = Seat::where('status', Seat::STATUS_RESERVED)
                    ->where('reservation_expires_at', '<', now())
                    ->lockForUpdate()
                    ->get();

                $count = $expiredSeats->count();

                foreach ($expiredSeats as $seat) {
                    $seat->update([
                        'status' => Seat::STATUS_AVAILABLE,
                        'reservation_expires_at' => null
                    ]);

                    Log::info('Released expired reservation', [
                        'seat_id' => $seat->id,
                        'event_id' => $seat->event_id
                    ]);
                }

                $this->info("Released {$count} expired seat reservations.");
            });
        } catch (\Exception $e) {
            $this->error('An error occurred while cleaning up expired reservations:');
            $this->error($e->getMessage());
            Log::error('Failed to cleanup expired reservations', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        return 0;
    }
}
