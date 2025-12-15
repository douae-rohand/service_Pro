<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceActivated extends Mailable
{
    use Queueable, SerializesModels;

    public $intervenant;
    public $service;

    /**
     * Create a new message instance.
     */
    public function __construct($intervenant, $service)
    {
        $this->intervenant = $intervenant;
        $this->service = $service;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Service activé - Votre demande peut être acceptée - Service Pro')
                    ->view('emails.service-activated')
                    ->with([
                        'intervenant' => $this->intervenant,
                        'service' => $this->service
                    ]);
    }
}
