<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    public function reserveSeats(Event $event, int $seatId, User $user)
    {
        return DB::transaction(function () use ($event, $seatId, $user) {
            // Lock the seats for update to prevent concurrent reservations
            $seat = Seat::where('id', $seatId)
                ->where('event_id', $event->id)
                ->where('status', Seat::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->firstOrFail();

            // Verify all requested seats are available
            if (!$seat) {
                throw new Exception('Seat are not available');
            }

            $seat->status = Seat::STATUS_RESERVED;
            $seat->reservation_expires_at = now();
            $seat->save();
            return Booking::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'seat_id' => $seatId,
                'total_amount' => $event->price,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING
            ]);
        });
    }

    public function confirmBooking(Booking $booking, string $paymentMethod, string $paymentId)
    {
        return DB::transaction(function () use ($booking, $paymentMethod, $paymentId) {
            // Lock the booking and associated seats
            $booking = Booking::where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$booking->isPending()) {
                throw new Exception('Booking is not in pending state');
            }

            // Check if reservation is still valid
            $hasExpiredSeat = Seat::where(function ($query) {
                    $query->where('status', '!=', Seat::STATUS_RESERVED)
                        ->orWhere('reservation_expires_at', '<', now());
                })
                ->where('id', $booking->seat_id)
                ->exists();

            if ($hasExpiredSeat) {
                throw new Exception('Seat reservation has expired');
            }

            // Update booking status
            $booking->update([
                'payment_status' => Booking::PAYMENT_STATUS_COMPLETED,
                'payment_method' => $paymentMethod,
                'payment_id' => $paymentId
            ]);

            // Update seat status
            Seat::where('id', $booking->seat_id)
                ->update([
                'status' => Seat::STATUS_BOOKED,
                'reservation_expires_at' => null
            ]);

            return $booking;
        });
    }

    public function releaseExpiredReservations()
    {
        return DB::transaction(function () {
            $expiredSeats = Seat::where('status', Seat::STATUS_RESERVED)
                ->where('reservation_expires_at', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($expiredSeats as $seat) {
                $seat->update([
                    'status' => Seat::STATUS_AVAILABLE,
                    'reservation_expires_at' => null
                ]);

                Booking::where('seat_id', $seat->id)
                    ->where('payment_status', Booking::PAYMENT_STATUS_PENDING)
                    ->update(['payment_status' => Booking::PAYMENT_STATUS_FAILED]);
            }

            return $expiredSeats->count();
        });
    }
}
