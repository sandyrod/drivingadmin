<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Notifications\LessonNotification;
use App\Services\LessonNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Reminds students and instructors of their upcoming lessons (email + SMS), twice:
 *  - LESSON_REMINDER_HOURS before (default 24 h);
 *  - LESSON_FINAL_REMINDER_MINUTES before (default 10 min).
 *
 * Runs every minute from the scheduler. Each reminder is sent once per lesson (again if the
 * lesson is rescheduled). When a lesson is already inside the last-minute window, only the
 * last-minute reminder is sent.
 */
class SendLessonReminders extends Command
{
    protected $signature = 'lessons:send-reminders';

    protected $description = 'Send the day-before and last-minute reminders for scheduled lessons';

    public function handle(LessonNotifier $notifier): int
    {
        // Lessons are stored in the school's local time
        $now = Carbon::now(config('app.school_timezone'));
        $finalMinutes = max(0, (int) config('app.lesson_final_reminder_minutes'));
        $count = 0;

        if ($finalMinutes > 0) {
            $finalUntil = $now->copy()->addMinutes($finalMinutes)->format('Y-m-d H:i:s');

            foreach ($this->upcoming($now, $finalUntil, 'final_reminder_sent_at') as $lesson) {
                $this->send($notifier, $lesson, LessonNotification::FINAL_REMINDER);
                // The day-before reminder would arrive after this one, so it is not sent anymore
                $lesson->forceFill(['final_reminder_sent_at' => now(), 'reminder_sent_at' => $lesson->reminder_sent_at ?? now()])->save();
                $count++;
            }
        }

        $until = $now->copy()->addHours(max(1, (int) config('app.lesson_reminder_hours')))->format('Y-m-d H:i:s');

        foreach ($this->upcoming($now, $until, 'reminder_sent_at') as $lesson) {
            $this->send($notifier, $lesson, LessonNotification::REMINDER);
            $lesson->forceFill(['reminder_sent_at' => now()])->save();
            $count++;
        }

        $this->info($count . ' recordatorio(s) procesado(s).');

        return self::SUCCESS;
    }

    private function upcoming(Carbon $now, string $until, string $sentColumn)
    {
        return Lesson::with(['student', 'instructor', 'vehicle'])
            ->where('status', Lesson::STATUS_SCHEDULED)
            ->whereNull($sentColumn)
            ->where('starts_at', '>', $now->format('Y-m-d H:i:s'))
            ->where('starts_at', '<=', $until)
            ->orderBy('starts_at')
            ->get();
    }

    private function send(LessonNotifier $notifier, Lesson $lesson, string $event): void
    {
        $result = $notifier->notify($lesson, $event);

        $this->line(sprintf('CLS-%04d (%s): enviado a %s%s', $lesson->id, $event,
            implode(', ', $result['sent']) ?: 'nadie',
            $result['failed'] ? ' | sin enviar: ' . implode(', ', $result['failed']) : ''));
    }
}
