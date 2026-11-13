<?php

namespace App\Support;

use App\Models\Lesson;
use Carbon\Carbon;

/**
 * Builds the calendar event (iCalendar .ics) for a lesson.
 *
 * Emailed as an invitation, Gmail adds it to Google Calendar automatically (and Outlook / Apple
 * Calendar offer to add it), so the phone shows the calendar's own alert before the lesson.
 * The same UID is kept for the whole life of the lesson; SEQUENCE grows on every change so the
 * calendar updates or removes the existing event instead of creating a duplicate.
 */
class LessonCalendar
{
    /** Lesson start, in the school's timezone (lessons are stored as school local time). */
    public static function start(Lesson $lesson): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $lesson->starts_at->format('Y-m-d H:i:s'), config('app.school_timezone'));
    }

    public static function end(Lesson $lesson): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $lesson->ends_at->format('Y-m-d H:i:s'), config('app.school_timezone'));
    }

    public static function uid(Lesson $lesson): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'drivingadmin';

        return 'lesson-' . $lesson->id . '@' . $host;
    }

    public static function title(Lesson $lesson, string $role): string
    {
        return $role === 'instructor'
            ? 'Clase de manejo con ' . self::studentName($lesson)
            : 'Clase de manejo - ' . config('app.name');
    }

    public static function description(Lesson $lesson, string $role): string
    {
        $lines = [
            $role === 'instructor'
                ? 'Estudiante: ' . self::studentName($lesson) . ($lesson->student?->phone ? ' (' . $lesson->student->phone . ')' : '')
                : 'Instructor: ' . self::instructorName($lesson) . ($lesson->instructor?->phone ? ' (' . $lesson->instructor->phone . ')' : ''),
            'Duración: ' . $lesson->duration_minutes . ' min',
        ];

        if ($lesson->vehicle) {
            $lines[] = 'Vehículo: ' . $lesson->vehicle->brand . ' [' . $lesson->vehicle->plate . ']';
        }
        if ($lesson->notes) {
            $lines[] = 'Notas: ' . $lesson->notes;
        }
        $lines[] = 'Referencia: CLS-' . str_pad($lesson->id, 4, '0', STR_PAD_LEFT);

        return implode("\n", $lines);
    }

    /**
     * @param string $method 'REQUEST' (new or changed event) or 'CANCEL' (remove it)
     */
    public static function ics(Lesson $lesson, string $role, string $attendeeEmail, string $method = 'REQUEST'): string
    {
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name') ?: config('app.name');
        $cancel = $method === 'CANCEL';

        $event = [
            'BEGIN:VEVENT',
            'UID:' . self::uid($lesson),
            'SEQUENCE:' . (int) $lesson->calendar_sequence,
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . self::start($lesson)->utc()->format('Ymd\THis\Z'),
            'DTEND:' . self::end($lesson)->utc()->format('Ymd\THis\Z'),
            'SUMMARY:' . self::escape(($cancel ? 'CANCELADA: ' : '') . self::title($lesson, $role)),
            'DESCRIPTION:' . self::escape(self::description($lesson, $role)),
        ];

        if ($address = config('app.school_address')) {
            $event[] = 'LOCATION:' . self::escape($address);
        }

        $event[] = 'ORGANIZER;CN=' . self::param($fromName) . ':mailto:' . $fromEmail;
        $event[] = 'ATTENDEE;CN=' . self::param($role === 'instructor' ? self::instructorName($lesson) : self::studentName($lesson))
            . ';ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE:mailto:' . $attendeeEmail;
        $event[] = 'STATUS:' . ($cancel ? 'CANCELLED' : 'CONFIRMED');

        if (!$cancel) {
            // Phone alerts: the day before and one hour before
            foreach (['-P1D', '-PT1H'] as $trigger) {
                array_push($event, 'BEGIN:VALARM', 'ACTION:DISPLAY', 'DESCRIPTION:' . self::escape(self::title($lesson, $role)), 'TRIGGER:' . $trigger, 'END:VALARM');
            }
        }

        $event[] = 'END:VEVENT';

        $lines = array_merge([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' . self::escape(config('app.name')) . '//Agenda de Clases//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:' . $method,
        ], $event, ['END:VCALENDAR']);

        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    /** "Add to Google Calendar" link, for recipients whose calendar didn't import the invitation. */
    public static function googleCalendarUrl(Lesson $lesson, string $role): string
    {
        $params = [
            'action' => 'TEMPLATE',
            'text' => self::title($lesson, $role),
            'dates' => self::start($lesson)->utc()->format('Ymd\THis\Z') . '/' . self::end($lesson)->utc()->format('Ymd\THis\Z'),
            'details' => self::description($lesson, $role),
        ];
        if ($address = config('app.school_address')) {
            $params['location'] = $address;
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public static function studentName(Lesson $lesson): string
    {
        return $lesson->student ? trim($lesson->student->first_name . ' ' . $lesson->student->last_name) : 'Estudiante';
    }

    public static function instructorName(Lesson $lesson): string
    {
        return $lesson->instructor ? trim($lesson->instructor->first_name . ' ' . $lesson->instructor->last_name) : 'Instructor';
    }

    /** Escape a TEXT value (RFC 5545 §3.3.11). */
    private static function escape(string $text): string
    {
        return str_replace(["\\", ';', ',', "\r\n", "\n"], ["\\\\", '\;', '\,', '\n', '\n'], $text);
    }

    /** Quote a parameter value (e.g. CN) so names with commas or colons stay valid. */
    private static function param(string $value): string
    {
        return '"' . str_replace('"', '', $value) . '"';
    }

    /** Lines longer than 75 octets must be folded (RFC 5545 §3.1), without splitting UTF-8 characters. */
    private static function fold(string $line): string
    {
        $out = '';
        $current = '';
        foreach (mb_str_split($line) as $char) {
            if (strlen($current . $char) > 74) {
                $out .= $current . "\r\n ";
                $current = '';
            }
            $current .= $char;
        }

        return $out . $current;
    }
}
