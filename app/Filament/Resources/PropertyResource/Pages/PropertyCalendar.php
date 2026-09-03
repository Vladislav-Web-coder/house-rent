<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PricingService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Resources\Pages\Page;

class PropertyCalendar extends Page
{
    protected static string $resource = PropertyResource::class;
    protected static string $view = 'filament.resources.property-resource.pages.property-calendar';
    protected static ?string $title = 'Календарь и цены';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public ?Property $record = null;

    public array $calendarData = [];
    public int $currentYear;
    public int $currentMonth;
    public string $monthName = '';
    public array $months = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
    ];

    public function mount(Property $record): void
    {
        $this->record = $record;
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->loadCalendarData();
    }

    public function loadCalendarData(): void
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $period = CarbonPeriod::create($startDate, $endDate);
        $pricingService = app(PricingService::class);

        $this->calendarData = [];

        // Обновляем название месяца
        $months = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        $this->monthName = $months[$this->currentMonth] . ' ' . $this->currentYear;

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $priceInfo = $pricingService->getDailyPriceWithSource($this->record, $date);

            // Проверка доступности
            $isAvailable = true;
            $bookingInfo = null;

            // 1. Проверка подтвержденных броней
            $booking = $this->record->bookings()
                ->where('status', 'confirmed')
                ->where('check_in', '<=', $dateStr)
                ->where('check_out', '>', $dateStr)
                ->first();

            if ($booking) {
                $isAvailable = false;
                $bookingInfo = [
                    'type' => 'booking',
                    'guest_name' => $booking->guest_name,
                ];
            }

            // 2. Проверка заявок (если дата еще свободна)
            if ($isAvailable) {
                $request = $this->record->bookingRequests()
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('check_in', '<=', $dateStr)
                    ->where('check_out', '>', $dateStr)
                    ->first();

                if ($request) {
                    $isAvailable = false;
                    $bookingInfo = [
                        'type' => 'request',
                        'guest_name' => $request->guest_name,
                    ];
                }
            }

            // 3. Проверка синхронизированных броней (если дата еще свободна)
            if ($isAvailable) {
                $synced = $this->record->syncedBookings()
                    ->with('calendarLink')
                    ->where('check_in', '<=', $dateStr)
                    ->where('check_out', '>', $dateStr)
                    ->first();

                if ($synced) {
                    $isAvailable = false;
                    $platformName = $synced->calendarLink?->platform ?? 'Внешний источник';
                    $bookingInfo = [
                        'type' => 'synced',
                        'source' => $platformName,
                    ];
                }
            }

            $this->calendarData[$dateStr] = [
                'date' => $dateStr,
                'day_number' => $date->day,
                'day_name' => $this->getDayNameRu($date),
                'price' => $priceInfo['price'],
                'source' => $priceInfo['source'],
                'is_available' => $isAvailable,
                'booking_info' => $bookingInfo,
            ];
        }
    }

    public function updatedCurrentMonth(): void
    {
        $this->loadCalendarData();
    }

    public function updatedCurrentYear(): void
    {
        $this->loadCalendarData();
    }

    public function prevMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->loadCalendarData();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
        $this->loadCalendarData();
    }

    public function goToCurrentMonth(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->loadCalendarData();
    }

    public function getYearsProperty(): array
    {
        $currentYear = now()->year;
        $years = [];
        for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
            $years[$i] = $i;
        }
        return $years;
    }

    private function getDayNameRu(Carbon $date): string
    {
        $days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return $days[$date->dayOfWeek];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }
}
