<?php

namespace App\Notifications;

use App\Models\Instructor;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends the instructor their personal "Mis clases" link.
 */
class InstructorPortalLink extends Notification
{
    public function __construct(private Instructor $instructor, private string $url)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = [];
        if ($notifiable->routeNotificationFor('mail', $this)) {
            $channels[] = 'mail';
        }
        if (SmsChannel::isConfigured() && SmsChannel::normalize($notifiable->routeNotificationFor('sms', $this))) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Tu enlace "Mis clases" - :school', ['school' => config('app.school_name')]))
            ->greeting(__('Hola :name,', ['name' => trim($this->instructor->first_name . ' ' . $this->instructor->last_name)]))
            ->line(__('Este es tu enlace personal para ver tus clases y marcarlas como **completadas** (o "no asistió") al terminar.'))
            ->action(__('Abrir Mis clases'), $this->url)
            ->line(__('Guárdalo en tu teléfono (por ejemplo, agrégalo a la pantalla de inicio). No lo compartas: cualquiera con el enlace puede marcar tus clases.'))
            ->salutation(config('app.name'));
    }

    public function toSms(object $notifiable): string
    {
        return __(':school: tu enlace para ver y marcar tus clases: :url', ['school' => config('app.school_name'), 'url' => $this->url]);
    }
}
