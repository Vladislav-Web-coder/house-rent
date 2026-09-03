<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarLink extends Model
{
    protected $fillable = [
        'property_id',
        'platform',
        'url',
        'etag',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function syncedBookings(): HasMany
    {
        return $this->hasMany(SyncedBooking::class);
    }
}
