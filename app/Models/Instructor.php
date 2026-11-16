<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'gender',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'height',
        'eye_color',
        'is_organ_donor',
        'instructor_photo_url',
        'vehicle',
        'plate',
        'transmission',
        'shift',
        'work_days',
        'work_start',
        'work_end',
        'status',
        'vehicle_id',
    ];

    /** Short Spanish names of the ISO days of the week (1 = Monday). */
    public const DAY_NAMES = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

    /** Days the instructor works (ISO numbers), empty when no schedule is set. */
    public function workDays(): array
    {
        return $this->work_days
            ? array_values(array_map('intval', array_filter(explode(',', $this->work_days))))
            : [];
    }

    public function hasSchedule(): bool
    {
        return $this->workDays() && $this->work_start && $this->work_end;
    }

    /** "08:00" */
    public function workStart(): ?string
    {
        return $this->work_start ? substr($this->work_start, 0, 5) : null;
    }

    public function workEnd(): ?string
    {
        return $this->work_end ? substr($this->work_end, 0, 5) : null;
    }

    /**
     * Whether a lesson from $start to $end (school local time, same day) fits in the working schedule.
     * Instructors without a schedule can be booked at any time.
     */
    public function worksDuring(\DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        if (!$this->hasSchedule()) {
            return true;
        }

        return in_array((int) $start->format('N'), $this->workDays(), true)
            && $start->format('Y-m-d') === $end->format('Y-m-d')
            && $start->format('H:i') >= $this->workStart()
            && $end->format('H:i') <= $this->workEnd();
    }

    /** e.g. "Lun–Vie, Sáb · 08:00–17:00" (or the old shift text when no schedule is set). */
    public function scheduleText(): string
    {
        if (!$this->hasSchedule()) {
            return $this->shift ?: 'Sin horario definido';
        }

        $days = $this->workDays();
        sort($days);

        // Group consecutive days: [1,2,3,4,5,7] => "Lun–Vie, Dom"
        $groups = [];
        foreach ($days as $day) {
            $last = count($groups) - 1;
            if ($last >= 0 && end($groups[$last]) === $day - 1) {
                $groups[$last][] = $day;
            } else {
                $groups[] = [$day];
            }
        }
        $text = implode(', ', array_map(fn ($g) => count($g) > 2
            ? self::DAY_NAMES[$g[0]] . '–' . self::DAY_NAMES[end($g)]
            : implode(', ', array_map(fn ($d) => self::DAY_NAMES[$d], $g)), $groups));

        return ($days === [1, 2, 3, 4, 5, 6, 7] ? 'Todos los días' : $text) . ' · ' . $this->workStart() . '–' . $this->workEnd();
    }

    /** Schedule fields for the dashboard JSON. */
    public function scheduleArray(): array
    {
        return [
            'shift' => $this->scheduleText(),
            'work_days' => $this->workDays(),
            'work_start' => $this->workStart(),
            'work_end' => $this->workEnd(),
        ];
    }

    /**
     * Get the vehicle assigned to the instructor.
     */
    public function vehicleModel()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get the licenses for the instructor.
     */
    public function licenses()
    {
        return $this->hasMany(InstructorLicense::class);
    }

    /**
     * Get the active license for the instructor.
     */
    public function activeLicense()
    {
        return $this->hasOne(InstructorLicense::class)->where('is_active', true);
    }
}
