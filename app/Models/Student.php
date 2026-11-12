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
        'contract_number',
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
        'package_id',
        'package_lessons',
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

    /**
     * Get the payments for the student.
     */
    public function payments()
    {
        return $this->hasMany(StudentPayment::class)->orderBy('payment_date', 'desc');
    }

    /**
     * Package (driving hours) the student purchased.
     */
    public function packageModel()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Driving lessons booked for the student.
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Snapshot a catalog package onto the student (purchase): name, price and lessons are
     * copied, so later changes to the package only affect new purchases.
     * Previous payments are kept; the debt is recalculated.
     */
    public function purchasePackage(Package $package): void
    {
        $debt = max(0, (float) $package->price - (float) $this->paid_amount);

        $this->fill([
            'package_id' => $package->id,
            'package' => $package->name,
            'package_lessons' => $package->lessons,
            'plan_cost' => $package->price,
            'debt_amount' => $debt,
            'status' => $debt > 0 ? 'active' : 'al_dia',
        ]);
    }

    /**
     * Minutes already used from the package (booked, completed or missed lessons).
     */
    public function usedLessonMinutes(): int
    {
        return (int) $this->lessons()->active()->sum('duration_minutes');
    }

    /**
     * Shape used by the admin dashboard JS.
     * Pass $usedMinutes to avoid an extra query when it was preloaded.
     */
    public function toDashboardArray(?int $usedMinutes = null): array
    {
        $activeLicense = $this->relationLoaded('licenses')
            ? $this->licenses->firstWhere('is_active', true)
            : $this->activeLicense;
        return [
            'id' => 'EST-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            'db_id' => $this->id,
            'date' => $this->created_at->format('Y-m-d'),
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'contract_number' => $this->contract_number,
            'plan' => $this->package,
            'package_id' => $this->package_id,
            // Lessons bought (copy taken at purchase time, not affected by later catalog changes)
            'package_lessons' => (int) $this->package_lessons,
            'used_minutes' => $usedMinutes ?? $this->usedLessonMinutes(),
            'cost' => (float) $this->plan_cost,
            'paid' => (float) $this->paid_amount,
            'debt' => (float) $this->debt_amount,
            'transmission' => $this->transmission,
            'status' => $this->debt_amount > 0 ? 'Activo' : 'Al Día',
            'height' => $this->height,
            'eye_color' => $this->eye_color,
            'is_organ_donor' => (bool) $this->is_organ_donor,
            'student_photo_url' => $this->student_photo_url,
            'license' => $activeLicense ? [
                'license_number' => $activeLicense->license_number,
                'expiration_date' => $activeLicense->expiration_date,
                'license_class' => $activeLicense->license_class,
                'license_photo_url' => $activeLicense->license_photo_url,
                'license_photo_back_url' => $activeLicense->license_photo_back_url,
            ] : null,
        ];
    }
}
