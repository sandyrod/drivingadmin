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
        'status',
        'vehicle_id',
    ];

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
