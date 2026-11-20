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

    /** Lessons per page in the "Completadas" / "No asistió" tabs */
    public const PER_PAGE = 20;

    /** Tabs of the page => lesson status they list */
    public const TABS = [
        'pendientes' => Lesson::STATUS_SCHEDULED,
        'completadas' => Lesson::STATUS_COMPLETED,
        'no-asistio' => Lesson::STATUS_NO_SHOW,
    ];

    /** Student columns the search box looks in */
    private const SEARCH_COLUMNS = [
        'first_name', 'middle_name', 'last_name', 'phone', 'other_phone', 'email', 'secondary_email',
        'contract_number', 'parent_name', 'address', 'city', 'zip_code', 'pickup_location',
    ];

    public function show(Request $request, string $token)
    {
        $instructor = $this->instructor($token);

        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'pendientes';
        $q = trim((string) $request->query('q', ''));
        $date = $request->query('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) ? $date : null;

        // Lessons are stored in school local time
        $now = Carbon::now(config('app.school_timezone'))->format('Y-m-d H:i:s');
        $today = substr($now, 0, 10);

        // Search (any student data, every word must match) + date: shared by the three tabs
        $base = fn () => $instructor->lessons()
            ->when($q !== '', function ($query) use ($q) {
                foreach (preg_split('/\s+/', $q) as $word) {
                    $query->whereHas('student', function ($s) use ($word) {
                        $s->where(function ($w) use ($word) {
                            foreach (self::SEARCH_COLUMNS as $column) {
                                $w->orWhere($column, 'like', '%' . $word . '%');
                            }
                            $w->orWhereHas('activeLicense', fn ($l) => $l->where('license_number', 'like', '%' . $word . '%'));
                        });
                    });
                }
            })
            ->when($date, fn ($query) => $query->whereDate('starts_at', $date));

        // Pending tab without a date: recent days still waiting to be marked + the next days
        $pendingQuery = fn () => $base()->where('status', Lesson::STATUS_SCHEDULED)
            ->when(!$date, fn ($query) => $query
                ->where('starts_at', '>=', Carbon::parse($today)->subDays(self::PENDING_DAYS))
                ->where('starts_at', '<', Carbon::parse($today)->addDays(self::UPCOMING_DAYS + 1)));

        $counts = [
            'pendientes' => $pendingQuery()->count(),
            'completadas' => $base()->where('status', Lesson::STATUS_COMPLETED)->count(),
            'no-asistio' => $base()->where('status', Lesson::STATUS_NO_SHOW)->count(),
        ];

        $view = [
            'instructor' => $instructor,
            'token' => $token,
            'tab' => $tab,
            'q' => $q,
            'date' => $date,
            'counts' => $counts,
            'now' => $now,
        ];

        if ($tab === 'pendientes') {
            $lessons = $pendingQuery()->with(['student', 'vehicle'])->orderBy('starts_at')->get();
            $started = fn (Lesson $l) => $l->starts_at->format('Y-m-d H:i:s') <= $now;

            $view['pending'] = $lessons->filter($started)->values();
            $view['today'] = $lessons->reject($started)->filter(fn ($l) => $l->starts_at->format('Y-m-d') === $today)->values();
            $view['upcoming'] = $lessons->reject($started)->filter(fn ($l) => $l->starts_at->format('Y-m-d') !== $today)->values();
        } else {
            $view['lessons'] = $base()->where('status', self::TABS[$tab])
                ->with(['student', 'vehicle'])
                ->orderByDesc('starts_at')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('instructor-portal', $view);
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
            return back()->with('error', __('Esa clase ya estaba marcada.'));
        }
        if ($lesson->starts_at->format('Y-m-d H:i:s') > $now) {
            return back()->with('error', __('Solo puedes marcar una clase cuando ya empezó.'));
        }

        $lesson->update(['status' => $validated['status']]);

        $student = trim(($lesson->student?->first_name ?? '') . ' ' . ($lesson->student?->last_name ?? ''));

        return back()->with('ok', ($validated['status'] === Lesson::STATUS_COMPLETED ? __('Clase completada') : __('Marcada como "no asistió"'))
            . ($student ? ': ' . $student : '') . '.');
    }

    private function instructor(string $token): Instructor
    {
        abort_if(strlen($token) < 32, 404);

        $instructor = Instructor::where('portal_token', $token)->first();
        abort_unless($instructor, 404);
        // Without a language picked on this device, the page uses the instructor's preferred one
        if (!\App\Http\Middleware\SetLocale::chosen(request())) {
            $locale = $instructor->language ?: config('app.default_language');
            app()->setLocale($locale);
            Carbon::setLocale($locale);
        }

        abort_if($instructor->status === 'Suspendido', 403, __('Tu acceso está suspendido. Comunícate con la academia.'));

        return $instructor;
    }
}
