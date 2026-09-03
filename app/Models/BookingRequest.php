<?php

namespace App\Models;

use App\Enums\BookingRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    protected $fillable = [
        'property_id', 'guest_name', 'guest_phone', 'country_code', 'contact_method',
        'check_in', 'check_out', 'adults', 'children',
        'total_price', 'comment', 'status', 'admin_comment', 'guest_email'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'status' => BookingRequestStatus::class,
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
