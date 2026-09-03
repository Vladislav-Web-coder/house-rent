<?php

namespace App\Providers;

use App\Listeners\ProcessVideoMedia;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\PriceByDayOfWeek;
use App\Models\PriceException;
use App\Models\PricePeriod;
use App\Models\Property;
use App\Models\SyncedBooking;
use App\Observers\BookingObserver;
use App\Observers\BookingRequestObserver;
use App\Observers\MediaObserver;
use App\Observers\PriceRelatedObserver;
use App\Observers\PropertyObserver;
use App\Observers\SyncedBookingObserver;
use App\Services\AvailabilityService;
use App\Services\CacheService;
use App\Services\ContractFileProvider;
use App\Services\PricingService;
use App\Services\VideoOptimizer;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        MediaHasBeenAddedEvent::class => [
            ProcessVideoMedia::class,
        ],
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AvailabilityService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(CacheService::class);
        $this->app->singleton(ContractFileProvider::class);
        $this->app->singleton(VideoOptimizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Property::observe(PropertyObserver::class);
        Booking::observe(BookingObserver::class);
        BookingRequest::observe(BookingRequestObserver::class);

        // SyncedBooking может не быть — проверяем существование модели
        if (class_exists(SyncedBooking::class)) {
            SyncedBooking::observe(SyncedBookingObserver::class);
        }

        // Ценовые модели
        if (class_exists(PriceException::class)) {
            PriceException::observe(PriceRelatedObserver::class);
        }
        if (class_exists(PricePeriod::class)) {
            PricePeriod::observe(PriceRelatedObserver::class);
        }
        if (class_exists(PriceByDayOfWeek::class)) {
            PriceByDayOfWeek::observe(PriceRelatedObserver::class);
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
