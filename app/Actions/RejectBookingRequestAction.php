<?php

namespace app\Actions;

use App\Enums\BookingRequestStatus;
use App\Models\BookingRequest;

class RejectBookingRequestAction
{
    public function execute(BookingRequest $request, ?string $reason = null): void
    {
        $request->update([
            'status' => BookingRequestStatus::REJECTED,
            'admin_comment' => $reason,
        ]);
    }
}
