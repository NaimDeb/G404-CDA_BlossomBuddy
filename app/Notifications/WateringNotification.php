<?php

namespace App\Notifications;

use App\Models\UserPlant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WateringNotification extends Notification
{
    use Queueable;

    private UserPlant $userPlant;

    /**
     * Create a new notification instance.
     */
    public function __construct($userPlant)
    {
        $this->userPlant = $userPlant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $userName = $this->userPlant->user->name;
        $plantName = $this->userPlant->plant->common_name;

        return (new MailMessage)
            ->subject("Bonjour $userName ,Arrosage requis")
            ->line("Ta plante $plantName a besoin d'eau 🌿")
            ->action('Voir mes plantes', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
