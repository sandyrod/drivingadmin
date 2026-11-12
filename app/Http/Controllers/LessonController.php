<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    /**
     * Book a driving lesson for a student.
     *
     * Rules:
     *  - the student must have a package assigned;
     *  - the lesson can't exceed the hours left in the package;
     *  - it can't be in the past;
     *  - neither the instructor nor the student can have another lesson at the same time.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->whereNull('deleted_at')],
        ], $this->slotRules()));

        return $this->saveLesson($validated, (int) $validated['student_id']);
    }

    /**
     * Reschedule a lesson: change instructor, date, time, duration or notes.
     * Only lessons that are still "scheduled" can be rescheduled; the same rules as booking apply
     * (the lesson's own time and hours are not counted against itself).
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->slotRules());

        $lesson = Lesson::findOrFail($id);

        if ($lesson->status !== Lesson::STATUS_SCHEDULED) {
            return $this->fail('Solo se pueden reprogramar clases que estén programadas.');
        }

        return $this->saveLesson($validated, $lesson->student_id, $lesson);
    }

    private function slotRules(): array
    {
        return [
            'instructor_id' => ['required', 'integer', Rule::exists('instructors', 'id')->whereNull('deleted_at')],
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'duration_minutes' => ['required', 'integer', Rule::in(Lesson::DURATIONS)],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Create a lesson, or move an existing one ($lesson), after checking every booking rule.
     */
    private function saveLesson(array $validated, int $studentId, ?Lesson $lesson = null)
    {
        $minutes = (int) $validated['duration_minutes'];
        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['time']);
        $end = $start->copy()->addMinutes($minutes);

        $nowAtSchool = Carbon::now(config('app.school_timezone'))->format('Y-m-d H:i');
        if ($start->format('Y-m-d H:i') < $nowAtSchool) {
            return $this->fail('No se puede agendar una clase en una fecha u hora que ya pasó.');
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
                    return $this->fail('Solo se pueden reprogramar clases que estén programadas.');
                }
            }

            $error = $this->checkBookingRules($student, $instructor, $start, $end, $minutes, $lesson);
            if ($error) {
                DB::rollBack();
                return $this->fail($error);
            }

            $data = [
                'instructor_id' => $instructor->id,
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'ends_at' => $end->format('Y-m-d H:i:s'),
                'duration_minutes' => $minutes,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($lesson) {
                // Keep the vehicle unless the instructor changed
                if ($lesson->instructor_id !== $instructor->id) {
                    $data['vehicle_id'] = $instructor->vehicle_id;
                }
                $lesson->update($data);
            } else {
                $lesson = Lesson::create($data + [
                    'student_id' => $student->id,
                    'vehicle_id' => $instructor->vehicle_id, // vehicle assigned to the instructor
                    'status' => Lesson::STATUS_SCHEDULED,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'lesson' => $lesson->load(['student', 'instructor', 'vehicle'])->toDashboardArray(),
                'student' => ['db_id' => $student->id, 'used_minutes' => $student->usedLessonMinutes()],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $this->errorMessage($e)], 500);
        }
    }

    /**
     * Returns an error message if the booking breaks a rule, or null if it is valid.
     * $ignore is the lesson being rescheduled (its own hours and time slot don't count).
     */
    private function checkBookingRules(Student $student, Instructor $instructor, Carbon $start, Carbon $end, int $minutes, ?Lesson $ignore): ?string
    {
        if (!$student->package_id) {
            return 'El estudiante no tiene un paquete asignado. Asígnale uno antes de agendar clases.';
        }

        // Lessons bought are taken from the student's purchase, not from the current catalog
        $lessonsBought = (int) $student->package_lessons;
        if ($lessonsBought === 0) {
            return 'El paquete del estudiante (' . $student->package . ') no incluye clases de manejo.';
        }

        $used = $student->usedLessonMinutes() - ($ignore ? (int) $ignore->duration_minutes : 0);
        $remaining = $lessonsBought * Lesson::SLOT_MINUTES - $used;

        if ($minutes > $remaining) {
            return $remaining > 0
                ? 'La clase excede las clases disponibles del paquete. ' . self::lessonsLeftText($remaining) . '.'
                : 'El estudiante ya usó todas las clases de su paquete.';
        }

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        $others = fn () => Lesson::active()->overlapping($startStr, $endStr)
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id));

        if ($others()->where('instructor_id', $instructor->id)->exists()) {
            return 'El instructor ya tiene una clase en ese horario.';
        }

        if ($others()->where('student_id', $student->id)->exists()) {
            return 'El estudiante ya tiene una clase en ese horario.';
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
            return $this->fail('Solo se pueden modificar clases que estén programadas.');
        }

        try {
            $lesson->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'lesson' => $lesson->load(['student', 'instructor', 'vehicle'])->toDashboardArray(),
                'student' => [
                    'db_id' => $lesson->student_id,
                    'used_minutes' => (int) Lesson::active()->where('student_id', $lesson->student_id)->sum('duration_minutes'),
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
            ? 'Queda 1 clase de ' . Lesson::SLOT_MINUTES . ' min'
            : "Quedan {$lessons} clases de " . Lesson::SLOT_MINUTES . ' min';
    }
}
