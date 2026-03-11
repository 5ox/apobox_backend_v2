<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreditCardExpired extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $customerName;
    public string $updatePaymentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $customerName, string $updatePaymentUrl)
    {
        $this->customerName = $customerName;
        $this->updatePaymentUrl = $updatePaymentUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.noreply', 'noreply@apobox.com'),
            subject: 'APO Box Account - Credit Card Expired',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer_card_expired',
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
