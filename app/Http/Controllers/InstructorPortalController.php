<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Mis clases": page opened from the instructor's personal link (no login).
 * The instructor sees their lessons and marks them as completed or no-show.
 */
class InstructorPortalController extends Controller
{
    /** How far back lessons still waiting to be marked are shown */
    public const PENDING_DAYS = 14;

    /** How many days ahead are shown */
    public const UPCOMING_DAYS = 7;

    public function show(string $token)
    {
        $instructor = $this->instructor($token);

        // Lessons are stored in school local time
        $now = Carbon::now(config('app.school_timezone'))->format('Y-m-d H:i:s');
        $today = substr($now, 0, 10);

        $lessons = $instructor->lessons()
            ->with(['student', 'vehicle'])
            ->where('status', '!=', Lesson::STATUS_CANCELLED)
            ->where('starts_at', '>=', Carbon::parse($today)->subDays(self::PENDING_DAYS))
            ->where('starts_at', '<', Carbon::parse($today)->addDays(self::UPCOMING_DAYS + 1))
            ->orderBy('starts_at')
            ->get();

        $isPending = fn (Lesson $l) => $l->status === Lesson::STATUS_SCHEDULED && $l->starts_at->format('Y-m-d H:i:s') <= $now;

        return view('instructor-portal', [
            'instructor' => $instructor,
            'token' => $token,
            'pending' => $lessons->filter($isPending)->values(),
            'today' => $lessons->reject($isPending)->filter(fn ($l) => $l->starts_at->format('Y-m-d') === $today)->values(),
            'upcoming' => $lessons->filter(fn ($l) => $l->starts_at->format('Y-m-d') > $today)->values(),
            'recent' => $lessons->reject($isPending)->filter(fn ($l) => $l->starts_at->format('Y-m-d') < $today)->reverse()->take(10)->values(),
        ]);
    }

    public function updateStatus(Request $request, string $token, int $lessonId)
    {
        $instructor = $this->instructor($token);
        $validated = $request->validate([
            'status' => ['required', Rule::in([Lesson::STATUS_COMPLETED, Lesson::STATUS_NO_SHOW])],
        ]);

        $lesson = $instructor->lessons()->whereKey($lessonId)->firstOrFail();
        $now = Carbon::now(config('app.school_timezone'))->format('Y-m-d H:i:s');

        if ($lesson->status !== Lesson::STATUS_SCHEDULED) {
            return back()->with('error', 'Esa clase ya estaba marcada.');
        }
        if ($lesson->starts_at->format('Y-m-d H:i:s') > $now) {
            return back()->with('error', 'Solo puedes marcar una clase cuando ya empezó.');
        }

        $lesson->update(['status' => $validated['status']]);

        $student = trim(($lesson->student?->first_name ?? '') . ' ' . ($lesson->student?->last_name ?? ''));

        return back()->with('ok', ($validated['status'] === Lesson::STATUS_COMPLETED ? 'Clase completada' : 'Marcada como "no asistió"')
            . ($student ? ': ' . $student : '') . '.');
    }

    private function instructor(string $token): Instructor
    {
        abort_if(strlen($token) < 32, 404);

        $instructor = Instructor::where('portal_token', $token)->first();
        abort_unless($instructor, 404);
        abort_if($instructor->status === 'Suspendido', 403, 'Tu acceso está suspendido. Comunícate con la academia.');

        return $instructor;
    }
}
