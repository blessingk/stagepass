<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Seat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'row',
        'column',
        'status',
        'reservation_expires_at'
    ];

    protected $casts = [
        'row' => 'integer',
        'column' => 'integer',
        'reservation_expires_at' => 'datetime'
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_BOOKED = 'booked';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_seat')
            ->withTimestamps();
    }

    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isReserved()
    {
        return $this->status === self::STATUS_RESERVED && 
               $this->reservation_expires_at > now();
    }

    public function isBooked()
    {
        return $this->status === self::STATUS_BOOKED;
    }

    public function reserve()
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_RESERVED,
            'reservation_expires_at' => now()->addMinutes(5)
        ]);
    }

    public function book()
    {
        if (!$this->isReserved()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_BOOKED,
            'reservation_expires_at' => null
        ]);
    }

    public function release()
    {
        if (!$this->isReserved()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_AVAILABLE,
            'reservation_expires_at' => null
        ]);
    }

    public function getLabel(): string
    {
        return "Row {$this->row}, Seat {$this->column}";
    }
}
