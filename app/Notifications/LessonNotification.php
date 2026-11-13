<?php

namespace App\Notifications;

use App\Models\Lesson;
use App\Notifications\Channels\SmsChannel;
use App\Support\LessonCalendar;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student or an instructor about one of their lessons.
 *
 * Email carries a calendar invitation (.ics) so the lesson lands in Google Calendar / the phone's
 * calendar; SMS (when Twilio is configured) carries a short text.
 */
class LessonNotification extends Notification
{
    public const SCHEDULED = 'scheduled';
    public const RESCHEDULED = 'rescheduled';
    public const CANCELLED = 'cancelled';
    public const REMINDER = 'reminder';

    /**
     * @param string $event one of the constants above
     * @param string $role 'student' or 'instructor'
     */
    public function __construct(
        public Lesson $lesson,
        public string $event,
        public string $role,
    ) {
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
        $lesson = $this->lesson;
        $when = $this->whenText();
        $cancel = $this->event === self::CANCELLED;
        $name = $this->role === 'instructor' ? LessonCalendar::instructorName($lesson) : LessonCalendar::studentName($lesson);

        $mail = (new MailMessage)
            ->subject($this->subject())
            ->greeting('Hola ' . $name . ',')
            ->line($this->intro())
            ->line('**Fecha y hora:** ' . $when);

        foreach (explode("\n", LessonCalendar::description($lesson, $this->role)) as $line) {
            [$label, $value] = array_pad(explode(': ', $line, 2), 2, '');
            $mail->line('**' . $label . ':** ' . $value);
        }

        if ($address = config('app.school_address')) {
            $mail->line('**Lugar:** ' . $address);
        }

        if ($cancel) {
            $mail->line('El evento se eliminará de tu calendario.');
        } else {
            $mail->line('Esta clase ya está en el archivo de calendario adjunto. Si usas Gmail se agrega solo a Google Calendar y tu teléfono te avisará antes de la clase.')
                ->action('Agregar a Google Calendar', LessonCalendar::googleCalendarUrl($lesson, $this->role));
        }

        $email = $notifiable->routeNotificationFor('mail', $this);
        $method = $cancel ? 'CANCEL' : 'REQUEST';

        return $mail
            ->salutation(config('app.name'))
            ->attachData(LessonCalendar::ics($lesson, $this->role, $email, $method), 'clase.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=' . $method,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $who = $this->role === 'instructor'
            ? 'con ' . LessonCalendar::studentName($this->lesson)
            : 'con el instructor ' . LessonCalendar::instructorName($this->lesson);

        return config('app.name') . ': ' . match ($this->event) {
            self::SCHEDULED => 'Clase de manejo agendada ' . $who . ' el ' . $this->whenText() . '.',
            self::RESCHEDULED => 'Tu clase de manejo fue reprogramada ' . $who . ' para el ' . $this->whenText() . '.',
            self::CANCELLED => 'Tu clase de manejo ' . $who . ' del ' . $this->whenText() . ' fue cancelada.',
            default => 'Recordatorio: tienes clase de manejo ' . $who . ' el ' . $this->whenText() . '.',
        };
    }

    private function subject(): string
    {
        return match ($this->event) {
            self::SCHEDULED => 'Clase de manejo agendada - ' . $this->whenText(),
            self::RESCHEDULED => 'Clase de manejo reprogramada - ' . $this->whenText(),
            self::CANCELLED => 'Clase de manejo cancelada - ' . $this->whenText(),
            default => 'Recordatorio: clase de manejo ' . $this->whenText(),
        };
    }

    private function intro(): string
    {
        return match ($this->event) {
            self::SCHEDULED => $this->role === 'instructor' ? 'Se te asignó una nueva clase de manejo.' : 'Tu clase de manejo quedó agendada.',
            self::RESCHEDULED => 'La clase de manejo cambió. Estos son los nuevos datos:',
            self::CANCELLED => 'La siguiente clase de manejo fue cancelada:',
            default => 'Te recordamos que tienes una clase de manejo próximamente.',
        };
    }

    /** e.g. "lunes 16/11/2026, 10:00 a. m. - 10:45 a. m." in the school's timezone */
    private function whenText(): string
    {
        $start = LessonCalendar::start($this->lesson)->locale('es');
        $end = LessonCalendar::end($this->lesson);

        return $start->isoFormat('dddd DD/MM/YYYY') . ', ' . $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }
}
