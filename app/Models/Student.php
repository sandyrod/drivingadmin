<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
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
        'student_photo_url',
        'package',
        'transmission',
        'plan_cost',
        'paid_amount',
        'debt_amount',
        'status',
    ];

    /**
     * Get the licenses for the student.
     */
    public function licenses()
    {
        return $this->hasMany(StudentLicense::class);
    }

    /**
     * Get the active license for the student.
     */
    public function activeLicense()
    {
        return $this->hasOne(StudentLicense::class)->where('is_active', true);
    }
}
