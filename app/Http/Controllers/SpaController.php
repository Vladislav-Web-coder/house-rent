<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class SpaController extends Controller
{
    /**
     * Главная страница
     */
    public function home()
    {
        return view('app', [
            'title' => 'УЮТНЫЙДОМ — аренда домов на берегу Ладоги | Карелия',
            'description' => 'Аренда комфортных домов и квартир на берегу Ладожского озера. Дома для всей семьи рядом с самыми интересными местами Карелии. Бронируйте онлайн!',
            'keywords' => 'аренда дома ладога, карелия отдых, дом у озера, аренда коттеджа ладожское озеро, отдых в карелии с детьми',
            'canonical' => url('/'),
            'ogImage' => asset('images/og-cover.jpg'),
            'ogType' => 'website',
            'organizationSchema' => $this->generateOrganizationSchema(), // ✅
        ]);
    }

    /**
     * Страница объекта
     */
    public function property(Request $request, string $id)
    {
        $property = Property::where('id', $id)
            ->where('is_visible', true)
            ->firstOrFail();

        $description = strip_tags($property->description);
        $description = mb_substr($description, 0, 160) . '...';

        return view('app', [
            'title' => "{$property->title} — УЮТНЫЙДОМ | Аренда на берегу Ладоги",
            'description' => $description,
            'keywords' => "{$property->title}, аренда {$this->getCategoryWord($property->category)}, ладога, карелия",
            'canonical' => url("/property/{$property->id}"),
            'ogImage' => $property->getFirstMediaUrl('gallery', 'thumb_800x600') ?: asset('images/og-cover.jpg'),
            'ogType' => 'website',
            'property' => $property,
            'organizationSchema' => $this->generateOrganizationSchema(), // ✅
            'propertySchema' => $this->generatePropertySchema($property), // ✅
        ]);
    }

    /**
     * Политика конфиденциальности
     */
    public function policy()
    {
        return view('app', [
            'title' => 'Политика конфиденциальности — УЮТНЫЙДОМ',
            'description' => 'Политика конфиденциальности сайта УЮТНЫЙДОМ. Условия обработки персональных данных пользователей.',
            'keywords' => 'политика конфиденциальности, персональные данные',
            'canonical' => url('/policy'),
            'organizationSchema' => $this->generateOrganizationSchema(), // ✅
        ]);
    }

    /**
     * 404 страница
     */
    public function notFound()
    {
        return response()->view('app', [
            'title' => 'Страница не найдена — УЮТНЫЙДОМ',
            'description' => 'Запрашиваемая страница не существует.',
            'canonical' => url('/'),
            'organizationSchema' => $this->generateOrganizationSchema(),
        ], 404);
    }

    /**
     * ✅ Schema.org для организации (на всех страницах)
     */
    private function generateOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => 'УЮТНЫЙДОМ',
            'description' => 'Аренда домов и квартир на берегу Ладожского озера в Карелии. Дома для всей семьи рядом с самыми интересными местами Карелии.',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'image' => asset('images/og-cover.jpg'),
            'telephone' => '+7 999 999-99-99',
            'email' => 'info@uyutnydom.ru',
            'priceRange' => '₽₽',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'RU',
                'addressRegion' => 'Республика Карелия',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => 61.0,
                'longitude' => 30.0,
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday',
                    'Friday', 'Saturday', 'Sunday',
                ],
                'opens' => '09:00',
                'closes' => '21:00',
            ],
            'sameAs' => [
                'https://t.me/uyutnydom',
                'https://vk.com/uyutnydom',
            ],
        ];
    }

    /**
     * ✅ Schema.org для объекта недвижимости
     */
    private function generatePropertySchema(Property $property): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $property->category === 'house' ? 'House' : 'Apartment',
            'name' => $property->title,
            'description' => mb_substr(strip_tags($property->description), 0, 300),
            'url' => url("/property/{$property->id}"),
            'image' => $property->getFirstMediaUrl('gallery') ?: asset('images/og-cover.jpg'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $property->address,
                'addressCountry' => 'RU',
            ],
            'numberOfRooms' => $property->rooms ?? 1,
            'floorSize' => [
                '@type' => 'QuantitativeValue',
                'value' => $property->area ?? 0,
                'unitCode' => 'MTK',
            ],
            'occupancy' => [
                '@type' => 'QuantitativeValue',
                'value' => ($property->max_adults ?? 0) + ($property->max_children ?? 0),
            ],
            'petsAllowed' => true,
            'smokingAllowed' => false,
            'priceRange' => 'от ' . number_format($property->base_price, 0, '.', ' ') . ' ₽/ночь',
            'offers' => [
                '@type' => 'Offer',
                'price' => $property->base_price,
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock',
                'url' => url("/property/{$property->id}"),
            ],
        ];

        if ($property->latitude && $property->longitude) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $property->latitude,
                'longitude' => (float) $property->longitude,
            ];
        }

        return $schema;
    }

    /**
     * Вспомогательный метод для склонения категории
     */
    private function getCategoryWord(string $category): string
    {
        return match ($category) {
            'house' => 'дома',
            'apartment' => 'квартиры',
            default => 'объекта',
        };
    }
}
