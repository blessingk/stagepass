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
    public function reserveSeats(Event $event, array $seatIds, User $user)
    {
        return DB::transaction(function () use ($event, $seatIds, $user) {
            // Lock the seats for update to prevent concurrent reservations
            $seats = Seat::whereIn('id', $seatIds)
                ->where('event_id', $event->id)
                ->where('status', Seat::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->get();

            // Verify all requested seats are available
            if ($seats->count() !== count($seatIds)) {
                throw new Exception('One or more seats are not available');
            }

            // Calculate total amount
            $totalAmount = $event->price * $seats->count();

            // Create booking
            $booking = Booking::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'payment_status' => Booking::PAYMENT_STATUS_PENDING
            ]);

            // Reserve seats
            foreach ($seats as $seat) {
                $seat->update([
                    'status' => Seat::STATUS_RESERVED,
                    'booking_id' => $booking->id,
                    'reservation_expires_at' => now()->addMinutes(5)
                ]);
            }

            return $booking;
        });
    }

    public function confirmBooking(Booking $booking, string $paymentMethod, string $paymentId)
    {
        return DB::transaction(function () use ($booking, $paymentMethod, $paymentId) {
            // Lock the booking and associated seats
            $booking = Booking::with(['seats'])
                ->where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$booking->isPending()) {
                throw new Exception('Booking is not in pending state');
            }

            // Check if reservation is still valid
            $hasExpiredSeats = $booking->seats()
                ->where(function ($query) {
                    $query->where('status', '!=', Seat::STATUS_RESERVED)
                        ->orWhere('reservation_expires_at', '<', now());
                })
                ->exists();

            if ($hasExpiredSeats) {
                throw new Exception('Seat reservation has expired');
            }

            // Update booking status
            $booking->update([
                'payment_status' => Booking::PAYMENT_STATUS_COMPLETED,
                'payment_method' => $paymentMethod,
                'payment_id' => $paymentId
            ]);

            // Update seat status
            $booking->seats()->update([
                'status' => Seat::STATUS_BOOKED,
                'reservation_expires_at' => null
            ]);

            return $booking->fresh(['seats']);
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
                    'booking_id' => null,
                    'reservation_expires_at' => null
                ]);
            }

            // Mark associated bookings as failed
            $bookingIds = $expiredSeats->pluck('booking_id')->unique()->filter();
            if ($bookingIds->isNotEmpty()) {
                Booking::whereIn('id', $bookingIds)
                    ->where('payment_status', Booking::PAYMENT_STATUS_PENDING)
                    ->update(['payment_status' => Booking::PAYMENT_STATUS_FAILED]);
            }

            return $expiredSeats->count();
        });
    }
} 