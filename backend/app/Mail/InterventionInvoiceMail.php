<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class InterventionInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $intervention;

    /**
     * Create a new message instance.
     */
    public function __construct($intervention)
    {
        $this->intervention = $intervention;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture ServicePro est disponible',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.intervention.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.invoice', ['intervention' => $this->intervention]);
        
        return [
            Attachment::fromData(fn () => $pdf->output(), 'Facture-ServicePro-' . $this->intervention->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
