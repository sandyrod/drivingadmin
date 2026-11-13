<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Notifications\LessonNotification;
use App\Services\LessonNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Reminds students and instructors of their upcoming lessons (email + SMS).
 * Runs every 15 minutes from the scheduler; each lesson is reminded only once
 * (again if it is rescheduled).
 */
class SendLessonReminders extends Command
{
    protected $signature = 'lessons:send-reminders';

    protected $description = 'Send reminders for scheduled lessons starting within the next LESSON_REMINDER_HOURS hours';

    public function handle(LessonNotifier $notifier): int
    {
        // Lessons are stored in the school's local time
        $now = Carbon::now(config('app.school_timezone'));
        $until = $now->copy()->addHours(max(1, (int) config('app.lesson_reminder_hours')));

        $lessons = Lesson::with(['student', 'instructor', 'vehicle'])
            ->where('status', Lesson::STATUS_SCHEDULED)
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '>', $now->format('Y-m-d H:i:s'))
            ->where('starts_at', '<=', $until->format('Y-m-d H:i:s'))
            ->orderBy('starts_at')
            ->get();

        foreach ($lessons as $lesson) {
            $result = $notifier->notify($lesson, LessonNotification::REMINDER);
            $lesson->forceFill(['reminder_sent_at' => now()])->save();

            $this->line(sprintf('CLS-%04d: enviado a %s%s', $lesson->id,
                implode(', ', $result['sent']) ?: 'nadie',
                $result['failed'] ? ' | sin enviar: ' . implode(', ', $result['failed']) : ''));
        }

        $this->info($lessons->count() . ' recordatorio(s) procesado(s).');

        return self::SUCCESS;
    }
}
