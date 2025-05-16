<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Seat;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id'
        ]);

        try {
            $result = DB::transaction(function () use ($request, $event) {
                $seat = Seat::where('id', $request->seat_id)
                    ->where('event_id', $event->id)
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
                    throw ValidationException::withMessages([
                        'seat' => 'This seat is not available for booking.'
                    ]);
                }

                $booking = Booking::create([
                    'user_id' => auth()->id(),
                    'event_id' => $event->id,
                    'seat_id' => $seat->id,
                    'status' => Booking::STATUS_CONFIRMED,
                    'total_amount' => 100.00, // Example price
                    'payment_status' => Booking::PAYMENT_STATUS_PAID,
                    'payment_method' => 'credit_card'
                ]);

                $seat->update([
                    'status' => Seat::STATUS_BOOKED,
                    'reservation_expires_at' => null
                ]);

                return [
                    'message' => 'Seat booked successfully!',
                    'booking' => $booking->load('seat')
                ];
            });

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            
            return response()->json([
                'message' => 'An error occurred while processing your booking.'
            ], 500);
        }
    }

    public function reserve(Request $request, Event $event)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id'
        ]);

        try {
            $result = DB::transaction(function () use ($request, $event) {
                $seat = Seat::where('id', $request->seat_id)
                    ->where('event_id', $event->id)
                    ->where('status', Seat::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    throw ValidationException::withMessages([
                        'seat' => 'This seat is not available for reservation.'
                    ]);
                }

                $seat->update([
                    'status' => Seat::STATUS_RESERVED,
                    'reservation_expires_at' => now()->addMinutes(5)
                ]);

                return [
                    'message' => 'Seat reserved successfully!',
                    'seat' => $seat,
                    'expires_at' => $seat->reservation_expires_at
                ];
            });

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            
            return response()->json([
                'message' => 'An error occurred while processing your reservation.'
            ], 500);
        }
    }

    public function release(Request $request, Event $event)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id'
        ]);

        try {
            $result = DB::transaction(function () use ($request, $event) {
                $seat = Seat::where('id', $request->seat_id)
                    ->where('event_id', $event->id)
                    ->where('status', Seat::STATUS_RESERVED)
                    ->where('reservation_expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    throw ValidationException::withMessages([
                        'seat' => 'This seat cannot be released.'
                    ]);
                }

                $seat->update([
                    'status' => Seat::STATUS_AVAILABLE,
                    'reservation_expires_at' => null
                ]);

                return [
                    'message' => 'Seat released successfully!',
                    'seat' => $seat
                ];
            });

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            
            return response()->json([
                'message' => 'An error occurred while releasing the seat.'
            ], 500);
        }
    }

    public function payment(Booking $booking)
    {
        if (!$booking->isPending() || $booking->user_id !== auth()->id()) {
            abort(404);
        }

        return view('booking.payment', [
            'booking' => $booking->load('seats', 'event')
        ]);
    }

    public function confirm(Request $request, Booking $booking, BookingService $bookingService)
    {
        if (!$booking->isPending() || $booking->user_id !== auth()->id()) {
            abort(404);
        }

        $request->validate([
            'payment_method' => 'required|string',
            'payment_id' => 'required|string'
        ]);

        try {
            $booking = $bookingService->confirmBooking(
                $booking,
                $request->payment_method,
                $request->payment_id
            );

            return redirect()
                ->route('events.show', $booking->event)
                ->with('success', 'Booking confirmed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
