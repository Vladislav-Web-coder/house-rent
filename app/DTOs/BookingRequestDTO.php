<?php

namespace App\DTOs;

final readonly class BookingRequestDTO
{
    public function __construct(
        public string $guestName,
        public string $guestPhone,
        public string $checkIn,
        public string $checkOut,
        public int $adults,
        public int $children,
        public ?string $comment,
    )
    {}

    public static function fromArray(array $data): self
    {
        return new self(
            guestName: $data['guest_name'],
            guestPhone: $data['guest_phone'],
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            comment: $data['comment'] ?? null,
        );
    }
}
