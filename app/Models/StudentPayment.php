<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPayment extends Model
{
    use HasFactory;

    /** Payment methods as shown on invoices and receipts. */
    public const METHOD_LABELS = [
        'Cash' => 'Efectivo',
        'Credit Card' => 'Tarjeta de Crédito',
        'Zelle' => 'Zelle',
        'Venmo' => 'Venmo',
        'Check' => 'Cheque',
    ];

    protected $fillable = [
        'student_id',
        'amount',
        'payment_method',
        'payment_date',
        'remaining_debt',
        'notes',
    ];

    /**
     * Get the student that owns the payment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
