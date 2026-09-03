<?php

namespace App\Mail;

use App\Models\BookingRequest;
use App\Services\ContractFileProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookingRequest $bookingRequest,
        public ?string $contractPath = null // Может быть null
    ) {}

    public function envelope(): Envelope
    {
        $propertyTitle = $this->bookingRequest->property->title ?? 'объект';

        return new Envelope(
            subject: "Заявка на бронирование «{$propertyTitle}» принята",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-received',
            with: [
                'booking' => $this->bookingRequest,
                'property' => $this->bookingRequest->property,
                'hasContract' => $this->contractPath !== null,
            ],
        );
    }

    public function attachments(): array
    {
        // Прикрепляем только если файл существует
        if (!$this->contractPath || !file_exists($this->contractPath)) {
            return [];
        }

        $displayName = app(ContractFileProvider::class)->getDisplayName();

        return [
            Attachment::fromPath($this->contractPath)
                ->as($displayName)
                ->withMime('application/pdf'),
        ];
    }
}
