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
    public const FINAL_REMINDER = 'final_reminder';

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

        foreach (explode("\n", LessonCalendar::description($lesson, $this->role, false)) as $line) {
            [$label, $value] = array_pad(explode(': ', $line, 2), 2, '');
            $mail->line('**' . $label . ':** ' . $value);
        }

        if ($lesson->isRoadTest() && $lesson->location) {
            $mail->line('**Lugar del examen:** ' . $lesson->location)
                ->line('[Abrir en Google Maps](' . \App\Models\Lesson::googleMapsUrl($lesson->location) . ') · '
                    . '[Abrir en Apple Maps (iPhone)](' . \App\Models\Lesson::appleMapsUrl($lesson->location) . ')');
        } elseif ($address = config('app.school_address')) {
            $mail->line('**Lugar:** ' . $address);
        }

        // Instructors: link to mark the lesson as completed after it
        if ($this->role === 'instructor' && !$cancel && $lesson->instructor) {
            $mail->line('Al terminar la clase márcala como completada en [Mis clases](' . $lesson->instructor->portalUrl() . ').');
        }

        $mail->salutation(config('app.name'));

        // Reminders are about a lesson that is already in the calendar: no new invitation
        if (in_array($this->event, [self::REMINDER, self::FINAL_REMINDER], true)) {
            return $mail;
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
            ->attachData(LessonCalendar::ics($lesson, $this->role, $email, $method), 'clase.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=' . $method,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $who = $this->role === 'instructor'
            ? 'con ' . LessonCalendar::studentName($this->lesson)
            : 'con el instructor ' . LessonCalendar::instructorName($this->lesson);

        $what = $this->what();
        $place = $this->lesson->isRoadTest() && $this->lesson->location
            ? ' Lugar: ' . $this->lesson->location . ' ' . \App\Models\Lesson::googleMapsUrl($this->lesson->location)
            : '';

        return config('app.name') . ': ' . match ($this->event) {
            self::SCHEDULED => ucfirst($what) . ' ' . $this->g('agendada', 'agendado') . ' ' . $who . ' el ' . $this->whenText() . '.' . $place,
            self::RESCHEDULED => ucfirst($this->g('reprogramada', 'reprogramado')) . ': ' . $what . ' ' . $who . ' para el ' . $this->whenText() . '.' . $place,
            self::CANCELLED => ucfirst($this->g('cancelada', 'cancelado')) . ': ' . $what . ' ' . $who . ' del ' . $this->whenText() . '.',
            self::FINAL_REMINDER => ucfirst($what) . ' ' . $who . ' empieza en ' . $this->minutesLeft() . ' minutos (' . $this->startTime() . ').' . $place,
            default => 'Recordatorio: ' . $what . ' ' . $who . ' el ' . $this->whenText() . '.' . $place,
        };
    }

    /** Spanish agreement: "clase" is feminine, "Road Test" masculine. */
    private function g(string $feminine, string $masculine): string
    {
        return $this->lesson->isRoadTest() ? $masculine : $feminine;
    }

    /** "clase de manejo" or "Road Test" */
    private function what(): string
    {
        return $this->lesson->isRoadTest() ? 'Road Test' : 'clase de manejo';
    }

    private function subject(): string
    {
        $what = ucfirst($this->what());

        return match ($this->event) {
            self::SCHEDULED => $what . ' ' . $this->g('agendada', 'agendado') . ' - ' . $this->whenText(),
            self::RESCHEDULED => $what . ' ' . $this->g('reprogramada', 'reprogramado') . ' - ' . $this->whenText(),
            self::CANCELLED => $what . ' ' . $this->g('cancelada', 'cancelado') . ' - ' . $this->whenText(),
            self::FINAL_REMINDER => $what . ': empieza en ' . $this->minutesLeft() . ' minutos',
            default => 'Recordatorio: ' . $this->what() . ' ' . $this->whenText(),
        };
    }

    private function intro(): string
    {
        if ($this->lesson->isRoadTest()) {
            return match ($this->event) {
                self::SCHEDULED => $this->role === 'instructor' ? 'Se te asignó un Road Test (examen de manejo).' : 'Tu Road Test (examen de manejo) quedó agendado.',
                self::RESCHEDULED => 'El Road Test cambió. Estos son los nuevos datos:',
                self::CANCELLED => 'El siguiente Road Test fue cancelado:',
                self::FINAL_REMINDER => 'Tu Road Test empieza en ' . $this->minutesLeft() . ' minutos.',
                default => 'Te recordamos que tienes un Road Test (examen de manejo) próximamente.',
            };
        }

        return match ($this->event) {
            self::SCHEDULED => $this->role === 'instructor' ? 'Se te asignó una nueva clase de manejo.' : 'Tu clase de manejo quedó agendada.',
            self::RESCHEDULED => 'La clase de manejo cambió. Estos son los nuevos datos:',
            self::CANCELLED => 'La siguiente clase de manejo fue cancelada:',
            self::FINAL_REMINDER => 'Tu clase de manejo empieza en ' . $this->minutesLeft() . ' minutos.',
            default => 'Te recordamos que tienes una clase de manejo próximamente.',
        };
    }

    /** Whole minutes until the lesson starts (at least 1). */
    private function minutesLeft(): int
    {
        return max(1, (int) ceil(now()->diffInSeconds(LessonCalendar::start($this->lesson), false) / 60));
    }

    private function startTime(): string
    {
        return LessonCalendar::start($this->lesson)->format('g:i A');
    }

    /** e.g. "lunes 16/11/2026, 10:00 a. m. - 10:45 a. m." in the school's timezone */
    private function whenText(): string
    {
        $start = LessonCalendar::start($this->lesson)->locale('es');
        $end = LessonCalendar::end($this->lesson);

        return $start->isoFormat('dddd DD/MM/YYYY') . ', ' . $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }
}
