<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $unavailableDates = [];

        $addDates = function ($checkIn, $checkOut) use (&$unavailableDates) {
            $start = Carbon::parse($checkIn);
            $end = Carbon::parse($checkOut)->subDay();

            if ($start->gt($end)) {
                return;
            }

            $unavailableDates[] = [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ];
        };

        if ($this->relationLoaded('bookings')) {
            foreach ($this->bookings as $booking) {
                $addDates($booking->check_in, $booking->check_out);
            }
        }

        if ($this->relationLoaded('bookingRequests')) {
            foreach ($this->bookingRequests as $req) {
                $addDates($req->check_in, $req->check_out);
            }
        }

        if ($this->relationLoaded('syncedBookings')) {
            foreach ($this->syncedBookings as $synced) {
                $addDates($synced->check_in, $synced->check_out);
            }
        }

        // Определяем главное медиа и его тип
        $firstMedia = $this->getFirstMedia('gallery');
        $mainMediaType = $firstMedia && str_starts_with($firstMedia->mime_type, 'video/')
            ? 'video'
            : 'image';

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->category === 'house' ? 'Дом' : 'Квартира',

            'base_price' => (float) $this->base_price,
            'min_stay' => (int) $this->min_stay,
            'max_adults' => (int) $this->max_adults,
            'max_children' => (int) $this->max_children,
            'address' => $this->address,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'area' => $this->area ? (float) $this->area : null,
            'rooms' => $this->rooms ? (int) $this->rooms : null,

            // Главное медиа (может быть фото или видео)
            'main_image' => $this->getFirstMediaUrl('gallery', 'thumb_800x600'),
            'main_media_type' => $mainMediaType,

            'gallery' => $this->getMedia('gallery')
                ->sortBy('order_column')
                ->map(function ($media) {
                    $isVideo = str_starts_with($media->mime_type, 'video/');
                    $hasThumb = $media->hasGeneratedConversion('thumb_800x600');

                    return [
                        'id' => $media->id,
                        'type' => $isVideo ? 'video' : 'image',
                        'mime_type' => $media->mime_type,
                        'url' => $isVideo ? $media->getUrl() : $media->getUrl('large_1600x1200'),
                        'thumb' => $hasThumb ? $media->getUrl('thumb_800x600') : null,
                        'has_thumb' => $hasThumb,
                    ];
                })
                ->values()
                ->toArray(),

            'unavailable_dates' => $unavailableDates,
        ];
    }
}
