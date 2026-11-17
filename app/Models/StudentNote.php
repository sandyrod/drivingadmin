<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    protected $fillable = ['student_id', 'user_id', 'body'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toDashboardArray(): array
    {
        return [
            'db_id' => $this->id,
            'body' => $this->body,
            'author' => $this->user?->name,
            'created_at' => $this->created_at?->timezone(config('app.school_timezone'))->format('Y-m-d h:i A'),
        ];
    }
}
