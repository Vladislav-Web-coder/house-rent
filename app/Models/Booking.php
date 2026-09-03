<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'property_id',
        'booking_request_id',
        'source',
        'guest_name',
        'guest_phone',
        'check_in',
        'check_out',
        'adults',
        'children',
        'total_price',
        'status',
        'admin_comment',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
    ];

    // Связи
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }
}
