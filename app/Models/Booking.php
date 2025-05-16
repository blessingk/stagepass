<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_RESERVED = 'reserved';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'total_amount',
        'payment_status',
        'payment_id',
        'payment_method',
        'paid_at',
        'cancelled_at',
        'expires_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function seats(): BelongsToMany
    {
        return $this->belongsToMany(Seat::class, 'booking_seat');
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function markAsCompleted($paymentId)
    {
        return $this->update([
            'payment_status' => self::PAYMENT_STATUS_COMPLETED,
            'payment_id' => $paymentId
        ]);
    }

    public function markAsFailed()
    {
        return $this->update([
            'payment_status' => self::PAYMENT_STATUS_FAILED
        ]);
    }

    public function markAsRefunded()
    {
        return $this->update([
            'payment_status' => self::PAYMENT_STATUS_REFUNDED
        ]);
    }
}
