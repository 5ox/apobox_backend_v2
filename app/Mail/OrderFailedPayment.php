<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderFailedPayment extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $orderId;
    public ?string $comments;
    public string $payUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $orderId, string $payUrl, ?string $comments = null)
    {
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
            subject: "APO Box Order #{$this->orderId} - Awaiting Payment",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_failed_payment',
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
