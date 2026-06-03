<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instructor_id',
        'rating',
        'vehicle_rating',
        'comment',
    ];

    /**
     * Get the instructor that was reviewed.
     */
    public function instructorModel()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }
}
