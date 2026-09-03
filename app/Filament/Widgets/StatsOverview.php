<?php

namespace App\Filament\Widgets;

use App\Enums\BookingRequestStatus;
use App\Models\BookingRequest;
use App\Models\Booking;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Заявки за текущий месяц
        $requestsThisMonth = BookingRequest::whereBetween('created_at', [$startOfMonth, $now])->count();
        $requestsLastMonth = BookingRequest::whereBetween('created_at', [$startOfMonth->copy()->subMonth(), $endOfLastMonth])->count();

        // Рост заявок в процентах
        $requestsGrowth = $requestsLastMonth > 0
            ? round((($requestsThisMonth - $requestsLastMonth) / $requestsLastMonth) * 100)
            : 100;

        // Ожидают подтверждения
        $pendingRequests = BookingRequest::where('status', BookingRequestStatus::PENDING)->count();

        // Одобренные заявки
        $approvedThisMonth = BookingRequest::where('status', BookingRequestStatus::APPROVED)
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->count();

        // Выручка за месяц (сумма одобренных заявок)
        $revenueThisMonth = BookingRequest::where('status', BookingRequestStatus::APPROVED)
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->sum('total_price');

        $revenueLastMonth = BookingRequest::where('status', BookingRequestStatus::APPROVED)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_price');

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100)
            : 100;

        // Активные брони (проживающие сейчас)
        $activeBookings = Booking::where('status', 'confirmed')
            ->where('check_in', '<=', $now)
            ->where('check_out', '>', $now)
            ->count();

        // Загрузка объектов на ближайшие 7 дней
        $occupancy = $this->calculateOccupancy(7);

        return [
            // Заявки за месяц
            Stat::make('Заявки за месяц', $requestsThisMonth)
                ->description($requestsGrowth >= 0 ? "↑ Рост на {$requestsGrowth}%" : "↓ Падение на " . abs($requestsGrowth) . "%")
                ->descriptionIcon($requestsGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($requestsGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getRequestsChartData()),

            // Ожидают подтверждения
            Stat::make('Ожидают подтверждения', $pendingRequests)
                ->description('Требуют внимания')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),

            // Одобренные за месяц
            Stat::make('Одобрено за месяц', $approvedThisMonth)
                ->description('Успешные бронирования')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // Выручка за месяц
            Stat::make('Выручка за месяц', number_format($revenueThisMonth, 0, '.', ' ') . ' ₽')
                ->description($revenueGrowth >= 0 ? "↑ Рост на {$revenueGrowth}%" : "↓ Падение на " . abs($revenueGrowth) . "%")
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChartData()),

            // Активные брони сейчас
            Stat::make('Проживают сейчас', $activeBookings)
                ->description('Гостей в объектах')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            // Загрузка на 7 дней
            Stat::make('Загрузка на 7 дней', $occupancy . '%')
                ->description('Занятость объектов')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($occupancy > 70 ? 'success' : ($occupancy > 40 ? 'warning' : 'danger')),
        ];
    }

    // Данные для мини-графика заявок
    private function getRequestsChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = BookingRequest::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    // Данные для мини-графика выручки
    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sum = BookingRequest::where('status', 'approved')
                ->whereDate('created_at', $date)
                ->sum('total_price');
            $data[] = $sum;
        }
        return $data;
    }

    // Расчёт загрузки объектов на ближайшие дни
    private function calculateOccupancy(int $days): int
    {
        $totalProperties = Property::where('is_visible', true)->count();
        if ($totalProperties === 0) return 0;

        $today = Carbon::now()->startOfDay();
        $totalAvailableSlots = $totalProperties * $days;
        $occupiedSlots = 0;

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);
            $occupied = Booking::where('status', 'confirmed')
                ->where('check_in', '<=', $date)
                ->where('check_out', '>', $date)
                ->count();
            $occupiedSlots += min($occupied, $totalProperties);
        }

        return round(($occupiedSlots / $totalAvailableSlots) * 100);
    }
}
