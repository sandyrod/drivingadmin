<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Student;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\LessonNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Sends lesson notifications to the student and the instructor.
 *
 * A failed email or SMS never breaks the booking: errors are logged and reported back
 * so the dashboard can tell the user who could not be notified.
 */
class LessonNotifier
{
    /**
     * Notify the lesson's student and/or instructor.
     *
     * @param string[] $roles 'student', 'instructor'
     * @return array{sent: string[], failed: string[]}
     */
    public function notify(Lesson $lesson, string $event, array $roles = ['student', 'instructor']): array
    {
        $lesson->loadMissing(['student', 'instructor', 'vehicle']);

        $result = ['sent' => [], 'failed' => []];
        foreach ($roles as $role) {
            $this->send($lesson, $event, $role, $role === 'instructor' ? $lesson->instructor : $lesson->student, $result);
        }

        return $result;
    }

    public static function merge(array ...$results): array
    {
        return [
            'sent' => array_values(array_merge(...array_column($results, 'sent'))),
            'failed' => array_values(array_merge(...array_column($results, 'failed'))),
        ];
    }

    private function send(Lesson $lesson, string $event, string $role, Student|Instructor|null $person, array &$result): void
    {
        if (!$person) {
            return;
        }

        $who = $role === 'instructor' ? 'instructor' : 'estudiante';
        $email = filter_var($person->email, FILTER_VALIDATE_EMAIL) ?: null;
        $phone = SmsChannel::isConfigured() ? SmsChannel::normalize($person->phone) : null;

        if (!$email && !$phone) {
            $result['failed'][] = "{$who} (sin correo ni teléfono válido)";
            return;
        }

        $route = Notification::route('mail', $email);
        if ($phone) {
            $route->route('sms', $phone);
        }

        try {
            $route->notifyNow(new LessonNotification($lesson, $event, $role));
            $result['sent'][] = $who . ' (' . implode(' + ', array_filter([$email ? 'correo' : null, $phone ? 'SMS' : null])) . ')';
        } catch (\Throwable $e) {
            Log::error("Lesson {$lesson->id}: could not notify {$role} ({$event}): " . $e->getMessage());
            $result['failed'][] = $who;
        }
    }
}
