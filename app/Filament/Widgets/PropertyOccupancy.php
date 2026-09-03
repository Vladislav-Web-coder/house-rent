<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Property;
use App\Models\BookingRequest;
use App\Models\SyncedBooking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PropertyOccupancy extends ChartWidget
{
    protected static ?string $heading = 'Загрузка объектов на ближайшие 30 дней';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $properties = Property::where('is_visible', true)->get();
        $labels = [];
        $data = [];
        $colors = [];

        $today = Carbon::now()->startOfDay();
        $monthAhead = $today->copy()->addDays(30);

        foreach ($properties as $property) {
            $occupiedDays = $this->getOccupiedDaysCount($property->id, $today, $monthAhead);
            $occupancyPercent = round(($occupiedDays / 30) * 100);

            $labels[] = $property->title;
            $data[] = $occupancyPercent;

            // Цвет в зависимости от загрузки
            if ($occupancyPercent > 70) {
                $colors[] = '#10b981'; // зелёный — хорошо загружен
            } elseif ($occupancyPercent > 40) {
                $colors[] = '#fbbf24'; // жёлтый — средне
            } else {
                $colors[] = '#ef4444'; // красный — низкая загрузка
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Загрузка (%)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // горизонтальные бары
            'scales' => [
                'x' => [
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Занятость (%)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    private function getOccupiedDaysCount(int $propertyId, Carbon $from, Carbon $to): int
    {
        $occupiedDates = [];

        // Брони
        $bookings = Booking::where('property_id', $propertyId)
            ->where('status', 'confirmed')
            ->where('check_out', '>', $from)
            ->where('check_in', '<=', $to)
            ->get();

        // Заявки
        $requests = BookingRequest::where('property_id', $propertyId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_out', '>', $from)
            ->where('check_in', '<=', $to)
            ->get();

        // Синхронизированные
        $synced = SyncedBooking::where('property_id', $propertyId)
            ->where('check_out', '>', $from)
            ->where('check_in', '<=', $to)
            ->get();

        foreach ($bookings->merge($requests)->merge($synced) as $booking) {
            $start = Carbon::parse($booking->check_in);
            $end = Carbon::parse($booking->check_out)->subDay();

            if ($start->isBefore($from)) $start = $from->copy();
            if ($end->isAfter($to)) $end = $to->copy();
            if ($start->gt($end)) continue;

            $period = \Carbon\CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $occupiedDates[$date->toDateString()] = true;
            }
        }

        return count($occupiedDates);
    }
}
