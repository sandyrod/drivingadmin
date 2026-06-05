<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorLicense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instructor_id',
        'license_number',
        'expiration_date',
        'license_class',
        'license_photo_url',
        'license_photo_back_url',
        'is_active',
    ];

    /**
     * Get the instructor that owns the license.
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
