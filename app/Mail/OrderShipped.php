<?php

namespace App\Mail;

use App\Mail\Concerns\HasEditableTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasEditableTemplate;

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
            from: config('mail.from.address', 'no-reply@apobox.com'),
            subject: $this->editableSubject('order_shipped', "APO Box Order #{$this->orderId} - Shipped"),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->editableContent('order_shipped', 'emails.order_shipped');
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

    protected function templateData(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'orderId' => $this->orderId,
            'outboundTracking' => $this->outboundTracking,
            'inboundTracking' => $this->inboundTracking,
            'trackingUrl' => $this->trackingUrl,
        ];
    }
}
