<?php

namespace App\Mail;

use App\Mail\Concerns\HasEditableTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreditCardExpiring extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasEditableTemplate;

    public string $firstName;
    public string $lastName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address', 'no-reply@apobox.com'),
            subject: $this->editableSubject('customer_card_expiring', 'APO Box Account - Credit Card Expiring'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->editableContent('customer_card_expiring', 'emails.customer_card_expiring');
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
        ];
    }
}
