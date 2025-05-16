<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'date',
        'venue',
        'rows',
        'columns',
        'status',
        'price'
    ];

    protected $casts = [
        'date' => 'datetime',
        'rows' => 'integer',
        'columns' => 'integer',
        'price' => 'decimal:2'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CANCELLED = 'cancelled';

    const MAX_ROWS = 20;
    const MAX_COLUMNS = 20;

    protected $columnsPerRow = [
        8,  // Row A: 8 seats
        10, // Row B: 10 seats
        10, // Row C: 10 seats
        8,  // Row D: 8 seats
        6,  // Row E: 6 seats
        8,  // Row F: 8 seats
        10, // Row G: 10 seats
        10, // Row H: 10 seats
        8,  // Row I: 8 seats
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($event) {
            $event->seats()->delete();
            $event->bookings()->delete();
        });

        static::saving(function ($event) {
            if ($event->rows > self::MAX_ROWS) {
                throw ValidationException::withMessages([
                    'rows' => 'Maximum number of rows is ' . self::MAX_ROWS
                ]);
            }
            if ($event->columns > self::MAX_COLUMNS) {
                throw ValidationException::withMessages([
                    'columns' => 'Maximum number of columns is ' . self::MAX_COLUMNS
                ]);
            }
        });
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availableSeats()
    {
        return $this->seats()->where('status', 'available');
    }

    public function generateSeatMap()
    {
        // Validate dimensions
        if ($this->rows > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'dimensions' => sprintf(
                    'Seat map dimensions cannot exceed %d rows',
                    self::MAX_ROWS
                )
            ]);
        }

        // Delete existing seats if any
        $this->seats()->delete();

        // Generate new seats
        $seats = [];
        $now = now();

        for ($row = 1; $row <= $this->rows; $row++) {
            $columnsInRow = $this->columnsPerRow[$row - 1] ?? 8; // Default to 8 if not specified
            for ($col = 1; $col <= $columnsInRow; $col++) {
                $seats[] = [
                    'event_id' => $this->id,
                    'row' => $row,
                    'column' => $col,
                    'status' => 'available',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert seats in chunks to prevent memory issues
        foreach (array_chunk($seats, 100) as $chunk) {
            $this->seats()->insert($chunk);
        }

        return true;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
