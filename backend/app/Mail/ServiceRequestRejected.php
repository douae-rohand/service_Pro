<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $intervenant;
    public $services;

    /**
     * Create a new message instance.
     */
    public function __construct($intervenant, $services = [])
    {
        $this->intervenant = $intervenant;
        $this->services = $services;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $servicesList = !empty($this->services) && is_array($this->services) 
            ? implode(', ', $this->services) 
            : (!empty($this->services) ? $this->services : 'service');
        
        return $this->subject('Votre demande de service a été refusée - Verde Net')
                    ->view('emails.service-request-rejected')
                    ->with([
                        'intervenant' => $this->intervenant,
                        'services' => $this->services,
                        'servicesList' => $servicesList
                    ]);
    }
}
