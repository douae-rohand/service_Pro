<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterventionAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public $intervention;
    public $client;
    public $intervenant;

    /**
     * Create a new message instance.
     */
    public function __construct($intervention)
    {
        $this->intervention = $intervention;
        $this->client = $intervention->client->utilisateur;
        $this->intervenant = $intervention->intervenant->utilisateur;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Détails de votre nouvelle intervention - Verde Net')
                    ->view('emails.intervention-accepted')
                    ->with([
                        'intervention' => $this->intervention,
                        'client' => $this->client,
                        'intervenant' => $this->intervenant
                    ]);
    }
}
