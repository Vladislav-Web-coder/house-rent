<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title', 'description', 'category', 'base_price', 'area', 'rooms',
        'min_stay', 'max_adults', 'max_children',
        'latitude', 'longitude', 'address', 'ical_export_token', 'is_visible'
    ];
    protected $casts = [
        'is_visible' => 'boolean',
        'base_price' => 'decimal:2',
        'area' => 'decimal:2',
        'rooms' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($property) {
            if (empty($property->ical_export_token)) {
                $property->ical_export_token = Str::random(40);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useFallbackUrl('/images/fallback-property.jpg');
    }

    /**
     * Регистрация конверсий с поддержкой видео
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $isVideo = $media && str_starts_with($media->mime_type, 'video/');

        // Превью генерируется вручную в ProcessVideoMedia,
        // но Spatie должен знать о конверсии для getUrl() и hasGeneratedConversion()
        $thumbConversion = $this->addMediaConversion('thumb_800x600')
            ->width(800)
            ->height(600)
            ->nonQueued();

        if ($isVideo) {
            // ❌ НЕ используем extractVideoFrameAtSecond — делаем сами
            // Но всё равно регистрируем конверсию, чтобы Spatie знал о ней
        } else {
            $thumbConversion->sharpen(10);
        }

        // Большая версия — только для фото
        if (!$isVideo) {
            $this->addMediaConversion('large_1600x1200')
                ->width(1600)
                ->height(1200)
                ->quality(85)
                ->nonQueued();
        }
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function calendarLinks(): HasMany
    {
        return $this->hasMany(CalendarLink::class);
    }

    public function syncedBookings(): HasMany
    {
        return $this->hasMany(SyncedBooking::class);
    }

    public function pricePeriods(): HasMany
    {
        return $this->hasMany(PricePeriod::class);
    }

    public function priceExceptions(): HasMany
    {
        return $this->hasMany(PriceException::class);
    }

    public function priceByDayOfWeeks(): HasMany
    {
        return $this->hasMany(PriceByDayOfWeek::class);
    }

    // === ХЕЛПЕРЫ ДЛЯ МЕДИА ===

    /**
     * Есть ли видео в галерее
     */
    public function getHasVideoAttribute(): bool
    {
        return $this->getMedia('gallery')
            ->contains(fn (Media $m) => str_starts_with($m->mime_type, 'video/'));
    }

    /**
     * Количество видео в галерее
     */
    public function getVideoCountAttribute(): int
    {
        return $this->getMedia('gallery')
            ->filter(fn (Media $m) => str_starts_with($m->mime_type, 'video/'))
            ->count();
    }

    public function getUnavailableDatesAttribute(): array
    {
        $bookings = $this->bookings()->where('status', 'confirmed')->get();
        $requests = $this->bookingRequests()->whereIn('status', ['pending', 'approved'])->get();
        $synced = $this->syncedBookings()->get();

        $unavailable = [];

        foreach (array_merge($bookings, $requests, $synced) as $item) {
            $unavailable[] = [
                'start' => $item->check_in->format('Y-m-d'),
                'end' => $item->check_out->format('Y-m-d')
            ];
        }

        return $unavailable;
    }
}
