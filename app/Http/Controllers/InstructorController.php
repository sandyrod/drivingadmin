<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\InstructorLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstructorController extends Controller
{
    /**
     * Store a newly created instructor (and optional license) in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Instructor details
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
            'email' => 'required|email|unique:instructors,email',
            'height' => 'nullable|string|max:10',
            'eye_color' => 'nullable|string|max:30',
            'is_organ_donor' => 'boolean',

            // Instructor-specific details
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transmission' => 'nullable|string|max:20',
            'shift' => 'nullable|string|max:50',

            // License details (optional)
            'license_number' => 'nullable|string|max:50|unique:instructor_licenses,license_number',
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Create instructor
            $instructor = Instructor::create([
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
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'transmission' => $validated['transmission'] ?? null,
                'shift' => $validated['shift'] ?? null,
                'status' => 'Disponible',
            ]);

            // Create license if filled
            $activeLicense = null;
            if (!empty($validated['license_number'])) {
                $activeLicense = InstructorLicense::create([
                    'instructor_id' => $instructor->id,
                    'license_number' => $validated['license_number'],
                    'expiration_date' => $validated['expiration_date'],
                    'license_class' => $validated['license_class'],
                    'is_active' => true,
                ]);
            }

            DB::commit();

            // Load vehicle details
            $instructor->load('vehicleModel');
            $assignedVehicle = $instructor->vehicleModel;

            // Return formatted instructor back for JS rendering
            return response()->json([
                'success' => true,
                'instructor' => [
                    'id' => 'INS-' . str_pad($instructor->id, 2, '0', STR_PAD_LEFT),
                    'db_id' => $instructor->id,
                    'name' => $instructor->first_name . ' ' . $instructor->last_name,
                    'first_name' => $instructor->first_name,
                    'middle_name' => $instructor->middle_name,
                    'last_name' => $instructor->last_name,
                    'birth_date' => $instructor->birth_date,
                    'gender' => $instructor->gender,
                    'address' => $instructor->address,
                    'city' => $instructor->city,
                    'state' => $instructor->state,
                    'zip_code' => $instructor->zip_code,
                    'phone' => $instructor->phone,
                    'email' => $instructor->email,
                    'vehicle' => $assignedVehicle ? $assignedVehicle->brand : 'Sin Asignar',
                    'plate' => $assignedVehicle ? $assignedVehicle->plate : '-',
                    'transmission' => $instructor->transmission ?? 'Automático',
                    'shift' => $instructor->shift ?? 'Mañana (8am-12pm)',
                    'status' => $instructor->status,
                    'height' => $instructor->height,
                    'eye_color' => $instructor->eye_color,
                    'is_organ_donor' => (bool) $instructor->is_organ_donor,
                    'license_text' => $activeLicense ? ($activeLicense->license_class . ' (' . $activeLicense->license_number . ')') : 'NYS DMV Certified',
                    'license' => $activeLicense ? [
                        'license_number' => $activeLicense->license_number,
                        'expiration_date' => $activeLicense->expiration_date,
                        'license_class' => $activeLicense->license_class,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando instructor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified instructor in database.
     */
    public function update(Request $request, $id)
    {
        $instructor = Instructor::findOrFail($id);

        $validated = $request->validate([
            // Instructor details
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
            'email' => 'required|email|unique:instructors,email,' . $id,
            'height' => 'nullable|string|max:10',
            'eye_color' => 'nullable|string|max:30',
            'is_organ_donor' => 'boolean',

            // Instructor-specific details
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transmission' => 'nullable|string|max:20',
            'shift' => 'nullable|string|max:50',

            // License details (optional)
            'license_number' => 'nullable|string|max:50|unique:instructor_licenses,license_number,' . ($instructor->activeLicense ? $instructor->activeLicense->id : 'NULL'),
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            $instructor->update([
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
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'transmission' => $validated['transmission'] ?? null,
                'shift' => $validated['shift'] ?? null,
            ]);

            // Update or create license
            $activeLicense = $instructor->activeLicense;
            if (!empty($validated['license_number'])) {
                if ($activeLicense) {
                    $activeLicense->update([
                        'license_number' => $validated['license_number'],
                        'expiration_date' => $validated['expiration_date'],
                        'license_class' => $validated['license_class'],
                    ]);
                } else {
                    $activeLicense = InstructorLicense::create([
                        'instructor_id' => $instructor->id,
                        'license_number' => $validated['license_number'],
                        'expiration_date' => $validated['expiration_date'],
                        'license_class' => $validated['license_class'],
                        'is_active' => true,
                    ]);
                }
            } else {
                if ($activeLicense) {
                    $activeLicense->update(['is_active' => false]);
                    $activeLicense = null;
                }
            }

            DB::commit();

            // Load vehicle details
            $instructor->load('vehicleModel');
            $assignedVehicle = $instructor->vehicleModel;

            return response()->json([
                'success' => true,
                'instructor' => [
                    'id' => 'INS-' . str_pad($instructor->id, 2, '0', STR_PAD_LEFT),
                    'db_id' => $instructor->id,
                    'name' => $instructor->first_name . ' ' . $instructor->last_name,
                    'first_name' => $instructor->first_name,
                    'middle_name' => $instructor->middle_name,
                    'last_name' => $instructor->last_name,
                    'birth_date' => $instructor->birth_date,
                    'gender' => $instructor->gender,
                    'address' => $instructor->address,
                    'city' => $instructor->city,
                    'state' => $instructor->state,
                    'zip_code' => $instructor->zip_code,
                    'phone' => $instructor->phone,
                    'email' => $instructor->email,
                    'vehicle' => $assignedVehicle ? $assignedVehicle->brand : 'Sin Asignar',
                    'plate' => $assignedVehicle ? $assignedVehicle->plate : '-',
                    'vehicle_id' => $instructor->vehicle_id,
                    'transmission' => $instructor->transmission ?? 'Automático',
                    'shift' => $instructor->shift ?? 'Mañana (8am-12pm)',
                    'status' => $instructor->status,
                    'height' => $instructor->height,
                    'eye_color' => $instructor->eye_color,
                    'is_organ_donor' => (bool) $instructor->is_organ_donor,
                    'license_text' => $activeLicense ? ($activeLicense->license_class . ' (' . $activeLicense->license_number . ')') : 'NYS DMV Certified',
                    'license' => $activeLicense ? [
                        'license_number' => $activeLicense->license_number,
                        'expiration_date' => $activeLicense->expiration_date,
                        'license_class' => $activeLicense->license_class,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando instructor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified instructor (soft-delete).
     */
    public function destroy($id)
    {
        try {
            $instructor = Instructor::findOrFail($id);
            $instructor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Instructor eliminado con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar instructor: ' . $e->getMessage()
            ], 500);
        }
    }
}
