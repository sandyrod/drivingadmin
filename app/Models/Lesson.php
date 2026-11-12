<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    /** Length of one driving lesson ("hora de servicio"), in minutes. */
    public const SLOT_MINUTES = 45;

    /** Durations (minutes) that can be booked: 1 to 4 consecutive lessons. */
    public const DURATIONS = [45, 90, 135, 180];

    protected $fillable = [
        'student_id',
        'instructor_id',
        'vehicle_id',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class)->withTrashed();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    /** Lessons that occupy time and package hours (everything except cancelled). */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /** Lessons that overlap the [start, end) interval. */
    public function scopeOverlapping($query, string $start, string $end)
    {
        return $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function toDashboardArray(): array
    {
        $student = $this->student;
        $instructor = $this->instructor;
        $vehicle = $this->vehicle;

        return [
            'id' => 'CLS-' . str_pad($this->id, 4, '0', STR_PAD_LEFT),
            'db_id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $student ? trim($student->first_name . ' ' . $student->last_name) : 'Estudiante Eliminado',
            'instructor_id' => $this->instructor_id,
            'instructor_name' => $instructor ? trim($instructor->first_name . ' ' . $instructor->last_name) : 'Instructor Eliminado',
            'vehicle' => $vehicle ? ($vehicle->brand . ' [' . $vehicle->plate . ']') : null,
            'date' => $this->starts_at->format('Y-m-d'),
            'time' => $this->starts_at->format('H:i'),
            'end_time' => $this->ends_at->format('H:i'),
            'duration_minutes' => (int) $this->duration_minutes,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
