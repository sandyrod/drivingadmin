<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand',
        'plate',
        'transmission',
        'status',
    ];

    /**
     * Get the instructors assigned to this vehicle.
     */
    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }
}
