<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WelcomeFromCeoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $deviceinfo;
    public $ipaddress;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $deviceinfo, $ipaddress)
    {
        $this->user = $user;
        $this->deviceinfo = $deviceinfo;
        $this->ipaddress = $ipaddress;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome From Ceo Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome.fromceo',
        );
    }

    public function build()
    {
        return $this
            ->from('nathan@venusnap.com', 'Nathan Mwamba')
            ->subject('Welcome to Venusnap 🎉')
            ->markdown('emails.welcome.fromceo')
            ->with(['user' => $this->user]);
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
