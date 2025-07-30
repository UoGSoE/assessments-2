<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public $complaints;

    public function __construct($complaints)
    {
        $this->complaints = $complaints;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue Feedback',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overdue_feedback',
            with: [
                'complaints' => $this->complaints,
            ],
        );
    }

}
