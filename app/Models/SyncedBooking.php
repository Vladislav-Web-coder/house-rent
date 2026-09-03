<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncedBooking extends Model
{
    protected $fillable = [
        'property_id',
        'calendar_link_id',
        'external_uid',
        'check_in',
        'check_out',
        'summary',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function calendarLink(): BelongsTo
    {
        return $this->belongsTo(CalendarLink::class);
    }
}
