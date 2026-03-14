<?php

namespace App\Mail;

use App\Mail\Concerns\HasEditableTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartialSignupAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasEditableTemplate;

    public string $customerName;
    public string $addAddressUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $customerName, string $addAddressUrl)
    {
        $this->customerName = $customerName;
        $this->addAddressUrl = $addAddressUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address', 'no-reply@apobox.com'),
            subject: $this->editableSubject('partial_signup_alert', 'APO Box Account - Complete Your Registration'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->editableContent('partial_signup_alert', 'emails.partial_signup_alert');
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
            'customerName' => $this->customerName,
            'addAddressUrl' => $this->addAddressUrl,
        ];
    }
}
