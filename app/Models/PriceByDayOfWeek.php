<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceByDayOfWeek extends Model
{
    protected $fillable = [
        'property_id',
        'day_of_week',
        'price',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Воскресенье',
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
        ];
        return $days[$this->day_of_week] ?? 'Неизвестно';
    }
}
