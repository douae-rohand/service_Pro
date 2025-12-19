<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $intervention;

    /**
     * Create a new notification instance.
     */
    public function __construct($intervention)
    {
        $this->intervention = $intervention;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'N\'oubliez pas d\'évaluer votre intervention du ' . $this->intervention->date_intervention->format('d/m/Y'),
            'intervention_id' => $this->intervention->id,
            'type' => 'evaluation_reminder',
            'link' => '/client-dashboard' 
        ];
    }
}
