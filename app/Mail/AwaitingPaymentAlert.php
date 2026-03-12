<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AwaitingPaymentAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $customerName;
    public string $orderId;
    public string $payUrl;
    public ?string $comments;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $customerName,
        string $orderId,
        string $payUrl,
        ?string $comments = null
    ) {
        $this->customerName = $customerName;
        $this->orderId = $orderId;
        $this->payUrl = $payUrl;
        $this->comments = $comments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address', 'no-reply@apobox.com'),
            subject: 'APO Box Account - Package Awaiting Payment',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.awaiting_payment_alert',
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
