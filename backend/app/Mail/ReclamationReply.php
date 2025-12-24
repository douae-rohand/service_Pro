<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReclamationReply extends Mailable
{
    use Queueable, SerializesModels;

    public $reclamation;
    public $recipient;
    public $recipientType;
    public $adminNotes;
    public $priorite;
    public $statut;
    public $raison;

    /**
     * Create a new message instance.
     */
    public function __construct($reclamation, $recipient, $recipientType, $adminNotes, $priorite, $statut, $raison)
    {
        $this->reclamation = $reclamation;
        $this->recipient = $recipient;
        $this->recipientType = $recipientType;
        $this->adminNotes = $adminNotes;
        $this->priorite = $priorite;
        $this->statut = $statut;
        $this->raison = $raison;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Réponse à votre réclamation - Verde Net')
                    ->view('emails.reclamation-reply')
                    ->with([
                        'reclamation' => $this->reclamation,
                        'recipient' => $this->recipient,
                        'recipientType' => $this->recipientType,
                        'adminNotes' => $this->adminNotes,
                        'priorite' => $this->priorite,
                        'statut' => $this->statut,
                        'raison' => $this->raison,
                    ]);
    }
}
