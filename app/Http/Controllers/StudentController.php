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
                'student_photo_url' => $s->student_photo_url,
                'license' => $activeLicense ? [
                    'license_number' => $activeLicense->license_number,
                    'expiration_date' => $activeLicense->expiration_date,
                    'license_class' => $activeLicense->license_class,
                    'license_photo_url' => $activeLicense->license_photo_url,
                    'license_photo_back_url' => $activeLicense->license_photo_back_url,
                ] : null
            ];
        });

        // Load all instructors with active license and vehicleModel relations
        $dbInstructors = \App\Models\Instructor::with([
            'licenses' => function ($query) {
                $query->where('is_active', true);
            },
            'vehicleModel'
        ])->get();

        // Map database records to the structure expected by the JS mockup system
        $instructors = $dbInstructors->map(function ($ins) {
            $activeLicense = $ins->licenses->first();
            return [
                'id' => 'INS-' . str_pad($ins->id, 2, '0', STR_PAD_LEFT),
                'db_id' => $ins->id,
                'name' => $ins->first_name . ' ' . $ins->last_name,
                'first_name' => $ins->first_name,
                'middle_name' => $ins->middle_name,
                'last_name' => $ins->last_name,
                'birth_date' => $ins->birth_date,
                'gender' => $ins->gender,
                'address' => $ins->address,
                'city' => $ins->city,
                'state' => $ins->state,
                'zip_code' => $ins->zip_code,
                'phone' => $ins->phone,
                'email' => $ins->email,
                'vehicle' => $ins->vehicleModel ? $ins->vehicleModel->brand : 'Sin Asignar',
                'plate' => $ins->vehicleModel ? $ins->vehicleModel->plate : '-',
                'vehicle_id' => $ins->vehicle_id,
                'transmission' => $ins->transmission ?? 'Automático',
                'shift' => $ins->shift ?? 'Mañana (8am-12pm)',
                'status' => $ins->status ?? 'Disponible',
                'height' => $ins->height,
                'eye_color' => $ins->eye_color,
                'is_organ_donor' => (bool) $ins->is_organ_donor,
                'instructor_photo_url' => $ins->instructor_photo_url,
                'license_text' => $activeLicense ? ($activeLicense->license_class . ' (' . $activeLicense->license_number . ')') : 'NYS DMV Certified',
                'license' => $activeLicense ? [
                    'license_number' => $activeLicense->license_number,
                    'expiration_date' => $activeLicense->expiration_date,
                    'license_class' => $activeLicense->license_class,
                    'license_photo_url' => $activeLicense->license_photo_url,
                    'license_photo_back_url' => $activeLicense->license_photo_back_url,
                ] : null
            ];
        });

        // Load all vehicles
        $dbVehicles = \App\Models\Vehicle::all();
        $vehicles = $dbVehicles->map(function ($v) {
            return [
                'id' => 'VEH-' . str_pad($v->id, 2, '0', STR_PAD_LEFT),
                'db_id' => $v->id,
                'brand' => $v->brand,
                'plate' => $v->plate,
                'transmission' => $v->transmission,
                'status' => $v->status,
            ];
        });

        // Load all reviews (evaluations)
        $dbReviews = \App\Models\Review::with('instructorModel')->get();
        $reviews = $dbReviews->map(function ($r) {
            return [
                'id' => 'REV-' . str_pad($r->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $r->id,
                'instructor' => $r->instructorModel ? ($r->instructorModel->first_name . ' ' . $r->instructorModel->last_name) : 'Instructor Eliminado',
                'instructor_id' => $r->instructor_id,
                'rating' => (int) $r->rating,
                'vehicleRating' => (int) $r->vehicle_rating,
                'comment' => $r->comment,
            ];
        });

        // Pass mapping to view
        return view('driving', compact('students', 'instructors', 'vehicles', 'reviews'));
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
            'student_photo' => 'nullable|string',
            'license_photo' => 'nullable|string',
            'license_photo_back' => 'nullable|string',

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

            // Save images
            $student_photo_url = $this->saveImage($validated['student_photo'] ?? null, 'students', 'student_profile');

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
                'student_photo_url' => $student_photo_url,
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
                $license_photo_url = $this->saveImage($validated['license_photo'] ?? null, 'licenses', 'lic_front');
                $license_photo_back_url = $this->saveImage($validated['license_photo_back'] ?? null, 'licenses', 'lic_back');

                $activeLicense = StudentLicense::create([
                    'student_id' => $student->id,
                    'license_number' => $validated['license_number'],
                    'expiration_date' => $validated['expiration_date'],
                    'license_class' => $validated['license_class'],
                    'license_photo_url' => $license_photo_url,
                    'license_photo_back_url' => $license_photo_back_url,
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
                    'student_photo_url' => $student->student_photo_url,
                    'license' => $activeLicense ? [
                        'license_number' => $activeLicense->license_number,
                        'expiration_date' => $activeLicense->expiration_date,
                        'license_class' => $activeLicense->license_class,
                        'license_photo_url' => $activeLicense->license_photo_url,
                        'license_photo_back_url' => $activeLicense->license_photo_back_url,
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
     * Update the specified student in database.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

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
            'email' => 'required|email|unique:students,email,' . $id,
            'height' => 'nullable|string|max:10',
            'eye_color' => 'nullable|string|max:30',
            'is_organ_donor' => 'boolean',
            'student_photo' => 'nullable|string',
            'license_photo' => 'nullable|string',
            'license_photo_back' => 'nullable|string',

            // Enrollment details
            'package' => 'required|string|max:100',
            'transmission' => 'required|string|max:20',
            'plan_cost' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'debt_amount' => 'required|numeric|min:0',

            // License/Permit details (optional)
            'license_number' => 'nullable|string|max:50|unique:student_licenses,license_number,' . ($student->activeLicense ? $student->activeLicense->id : 'NULL'),
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Update student photo
            $studentPhotoVal = $request->input('student_photo');
            $student_photo_url = $student->student_photo_url;
            
            if ($studentPhotoVal !== $student->student_photo_url) {
                if (empty($studentPhotoVal)) {
                    $this->deleteOldImage($student->student_photo_url);
                    $student_photo_url = null;
                } else if (str_starts_with($studentPhotoVal, 'data:image/')) {
                    $this->deleteOldImage($student->student_photo_url);
                    $student_photo_url = $this->saveImage($studentPhotoVal, 'students', 'student_profile');
                }
            }

            $student->update([
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
                'student_photo_url' => $student_photo_url,
                'package' => $validated['package'],
                'transmission' => $validated['transmission'],
                'plan_cost' => $validated['plan_cost'],
                'paid_amount' => $validated['paid_amount'],
                'debt_amount' => $validated['debt_amount'],
                'status' => $validated['debt_amount'] > 0 ? 'active' : 'al_dia',
            ]);

            // Update or create license
            $activeLicense = $student->activeLicense;
            if (!empty($validated['license_number'])) {
                $licPhotoVal = $request->input('license_photo');
                $licPhotoBackVal = $request->input('license_photo_back');
                
                $license_photo_url = $activeLicense ? $activeLicense->license_photo_url : null;
                $license_photo_back_url = $activeLicense ? $activeLicense->license_photo_back_url : null;
                
                // Front photo change
                if ($activeLicense && $licPhotoVal !== $activeLicense->license_photo_url) {
                    if (empty($licPhotoVal)) {
                        $this->deleteOldImage($activeLicense->license_photo_url);
                        $license_photo_url = null;
                    } else if (str_starts_with($licPhotoVal, 'data:image/')) {
                        $this->deleteOldImage($activeLicense->license_photo_url);
                        $license_photo_url = $this->saveImage($licPhotoVal, 'licenses', 'lic_front');
                    }
                } else if (!$activeLicense && !empty($licPhotoVal)) {
                    $license_photo_url = $this->saveImage($licPhotoVal, 'licenses', 'lic_front');
                }
                
                // Back photo change
                if ($activeLicense && $licPhotoBackVal !== $activeLicense->license_photo_back_url) {
                    if (empty($licPhotoBackVal)) {
                        $this->deleteOldImage($activeLicense->license_photo_back_url);
                        $license_photo_back_url = null;
                    } else if (str_starts_with($licPhotoBackVal, 'data:image/')) {
                        $this->deleteOldImage($activeLicense->license_photo_back_url);
                        $license_photo_back_url = $this->saveImage($licPhotoBackVal, 'licenses', 'lic_back');
                    }
                } else if (!$activeLicense && !empty($licPhotoBackVal)) {
                    $license_photo_back_url = $this->saveImage($licPhotoBackVal, 'licenses', 'lic_back');
                }

                if ($activeLicense) {
                    $activeLicense->update([
                        'license_number' => $validated['license_number'],
                        'expiration_date' => $validated['expiration_date'],
                        'license_class' => $validated['license_class'],
                        'license_photo_url' => $license_photo_url,
                        'license_photo_back_url' => $license_photo_back_url,
                    ]);
                } else {
                    $activeLicense = StudentLicense::create([
                        'student_id' => $student->id,
                        'license_number' => $validated['license_number'],
                        'expiration_date' => $validated['expiration_date'],
                        'license_class' => $validated['license_class'],
                        'license_photo_url' => $license_photo_url,
                        'license_photo_back_url' => $license_photo_back_url,
                        'is_active' => true,
                    ]);
                }
            } else {
                if ($activeLicense) {
                    $this->deleteOldImage($activeLicense->license_photo_url);
                    $this->deleteOldImage($activeLicense->license_photo_back_url);
                    $activeLicense->update([
                        'is_active' => false,
                        'license_photo_url' => null,
                        'license_photo_back_url' => null,
                    ]);
                    $activeLicense = null;
                }
            }

            DB::commit();

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
                    'student_photo_url' => $student->student_photo_url,
                    'license' => $activeLicense ? [
                        'license_number' => $activeLicense->license_number,
                        'expiration_date' => $activeLicense->expiration_date,
                        'license_class' => $activeLicense->license_class,
                        'license_photo_url' => $activeLicense->license_photo_url,
                        'license_photo_back_url' => $activeLicense->license_photo_back_url,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando estudiante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()
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

    private function saveImage($base64, $subfolder, $prefix)
    {
        if (empty($base64)) {
            return null;
        }

        if (str_starts_with($base64, '/uploads/')) {
            return $base64;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, jpeg, gif, webp

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                throw new \Exception('Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.');
            }

            $data = base64_decode($data);
            if ($data === false) {
                throw new \Exception('Fallo al decodificar la imagen.');
            }

            $fileName = $prefix . '_' . uniqid() . '.' . $type;
            $dirPath = public_path('uploads/' . $subfolder);
            
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            file_put_contents($dirPath . '/' . $fileName, $data);
            return '/uploads/' . $subfolder . '/' . $fileName;
        }

        return null;
    }

    private function deleteOldImage($url)
    {
        if ($url && str_starts_with($url, '/uploads/')) {
            $path = public_path(ltrim($url, '/'));
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
