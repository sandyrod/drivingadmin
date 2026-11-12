<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'lessons',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lessons' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Shape used by the admin dashboard JS.
     * students_count is available when loaded with withCount('students').
     */
    public function toDashboardArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'lessons' => (int) $this->lessons,
            'price' => (float) $this->price,
            'is_active' => (bool) $this->is_active,
            'students_count' => (int) ($this->students_count ?? $this->students()->count()),
        ];
    }
}
