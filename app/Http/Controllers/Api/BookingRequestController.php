<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateBookingRequestAction;
use App\Http\Requests\StoreBookingRequestRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;

class BookingRequestController
{
    public function store(
        Property $property,
        StoreBookingRequestRequest $request,
        CreateBookingRequestAction $action
    ): JsonResponse {
        // Скрытые объекты не должны быть доступны через публичный API
        if (!$property->is_visible) {
            return response()->json(['message' => 'Объект не найден'], 404);
        }

        try {
            $bookingRequest = $action->execute($property, $request->validated());

            return response()->json([
                'message' => 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
                'data' => [
                    'id' => $bookingRequest->id,
                    'status' => $bookingRequest->status,
                ]
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            \Log::error('Booking request failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Произошла ошибка при создании заявки. Попробуйте позже.',
            ], 500);
        }
    }
}
