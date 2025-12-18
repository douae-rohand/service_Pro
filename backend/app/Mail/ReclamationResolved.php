<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReclamationResolved extends Mailable
{
    use Queueable, SerializesModels;

    public $reclamation;
    public $recipient;
    public $recipientType;
    public $adminNotes;
    public $raison;
    public $signaleParName;
    public $concernantName;

    /**
     * Create a new message instance.
     */
    public function __construct($reclamation, $recipient, $recipientType, $adminNotes, $raison, $signaleParName, $concernantName = null)
    {
        $this->reclamation = $reclamation;
        $this->recipient = $recipient;
        $this->recipientType = $recipientType;
        $this->adminNotes = $adminNotes;
        $this->raison = $raison;
        $this->signaleParName = $signaleParName;
        $this->concernantName = $concernantName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('✅ Votre réclamation a été résolue - Service Pro')
                    ->view('emails.reclamation-resolved')
                    ->with([
                        'reclamation' => $this->reclamation,
                        'recipient' => $this->recipient,
                        'recipientType' => $this->recipientType,
                        'adminNotes' => $this->adminNotes,
                        'raison' => $this->raison,
                        'signaleParName' => $this->signaleParName,
                        'concernantName' => $this->concernantName,
                    ]);
    }
}

