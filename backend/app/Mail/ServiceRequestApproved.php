<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $intervenant;
    public $services;

    /**
     * Create a new message instance.
     */
    public function __construct($intervenant, $services)
    {
        $this->intervenant = $intervenant;
        $this->services = $services;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $servicesList = is_array($this->services) ? implode(', ', $this->services) : $this->services;
        
        return $this->subject('Votre demande de service a été acceptée - Service Pro')
                    ->view('emails.service-request-approved')
                    ->with([
                        'intervenant' => $this->intervenant,
                        'services' => $this->services,
                        'servicesList' => $servicesList
                    ]);
    }
}
