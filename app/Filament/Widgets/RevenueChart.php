<?php

namespace App\Filament\Widgets;

use App\Enums\BookingRequestStatus;
use App\Models\BookingRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Выручка за последние 12 месяцев';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $labels = [];
        $revenue = [];
        $requestsCount = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $labels[] = $month->locale('ru')->translatedFormat('M Y');

            $revenue[] = BookingRequest::where('status', BookingRequestStatus::APPROVED)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_price');

            $requestsCount[] = BookingRequest::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Выручка (₽)',
                    'data' => $revenue,
                    'backgroundColor' => 'rgba(119, 196, 219, 0.7)',
                    'borderColor' => '#77c4db',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Количество заявок',
                    'data' => $requestsCount,
                    'type' => 'line',
                    'borderColor' => '#283e46',
                    'backgroundColor' => 'rgba(40, 62, 70, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
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
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Выручка (₽)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Заявки',
                    ],
                ],
            ],
        ];
    }
}
