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
            ->greeting(__('Hola :name,', ['name' => $name]))
            ->line($this->intro())
            ->line('**' . __('Fecha y hora') . ':** ' . $when);

        foreach (explode("\n", LessonCalendar::description($lesson, $this->role, false)) as $line) {
            [$label, $value] = array_pad(explode(': ', $line, 2), 2, '');
            $mail->line('**' . $label . ':** ' . $value);
        }

        if ($lesson->isRoadTest() && $lesson->location) {
            $mail->line('**' . __('Lugar del examen') . ':** ' . $lesson->location)
                ->line('[' . __('Abrir en Google Maps') . '](' . \App\Models\Lesson::googleMapsUrl($lesson->location) . ') · '
                    . '[' . __('Abrir en Apple Maps (iPhone)') . '](' . \App\Models\Lesson::appleMapsUrl($lesson->location) . ')');
        } elseif ($address = config('app.school_address')) {
            $mail->line('**' . __('Lugar') . ':** ' . $address);
        }

        // Instructors: link to mark the lesson as completed after it
        if ($this->role === 'instructor' && !$cancel && $lesson->instructor) {
            $mail->line(__('Al terminar la clase márcala como completada en [Mis clases](:url).', ['url' => $lesson->instructor->portalUrl()]));
        }

        $mail->salutation(config('app.name'));

        // Reminders are about a lesson that is already in the calendar: no new invitation
        if (in_array($this->event, [self::REMINDER, self::FINAL_REMINDER], true)) {
            return $mail;
        }

        if ($cancel) {
            $mail->line(__('El evento se eliminará de tu calendario.'));
        } else {
            $mail->line(__('Esta clase ya está en el archivo de calendario adjunto. Si usas Gmail se agrega solo a Google Calendar y tu teléfono te avisará antes de la clase.'))
                ->action(__('Agregar a Google Calendar'), LessonCalendar::googleCalendarUrl($lesson, $this->role));
        }

        $email = $notifiable->routeNotificationFor('mail', $this);
        $method = $cancel ? 'CANCEL' : 'REQUEST';

        return $mail
            ->attachData(LessonCalendar::ics($lesson, $this->role, $email, $method), __('clase.ics'), [
                'mime' => 'text/calendar; charset=UTF-8; method=' . $method,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        // Instructors read "con <student>", students "con el instructor <instructor>".
        // (Full sentences per role: a key starting with "con :" can't be used, Windows treats "con" as a device name.)
        $toInstructor = $this->role === 'instructor';
        $name = $toInstructor ? LessonCalendar::studentName($this->lesson) : LessonCalendar::instructorName($this->lesson);

        $what = $this->what();
        $place = $this->lesson->isRoadTest() && $this->lesson->location
            ? ' ' . __('Lugar') . ': ' . $this->lesson->location . ' ' . \App\Models\Lesson::googleMapsUrl($this->lesson->location)
            : '';
        $when = $this->whenText();

        return config('app.name') . ': ' . match ($this->event) {
            self::SCHEDULED => __($toInstructor ? ':what :done con :name el :when.' : ':what :done con el instructor :name el :when.',
                ['what' => ucfirst($what), 'done' => __($this->g('agendada', 'agendado')), 'name' => $name, 'when' => $when]) . $place,
            self::RESCHEDULED => __($toInstructor ? ':done: :what con :name para el :when.' : ':done: :what con el instructor :name para el :when.',
                ['done' => ucfirst(__($this->g('reprogramada', 'reprogramado'))), 'what' => $what, 'name' => $name, 'when' => $when]) . $place,
            self::CANCELLED => __($toInstructor ? ':done: :what con :name del :when.' : ':done: :what con el instructor :name del :when.',
                ['done' => ucfirst(__($this->g('cancelada', 'cancelado'))), 'what' => $what, 'name' => $name, 'when' => $when]),
            self::FINAL_REMINDER => __($toInstructor ? ':what con :name empieza en :minutes minutos (:time).' : ':what con el instructor :name empieza en :minutes minutos (:time).',
                ['what' => ucfirst($what), 'name' => $name, 'minutes' => $this->minutesLeft(), 'time' => $this->startTime()]) . $place,
            default => __($toInstructor ? 'Recordatorio: :what con :name el :when.' : 'Recordatorio: :what con el instructor :name el :when.',
                ['what' => $what, 'name' => $name, 'when' => $when]) . $place,
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
        return $this->lesson->isRoadTest() ? 'Road Test' : __('clase de manejo');
    }

    private function subject(): string
    {
        $what = ucfirst($this->what());

        return match ($this->event) {
            self::SCHEDULED => __(':what :done - :when', ['what' => $what, 'done' => __($this->g('agendada', 'agendado')), 'when' => $this->whenText()]),
            self::RESCHEDULED => __(':what :done - :when', ['what' => $what, 'done' => __($this->g('reprogramada', 'reprogramado')), 'when' => $this->whenText()]),
            self::CANCELLED => __(':what :done - :when', ['what' => $what, 'done' => __($this->g('cancelada', 'cancelado')), 'when' => $this->whenText()]),
            self::FINAL_REMINDER => __(':what: empieza en :minutes minutos', ['what' => $what, 'minutes' => $this->minutesLeft()]),
            default => __('Recordatorio: :what :when', ['what' => $this->what(), 'when' => $this->whenText()]),
        };
    }

    private function intro(): string
    {
        if ($this->lesson->isRoadTest()) {
            return match ($this->event) {
                self::SCHEDULED => $this->role === 'instructor' ? __('Se te asignó un Road Test (examen de manejo).') : __('Tu Road Test (examen de manejo) quedó agendado.'),
                self::RESCHEDULED => __('El Road Test cambió. Estos son los nuevos datos:'),
                self::CANCELLED => __('El siguiente Road Test fue cancelado:'),
                self::FINAL_REMINDER => __('Tu Road Test empieza en :minutes minutos.', ['minutes' => $this->minutesLeft()]),
                default => __('Te recordamos que tienes un Road Test (examen de manejo) próximamente.'),
            };
        }

        return match ($this->event) {
            self::SCHEDULED => $this->role === 'instructor' ? __('Se te asignó una nueva clase de manejo.') : __('Tu clase de manejo quedó agendada.'),
            self::RESCHEDULED => __('La clase de manejo cambió. Estos son los nuevos datos:'),
            self::CANCELLED => __('La siguiente clase de manejo fue cancelada:'),
            self::FINAL_REMINDER => __('Tu clase de manejo empieza en :minutes minutos.', ['minutes' => $this->minutesLeft()]),
            default => __('Te recordamos que tienes una clase de manejo próximamente.'),
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

    /** e.g. "lunes 16/11/2026, 10:00 AM - 10:45 AM" in the school's timezone, in the recipient's language */
    private function whenText(): string
    {
        $locale = app()->getLocale();
        $start = LessonCalendar::start($this->lesson)->locale($locale);
        $end = LessonCalendar::end($this->lesson);

        // US date order for English ("Monday 11/16/2026")
        return $start->isoFormat($locale === 'en' ? 'dddd MM/DD/YYYY' : 'dddd DD/MM/YYYY') . ', ' . $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }
}
