<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Student;
use App\Notifications\LessonNotification;
use App\Services\LessonNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    /**
     * Book a driving lesson or a road test for a student.
     *
     * Rules:
     *  - driving lessons: the student must have a package with lessons left;
     *  - road tests: need the test site; they don't use package lessons and need at least
     *    ROAD_TEST_GAP_MINUTES free before and after (instructor and student);
     *  - it can't be in the past nor outside the instructor's working schedule;
     *  - neither the instructor nor the student can have another lesson at the same time.
     */
    public function store(Request $request)
    {
        $type = $request->input('type') === Lesson::TYPE_ROAD_TEST ? Lesson::TYPE_ROAD_TEST : Lesson::TYPE_LESSON;

        $validated = $request->validate(array_merge([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->whereNull('deleted_at')],
            'type' => ['nullable', Rule::in([Lesson::TYPE_LESSON, Lesson::TYPE_ROAD_TEST])],
        ], $this->slotRules($type)));

        return $this->saveLesson($validated, (int) $validated['student_id'], $type);
    }

    /**
     * Reschedule a lesson: change instructor, date, time, duration or notes.
     * Only lessons that are still "scheduled" can be rescheduled; the same rules as booking apply
     * (the lesson's own time and hours are not counted against itself).
     */
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $type = $lesson->type ?: Lesson::TYPE_LESSON; // a lesson doesn't change type when rescheduled

        $validated = $request->validate($this->slotRules($type));

        if ($lesson->status !== Lesson::STATUS_SCHEDULED) {
            return $this->fail(__('Solo se pueden reprogramar clases que estén programadas.'));
        }

        return $this->saveLesson($validated, $lesson->student_id, $type, $lesson);
    }

    public function __construct(private LessonNotifier $notifier)
    {
    }

    private function slotRules(string $type): array
    {
        $roadTest = $type === Lesson::TYPE_ROAD_TEST;

        return [
            'instructor_id' => ['required', 'integer', Rule::exists('instructors', 'id')->whereNull('deleted_at')],
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'duration_minutes' => ['required', 'integer', Rule::in($roadTest ? Lesson::ROAD_TEST_DURATIONS : Lesson::DURATIONS)],
            'location' => $roadTest ? 'required|string|max:255' : 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Create a lesson, or move an existing one ($lesson), after checking every booking rule.
     */
    private function saveLesson(array $validated, int $studentId, string $type, ?Lesson $lesson = null)
    {
        $minutes = (int) $validated['duration_minutes'];
        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['time']);
        $end = $start->copy()->addMinutes($minutes);

        $nowAtSchool = Carbon::now(config('app.school_timezone'))->format('Y-m-d H:i');
        if ($start->format('Y-m-d H:i') < $nowAtSchool) {
            return $this->fail(__('No se puede agendar una clase en una fecha u hora que ya pasó.'));
        }

        try {
            DB::beginTransaction();

            // Lock the student so two simultaneous bookings can't exceed the package
            $student = Student::with('packageModel')->lockForUpdate()->findOrFail($studentId);
            $instructor = Instructor::lockForUpdate()->findOrFail($validated['instructor_id']);

            if ($lesson) {
                // Re-read the lesson inside the transaction
                $lesson = Lesson::lockForUpdate()->findOrFail($lesson->id);
                if ($lesson->status !== Lesson::STATUS_SCHEDULED) {
                    DB::rollBack();
                    return $this->fail(__('Solo se pueden reprogramar clases que estén programadas.'));
                }
            }

            $error = $this->checkBookingRules($student, $instructor, $type, $start, $end, $minutes, $lesson);
            if ($error) {
                DB::rollBack();
                return $this->fail($error);
            }

            $data = [
                'instructor_id' => $instructor->id,
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'ends_at' => $end->format('Y-m-d H:i:s'),
                'duration_minutes' => $minutes,
                'location' => $type === Lesson::TYPE_ROAD_TEST ? trim($validated['location']) : null,
                'notes' => $validated['notes'] ?? null,
            ];

            $previous = null;
            if ($lesson) {
                // Copy of the lesson before the change, to cancel the old instructor's calendar event
                $previous = (clone $lesson)->load(['student', 'instructor', 'vehicle']);
                // Keep the vehicle unless the instructor changed
                if ($lesson->instructor_id !== $instructor->id) {
                    $data['vehicle_id'] = $instructor->vehicle_id;
                }
                // New calendar version; the reminder is sent again for the new date
                $data['calendar_sequence'] = $lesson->calendar_sequence + 1;
                $data['reminder_sent_at'] = null;
                $data['final_reminder_sent_at'] = null;
                $lesson->update($data);
            } else {
                $lesson = Lesson::create($data + [
                    'student_id' => $student->id,
                    'type' => $type,
                    'vehicle_id' => $instructor->vehicle_id, // vehicle assigned to the instructor
                    'status' => Lesson::STATUS_SCHEDULED,
                ]);
            }

            DB::commit();

            $lesson->load(['student', 'instructor', 'vehicle']);
            $notified = $this->notifyChange($lesson, $previous);

            return response()->json([
                'success' => true,
                'lesson' => $lesson->toDashboardArray(),
                'student' => ['db_id' => $student->id, 'used_minutes' => $student->usedLessonMinutes()],
                'notifications' => $notified,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $this->errorMessage($e)], 500);
        }
    }

    /**
     * Email (calendar invitation) / SMS the student and instructor about a new or rescheduled lesson.
     * When the instructor changed, the previous instructor gets a cancellation for their event.
     */
    private function notifyChange(Lesson $lesson, ?Lesson $previous): array
    {
        if (!$previous) {
            return $this->notifier->notify($lesson, LessonNotification::SCHEDULED);
        }

        if ($previous->instructor_id === $lesson->instructor_id) {
            return $this->notifier->notify($lesson, LessonNotification::RESCHEDULED);
        }

        $previous->calendar_sequence = $lesson->calendar_sequence;

        return LessonNotifier::merge(
            $this->notifier->notify($lesson, LessonNotification::RESCHEDULED, ['student']),
            $this->notifier->notify($lesson, LessonNotification::SCHEDULED, ['instructor']),
            $this->notifier->notify($previous, LessonNotification::CANCELLED, ['instructor']),
        );
    }

    /**
     * Returns an error message if the booking breaks a rule, or null if it is valid.
     * $ignore is the lesson being rescheduled (its own hours and time slot don't count).
     */
    private function checkBookingRules(Student $student, Instructor $instructor, string $type, Carbon $start, Carbon $end, int $minutes, ?Lesson $ignore): ?string
    {
        if (!$instructor->worksDuring($start, $end)) {
            return __('Ese horario está fuera del horario de trabajo de :name (:schedule).', [
                'name' => trim($instructor->first_name . ' ' . $instructor->last_name),
                'schedule' => $instructor->scheduleText(),
            ]);
        }

        $roadTest = $type === Lesson::TYPE_ROAD_TEST;

        // Road tests don't use the package lessons
        $error = $roadTest ? null : $this->checkPackage($student, $minutes, $ignore);
        if ($error) {
            return $error;
        }

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        $others = fn (string $from, string $to) => Lesson::active()->overlapping($from, $to)
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id));

        if ($others($startStr, $endStr)->where('instructor_id', $instructor->id)->exists()) {
            return __('El instructor ya tiene una clase en ese horario.');
        }

        if ($others($startStr, $endStr)->where('student_id', $student->id)->exists()) {
            return __('El estudiante ya tiene una clase en ese horario.');
        }

        // Free time around road tests: nothing can be closer than the gap to a road test
        $gap = Lesson::ROAD_TEST_GAP_MINUTES;
        $gapFrom = $start->copy()->subMinutes($gap)->format('Y-m-d H:i:s');
        $gapTo = $end->copy()->addMinutes($gap)->format('Y-m-d H:i:s');
        $tooClose = fn () => $others($gapFrom, $gapTo)
            ->when(!$roadTest, fn ($q) => $q->where('type', Lesson::TYPE_ROAD_TEST));
        $gapText = $gap % 60 === 0 ? ($gap === 60 ? __('1 hora') : __(':n horas', ['n' => $gap / 60])) : __(':n minutos', ['n' => $gap]);

        if ($tooClose()->where('instructor_id', $instructor->id)->exists()) {
            return __('El instructor necesita al menos :gap libre entre un Road Test y otra clase.', ['gap' => $gapText]);
        }

        if ($tooClose()->where('student_id', $student->id)->exists()) {
            return __('El estudiante necesita al menos :gap libre entre un Road Test y otra clase.', ['gap' => $gapText]);
        }

        return null;
    }

    /** Package rules for driving lessons. */
    private function checkPackage(Student $student, int $minutes, ?Lesson $ignore): ?string
    {
        if (!$student->package_id) {
            return __('El estudiante no tiene un paquete asignado. Asígnale uno antes de agendar clases.');
        }

        // Lessons bought are taken from the student's purchase, not from the current catalog
        $lessonsBought = (int) $student->package_lessons;
        if ($lessonsBought === 0) {
            return __('El paquete del estudiante (:package) no incluye clases de manejo.', ['package' => $student->package]);
        }

        $used = $student->usedLessonMinutes() - ($ignore ? (int) $ignore->duration_minutes : 0);
        $remaining = $lessonsBought * Lesson::SLOT_MINUTES - $used;

        if ($minutes > $remaining) {
            return $remaining > 0
                ? __('La clase excede las clases disponibles del paquete. :left.', ['left' => self::lessonsLeftText($remaining)])
                : __('El estudiante ya usó todas las clases de su paquete.');
        }

        return null;
    }

    /**
     * Mark a scheduled lesson as completed, missed (no-show) or cancelled.
     * Cancelling frees the hours back to the student's package.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([Lesson::STATUS_COMPLETED, Lesson::STATUS_CANCELLED, Lesson::STATUS_NO_SHOW])],
        ]);

        $lesson = Lesson::findOrFail($id);

        if ($lesson->status !== Lesson::STATUS_SCHEDULED) {
            return $this->fail(__('Solo se pueden modificar clases que estén programadas.'));
        }

        try {
            $cancelled = $validated['status'] === Lesson::STATUS_CANCELLED;
            $lesson->update(['status' => $validated['status']] + ($cancelled ? ['calendar_sequence' => $lesson->calendar_sequence + 1] : []));
            $lesson->load(['student', 'instructor', 'vehicle']);

            // A cancelled lesson is removed from both calendars; completed / no-show need no notice
            $notified = $cancelled ? $this->notifier->notify($lesson, LessonNotification::CANCELLED) : null;

            return response()->json([
                'success' => true,
                'lesson' => $lesson->toDashboardArray(),
                'notifications' => $notified,
                'student' => [
                    'db_id' => $lesson->student_id,
                    'used_minutes' => (int) Lesson::usingPackage()->where('student_id', $lesson->student_id)->sum('duration_minutes'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $this->errorMessage($e)], 500);
        }
    }

    private function fail(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }

    private static function lessonsLeftText(int $minutes): string
    {
        $lessons = intdiv($minutes, Lesson::SLOT_MINUTES);

        return $lessons === 1
            ? __('Queda 1 clase de :minutes min', ['minutes' => Lesson::SLOT_MINUTES])
            : __('Quedan :n clases de :minutes min', ['n' => $lessons, 'minutes' => Lesson::SLOT_MINUTES]);
    }
}
