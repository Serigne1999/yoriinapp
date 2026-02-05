<?php

namespace Modules\Superadmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBusinessNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($business)
    {
        $this->business = $business;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $details = '🏢 Nom du business : ' . $this->business->name . "\n"
             . '👤 Propriétaire : ' . $this->business->owner->user_full_name . "\n"
             . '📧 Email : ' . $this->business->owner->email . "\n"
             . '📞 Téléphone : ' . ($this->business->locations->first()->mobile ?? 'Non renseigné');

        return (new MailMessage)
                ->subject('🎉 Nouvelle Entreprise inscrit sur Yoriin App')
                ->greeting('Bonjour 👋!')
                ->line('Un nouveau compte vient d\'être enregistré avec succès sur la plateforme.')
                ->line($details);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
