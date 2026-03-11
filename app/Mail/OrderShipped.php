<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstName;
    public string $lastName;
    public string $orderId;
    public ?string $outboundTracking;
    public ?string $inboundTracking;
    public string $trackingUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $orderId,
        ?string $outboundTracking,
        ?string $inboundTracking,
        string $trackingUrl
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->orderId = $orderId;
        $this->outboundTracking = $outboundTracking;
        $this->inboundTracking = $inboundTracking;
        $this->trackingUrl = $trackingUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.noreply', 'noreply@apobox.com'),
            subject: "APO Box Order #{$this->orderId} - Shipped",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_shipped',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
