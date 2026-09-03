<?php

namespace App\Filament\Widgets;

use App\Enums\BookingRequestStatus;
use App\Models\BookingRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class BookingsChart extends ChartWidget
{
    protected static ?string $heading = 'Заявки за последние 30 дней';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 2;
    protected static ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 дней',
            '30' => '30 дней',
            '90' => '3 месяца',
            '365' => 'Год',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $labels = [];
        $newRequests = [];
        $approved = [];
        $rejected = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d.m');

            $newRequests[] = BookingRequest::whereDate('created_at', $date)->count();
            $approved[] = BookingRequest::where('status', BookingRequestStatus::APPROVED)
                ->whereDate('created_at', $date)->count();
            $rejected[] = BookingRequest::whereIn('status', [BookingRequestStatus::REJECTED, BookingRequestStatus::CANCELLED])
                ->whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Новые заявки',
                    'data' => $newRequests,
                    'borderColor' => '#77c4db',
                    'backgroundColor' => 'rgba(119, 196, 219, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Одобренные',
                    'data' => $approved,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Отклонённые',
                    'data' => $rejected,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
