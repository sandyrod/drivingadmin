<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource (Dashboard view).
     */
    public function index()
    {
        // Load all students with active license relation
        $dbStudents = Student::with([
            'licenses' => function ($query) {
                $query->where('is_active', true);
            }
        ])->get();

        // Map database records to the exact structure expected by the JS mockup system
        $students = $dbStudents->map(function ($s) {
            $activeLicense = $s->licenses->first();
            return [
                'id' => 'EST-' . str_pad($s->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $s->id,
                'date' => $s->created_at->format('Y-m-d'),
                'name' => $s->first_name . ' ' . $s->last_name,
                'first_name' => $s->first_name,
                'middle_name' => $s->middle_name,
                'last_name' => $s->last_name,
                'birth_date' => $s->birth_date,
                'gender' => $s->gender,
                'address' => $s->address,
                'city' => $s->city,
                'state' => $s->state,
                'zip_code' => $s->zip_code,
                'phone' => $s->phone,
                'email' => $s->email,
                'plan' => $s->package,
                'cost' => (float) $s->plan_cost,
                'paid' => (float) $s->paid_amount,
                'debt' => (float) $s->debt_amount,
                'transmission' => $s->transmission,
                'status' => $s->debt_amount > 0 ? 'Activo' : 'Al Día',
                'height' => $s->height,
                'eye_color' => $s->eye_color,
                'is_organ_donor' => (bool) $s->is_organ_donor,
                'license' => $activeLicense ? [
                    'license_number' => $activeLicense->license_number,
                    'expiration_date' => $activeLicense->expiration_date,
                    'license_class' => $activeLicense->license_class,
                ] : null
            ];
        });

        // Pass mapping to view
        return view('driving', compact('students'));
    }

    /**
     * Store a newly created student (and optional permit) in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Student details
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|string|max:1',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|unique:students,email',
            'height' => 'nullable|string|max:10',
            'eye_color' => 'nullable|string|max:30',
            'is_organ_donor' => 'boolean',

            // Enrollment details
            'package' => 'required|string|max:100',
            'transmission' => 'required|string|max:20',
            'plan_cost' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'debt_amount' => 'required|numeric|min:0',

            // License/Permit details (optional)
            'license_number' => 'nullable|string|max:50|unique:student_licenses,license_number',
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Create student
            $student = Student::create([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'height' => $validated['height'] ?? null,
                'eye_color' => $validated['eye_color'] ?? null,
                'is_organ_donor' => $request->boolean('is_organ_donor'),
                'package' => $validated['package'],
                'transmission' => $validated['transmission'],
                'plan_cost' => $validated['plan_cost'],
                'paid_amount' => $validated['paid_amount'],
                'debt_amount' => $validated['debt_amount'],
                'status' => $validated['debt_amount'] > 0 ? 'active' : 'al_dia',
            ]);

            // Create license/permit if filled
            $activeLicense = null;
            if (!empty($validated['license_number'])) {
                $activeLicense = StudentLicense::create([
                    'student_id' => $student->id,
                    'license_number' => $validated['license_number'],
                    'expiration_date' => $validated['expiration_date'],
                    'license_class' => $validated['license_class'],
                    'is_active' => true,
                ]);
            }

            DB::commit();

            // Return formatted student back for JS rendering
            return response()->json([
                'success' => true,
                'student' => [
                    'id' => 'EST-' . str_pad($student->id, 3, '0', STR_PAD_LEFT),
                    'db_id' => $student->id,
                    'date' => $student->created_at->format('Y-m-d'),
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'birth_date' => $student->birth_date,
                    'gender' => $student->gender,
                    'address' => $student->address,
                    'city' => $student->city,
                    'state' => $student->state,
                    'zip_code' => $student->zip_code,
                    'phone' => $student->phone,
                    'email' => $student->email,
                    'plan' => $student->package,
                    'cost' => (float) $student->plan_cost,
                    'paid' => (float) $student->paid_amount,
                    'debt' => (float) $student->debt_amount,
                    'transmission' => $student->transmission,
                    'status' => $student->debt_amount > 0 ? 'Activo' : 'Al Día',
                    'height' => $student->height,
                    'eye_color' => $student->eye_color,
                    'is_organ_donor' => (bool) $student->is_organ_donor,
                    'license' => $activeLicense ? [
                        'license_number' => $activeLicense->license_number,
                        'expiration_date' => $activeLicense->expiration_date,
                        'license_class' => $activeLicense->license_class,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando estudiante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a payment/abono for a student.
     */
    public function recordPayment(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $student->debt_amount,
        ]);

        try {
            DB::beginTransaction();

            $amount = $validated['amount'];
            $student->paid_amount += $amount;
            $student->debt_amount -= $amount;

            if ($student->debt_amount <= 0) {
                $student->status = 'al_dia';
            }

            $student->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'paid' => (float) $student->paid_amount,
                'debt' => (float) $student->debt_amount,
                'status' => $student->debt_amount > 0 ? 'Activo' : 'Al Día'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar abono: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student (soft-delete).
     */
    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estudiante eliminado con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar estudiante: ' . $e->getMessage()
            ], 500);
        }
    }
}
