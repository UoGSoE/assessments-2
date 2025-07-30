<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProblematicAssessment extends Mailable
{
    use Queueable, SerializesModels;

    public $assessment;

    public function __construct($assessment)
    {
        $this->assessment = $assessment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Problematic Assessment',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.problematic_assessment',
            with: [
                'assessment' => $this->assessment,
            ],
        );
    }

}
