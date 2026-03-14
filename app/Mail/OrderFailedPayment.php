<?php

namespace App\Mail;

use App\Mail\Concerns\HasEditableTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderFailedPayment extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasEditableTemplate;

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
            subject: $this->editableSubject('order_failed_payment', "APO Box Order #{$this->orderId} - Awaiting Payment"),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->editableContent('order_failed_payment', 'emails.order_failed_payment');
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
            'orderId' => $this->orderId,
            'payUrl' => $this->payUrl,
            'comments' => $this->comments,
        ];
    }
}
