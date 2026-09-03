<?php

namespace App\Console\Commands;

use App\Models\BookingRequest;
use App\Enums\BookingRequestStatus;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CancelExpiredBookingRequests extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Автоматически отклоняет заявки в статусе pending, созданные более 24 часов назад';

    public function handle()
    {
        $expiredRequests = BookingRequest::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->get();

        foreach ($expiredRequests as $request) {
            $request->update([
                'status' => BookingRequestStatus::REJECTED,
                'admin_comment' => 'Автоматическая отмена: заявка не была подтверждена в течение 24 часов.',
            ]);
        }

        $this->info("Отменено просроченных заявок: {$expiredRequests->count()}");
        return Command::SUCCESS;
    }
}
