<?php

namespace App\Mail;

use App\Mail\Concerns\HasEditableTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasEditableTemplate;

    public string $firstName;
    public string $lastName;
    public string $billingId;
    public array $address;
    public string $almostFinishedUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $billingId,
        array $address,
        string $almostFinishedUrl
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->billingId = $billingId;
        $this->address = $address;
        $this->almostFinishedUrl = $almostFinishedUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.support', 'support@apobox.com'),
            subject: $this->editableSubject('welcome', 'Welcome to APO Box Shipping'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->editableContent('welcome', 'emails.welcome');
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
            'billingId' => $this->billingId,
            'address' => $this->address,
            'almostFinishedUrl' => $this->almostFinishedUrl,
        ];
    }
}
