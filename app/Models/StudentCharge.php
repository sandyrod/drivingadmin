<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Additional package bought by a student after enrollment (a Ledger charge with its own invoice).
 */
class StudentCharge extends Model
{
    protected $fillable = [
        'student_id',
        'package_id',
        'description',
        'lessons',
        'amount',
        'charge_date',
        'notes',
    ];

    protected $casts = [
        'charge_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** Invoice number: the student's invoice number plus the charge id. */
    public function getNumberAttribute(): string
    {
        return 'INV-' . str_pad($this->student_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    public function toDashboardArray(): array
    {
        return [
            'db_id' => $this->id,
            'number' => $this->number,
            'date' => $this->charge_date?->format('Y-m-d'),
            'description' => $this->description,
            'lessons' => (int) $this->lessons,
            'amount' => (float) $this->amount,
            'notes' => $this->notes,
        ];
    }
}
