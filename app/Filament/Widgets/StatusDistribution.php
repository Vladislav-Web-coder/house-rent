<?php

namespace App\Filament\Widgets;

use App\Enums\BookingRequestStatus;
use App\Models\BookingRequest;
use Filament\Widgets\ChartWidget;

class StatusDistribution extends ChartWidget
{
    protected static ?string $heading = 'Распределение заявок по статусам';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '300px';
    private const STATUS_HEX_COLORS = [
        'pending' => '#fbbf24',
        'approved' => '#10b981',
        'rejected' => '#ef4444',
        'cancelled' => '#9ca3af',
    ];

    protected function getData(): array
    {
        $stats = BookingRequest::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels = [];
        $data = [];
        $colors = [];

        foreach (BookingRequestStatus::cases() as $status) {
            $count = $stats[$status->value] ?? 0;

            if ($count > 0) {
                $labels[] = "{$status->label()}";
                $data[] = $count;
                $colors[] = self::STATUS_HEX_COLORS[$status->value] ?? '#9ca3af';
            }
        }

        // Если данных нет — показываем пустое состояние
        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Заявки',
                        'data' => [1],
                        'backgroundColor' => ['#e5e7eb'],
                    ],
                ],
                'labels' => ['Нет данных'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Заявки',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
