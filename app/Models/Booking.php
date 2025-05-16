<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2'
    ];

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETED = 'completed';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function isPending()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isCompleted()
    {
        return $this->payment_status === self::PAYMENT_STATUS_COMPLETED;
    }

    public function isFailed()
    {
        return $this->payment_status === self::PAYMENT_STATUS_FAILED;
    }

    public function isRefunded()
    {
        return $this->payment_status === self::PAYMENT_STATUS_REFUNDED;
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
