<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Берём медиа из уже загруженного отношения (см. PropertyController::index),
        // чтобы не генерировать N+1 запросов на каждый объект в списке.
        $galleryMedia = $this->relationLoaded('media')
            ? $this->media->sortBy('order_column')->values()
            : $this->getMedia('gallery');

        $firstMedia = $galleryMedia->first();
        $mainMediaType = $firstMedia && str_starts_with($firstMedia->mime_type, 'video/')
            ? 'video'
            : 'image';

        $gallery = $galleryMedia
            ->map(function ($media) {
                $isVideo = str_starts_with($media->mime_type, 'video/');
                $hasThumb = $media->hasGeneratedConversion('thumb_800x600');

                return [
                    'id' => $media->id,
                    'type' => $isVideo ? 'video' : 'image',
                    'mime_type' => $media->mime_type,
                    'url' => $isVideo ? $media->getUrl() : $media->getUrl('thumb_800x600'),
                    'thumb' => $hasThumb ? $media->getUrl('thumb_800x600') : null,
                    'has_thumb' => $hasThumb,
                ];
            })
            ->values()
            ->toArray();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'category_label' => $this->category === 'house' ? 'Дом' : 'Квартира',

            'base_price' => (float) $this->base_price,
            'address' => $this->address,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,

            'description_short' => Str::limit(strip_tags($this->description), 120),
            'description' => $this->description, // Полное описание для карточки

            'max_adults' => $this->max_adults,
            'max_children' => $this->max_children,
            'max_guests' => ($this->max_adults ?? 0) + ($this->max_children ?? 0),

            'rooms' => $this->rooms ? (int) $this->rooms : null,
            'area' => $this->area ? (float) $this->area : null,
            'min_stay' => $this->min_stay ? (int) $this->min_stay : 1,

            // Медиа
            'main_image' => $firstMedia && $firstMedia->hasGeneratedConversion('thumb_800x600')
                ? $firstMedia->getUrl('thumb_800x600')
                : ($firstMedia ? $firstMedia->getUrl() : null),
            'main_media_type' => $mainMediaType,
            'gallery' => $gallery,
            'images' => $gallery, // алиас для совместимости

            // Счётчики видео
            'has_videos' => collect($gallery)->contains('type', 'video'),
            'video_count' => collect($gallery)->where('type', 'video')->count(),

            // Для проверки доступности (на главной)
            'bookings' => $this->whenLoaded('bookings', function () {
                return $this->bookings
                    ->where('status', 'confirmed')
                    ->map(fn($booking) => [
                        'check_in' => $booking->check_in->format('Y-m-d'),
                        'check_out' => $booking->check_out->format('Y-m-d'),
                    ])
                    ->values()
                    ->toArray();
            }, []),
        ];
    }
}
