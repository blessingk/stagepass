<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use PDOException;

class BookingService
{
    const MAX_RETRIES = 3;
    const RETRY_DELAY_MS = 100;

    private function shouldRetryException(Exception $e): bool
    {
        if ($e instanceof PDOException) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Retry on connection issues and deadlocks
            return in_array($errorCode, ['2006', '2013', '2014']) || // MySQL server gone away, Lost connection, Unbuffered queries
                   str_contains($errorMessage, 'Packets out of order') ||
                   str_contains($errorMessage, 'COM_STMT_PREPARE') ||
                   str_contains($errorMessage, 'Deadlock found');
        }
        return false;
    }

    /**
     * @throws Exception
     */
    private function retryOperation(callable $operation, string $operationName)
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < self::MAX_RETRIES) {
            try {
                return $operation();
            } catch (Exception $e) {
                $lastException = $e;

                if (!$this->shouldRetryException($e)) {
                    throw $e;
                }

                $attempts++;
                if ($attempts < self::MAX_RETRIES) {
                    Log::warning("Retrying {$operationName} after error: " . $e->getMessage(), [
                        'attempt' => $attempts,
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                    usleep(self::RETRY_DELAY_MS * 1000 * $attempts); // Exponential backoff
                }
            }
        }

        Log::error("Failed {$operationName} after {$attempts} attempts", [
            'error' => $lastException->getMessage(),
            'code' => $lastException->getCode()
        ]);
        throw $lastException;
    }

    /**
     * @throws Exception
     */
    public function reserveSeats(Event $event, $seatId, User $user)
    {
        return $this->retryOperation(function () use ($event, $seatId, $user) {
            return DB::transaction(function () use ($event, $seatId, $user) {
                // Lock the seats for update to prevent concurrent reservations
                $seat = Seat::where('id', $seatId)
                    ->where('event_id', $event->id)
                    ->where('status', Seat::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    Log::info("Seat {$seatId} not available for event {$event->id}");
                    throw new Exception('Seat is not available');
                }

                // Double check seat is still available
                if ($seat->status !== Seat::STATUS_AVAILABLE) {
                    Log::info("Seat {$seatId} status changed to {$seat->status} for event {$event->id}");
                    throw new Exception('Seat is no longer available');
                }

                $seat->status = Seat::STATUS_RESERVED;
                $seat->reservation_expires_at = now()->addMinutes(5);
                $seat->save();

                $booking = Booking::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'total_amount' => $event->price,
                    'payment_status' => Booking::PAYMENT_STATUS_PENDING
                ]);

                // Attach the seat to the booking using the many-to-many relationship
                $booking->seats()->attach($seat->id);

                Log::info("User {$user->id} successfully reserved seat {$seatId} for event {$event->id}", [
                    'booking_id' => $booking->id
                ]);

                return $booking;
            });
        }, 'reserveSeats');
    }

    /**
     * @throws Exception
     */
    public function confirmBooking(Booking $booking, string $paymentMethod, string $paymentId)
    {
        return $this->retryOperation(function () use ($booking, $paymentMethod, $paymentId) {
            return DB::transaction(function () use ($booking, $paymentMethod, $paymentId) {
                // Lock the booking and associated seats
                $booking = Booking::where('id', $booking->id)
                    ->with('seats')
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$booking->isReserved()) {
                    Log::warning("Booking {$booking->id} is not in reserved state", [
                        'status' => $booking->status
                    ]);
                    throw new Exception('Booking is not in reserved state');
                }

                // Check if all seat reservations are still valid
                $seats = $booking->seats()
                    ->where('status', Seat::STATUS_RESERVED)
                    ->where('reservation_expires_at', '>', now())
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() !== $booking->seats->count()) {
                    Log::warning("Some seat reservations expired for booking {$booking->id}");
                    throw new Exception('Some seat reservations have expired');
                }

                // Update booking status
                $booking->update([
                    'status' => Booking::STATUS_CONFIRMED,
                    'payment_status' => Booking::PAYMENT_STATUS_PAID,
                    'payment_method' => $paymentMethod,
                    'payment_id' => $paymentId,
                    'paid_at' => now()
                ]);

                // Update all seats status
                foreach ($seats as $seat) {
                    $seat->update([
                        'status' => Seat::STATUS_BOOKED,
                        'reservation_expires_at' => null
                    ]);
                }

                Log::info("Booking {$booking->id} confirmed successfully", [
                    'user_id' => $booking->user_id,
                    'seat_ids' => $seats->pluck('id')
                ]);

                return $booking;
            });
        }, 'confirmBooking');
    }

    /**
     * @throws Exception
     */
    public function releaseExpiredReservations()
    {
        return $this->retryOperation(function () {
            return DB::transaction(function () {
                $expiredSeats = Seat::where('status', Seat::STATUS_RESERVED)
                    ->where('reservation_expires_at', '<', now())
                    ->with('bookings')
                    ->lockForUpdate()
                    ->get();

                $count = 0;
                foreach ($expiredSeats as $seat) {
                    $seat->update([
                        'status' => Seat::STATUS_AVAILABLE,
                        'reservation_expires_at' => null
                    ]);

                    // Update associated bookings
                    foreach ($seat->bookings as $booking) {
                        if ($booking->payment_status === Booking::PAYMENT_STATUS_PENDING) {
                            $booking->update([
                                'status' => Booking::STATUS_EXPIRED,
                                'payment_status' => Booking::PAYMENT_STATUS_FAILED
                            ]);
                            $count++;
                        }
                    }

                    Log::info("Released expired reservation for seat {$seat->id}", [
                        'affected_bookings' => $count
                    ]);
                }

                return $count;
            });
        }, 'releaseExpiredReservations');
    }
}
