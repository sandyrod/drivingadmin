<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\InstructorLicense;
use App\Notifications\InstructorPortalLink;
use App\Support\ImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
            'instructor_photo' => ImageUploads::RULE,
            'license_photo' => ImageUploads::RULE,
            'license_photo_back' => ImageUploads::RULE,

            // Instructor-specific details
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transmission' => 'nullable|string|max:20',
            'language' => 'nullable|in:es,en',
            'shift' => 'nullable|string|max:50',
            // Working schedule (optional: without it the instructor can be booked at any time)
            'work_days' => 'nullable|array|min:1',
            'work_days.*' => 'integer|between:1,7|distinct',
            'work_start' => 'nullable|required_with:work_days|date_format:H:i',
            'work_end' => 'nullable|required_with:work_days|date_format:H:i|after:work_start',

            // License details (optional)
            'license_number' => 'nullable|string|max:50|unique:instructor_licenses,license_number',
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Save images
            $instructor_photo_url = $this->saveImage($validated['instructor_photo'] ?? null, 'instructors', 'instructor_profile');

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
                'instructor_photo_url' => $instructor_photo_url,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'transmission' => $validated['transmission'] ?? null,
                'language' => $validated['language'] ?? config('app.default_language'),
                'shift' => $validated['shift'] ?? null,
                ...self::scheduleFields($validated),
                'status' => 'Disponible',
            ]);

            // Create license if filled
            $activeLicense = null;
            if (!empty($validated['license_number'])) {
                $license_photo_url = $this->saveImage($validated['license_photo'] ?? null, 'licenses', 'lic_front');
                $license_photo_back_url = $this->saveImage($validated['license_photo_back'] ?? null, 'licenses', 'lic_back');

                $activeLicense = InstructorLicense::create([
                    'instructor_id' => $instructor->id,
                    'license_number' => $validated['license_number'],
                    'expiration_date' => $validated['expiration_date'],
                    'license_class' => $validated['license_class'],
                    'license_photo_url' => $license_photo_url,
                    'license_photo_back_url' => $license_photo_back_url,
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
                    'vehicle' => $assignedVehicle ? $assignedVehicle->brand : __('Sin Asignar'),
                    'plate' => $assignedVehicle ? $assignedVehicle->plate : '-',
                    'transmission' => $instructor->transmission ?? 'Automático',
                    'language' => $instructor->language ?: config('app.default_language'),
                    ...$instructor->scheduleArray(),
                    'status' => $instructor->status,
                    'height' => $instructor->height,
                    'eye_color' => $instructor->eye_color,
                    'is_organ_donor' => (bool) $instructor->is_organ_donor,
                    'instructor_photo_url' => $instructor->instructor_photo_url,
                    'license_text' => $activeLicense ? ($activeLicense->license_class . ' (' . $activeLicense->license_number . ')') : 'NYS DMV Certified',
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
            Log::error('Error registrando instructor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
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
            'instructor_photo' => ImageUploads::RULE,
            'license_photo' => ImageUploads::RULE,
            'license_photo_back' => ImageUploads::RULE,

            // Instructor-specific details
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transmission' => 'nullable|string|max:20',
            'language' => 'nullable|in:es,en',
            'shift' => 'nullable|string|max:50',
            // Working schedule (optional: without it the instructor can be booked at any time)
            'work_days' => 'nullable|array|min:1',
            'work_days.*' => 'integer|between:1,7|distinct',
            'work_start' => 'nullable|required_with:work_days|date_format:H:i',
            'work_end' => 'nullable|required_with:work_days|date_format:H:i|after:work_start',

            // License details (optional)
            'license_number' => 'nullable|string|max:50|unique:instructor_licenses,license_number,' . ($instructor->activeLicense ? $instructor->activeLicense->id : 'NULL'),
            'expiration_date' => 'nullable|required_with:license_number|date',
            'license_class' => 'nullable|required_with:license_number|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Update instructor photo
            $insPhotoVal = $request->input('instructor_photo');
            $instructor_photo_url = $instructor->instructor_photo_url;
            
            if ($insPhotoVal !== $instructor->instructor_photo_url) {
                if (empty($insPhotoVal)) {
                    $this->deleteOldImage($instructor->instructor_photo_url);
                    $instructor_photo_url = null;
                } else if (str_starts_with($insPhotoVal, 'data:image/')) {
                    $this->deleteOldImage($instructor->instructor_photo_url);
                    $instructor_photo_url = $this->saveImage($insPhotoVal, 'instructors', 'instructor_profile');
                }
            }

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
                'instructor_photo_url' => $instructor_photo_url,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'transmission' => $validated['transmission'] ?? null,
                'language' => $validated['language'] ?? config('app.default_language'),
                'shift' => $validated['shift'] ?? null,
                ...self::scheduleFields($validated),
            ]);

            // Update or create license
            $activeLicense = $instructor->activeLicense;
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
                    $activeLicense = InstructorLicense::create([
                        'instructor_id' => $instructor->id,
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
                    'vehicle' => $assignedVehicle ? $assignedVehicle->brand : __('Sin Asignar'),
                    'plate' => $assignedVehicle ? $assignedVehicle->plate : '-',
                    'vehicle_id' => $instructor->vehicle_id,
                    'transmission' => $instructor->transmission ?? 'Automático',
                    'language' => $instructor->language ?: config('app.default_language'),
                    ...$instructor->scheduleArray(),
                    'status' => $instructor->status,
                    'height' => $instructor->height,
                    'eye_color' => $instructor->eye_color,
                    'is_organ_donor' => (bool) $instructor->is_organ_donor,
                    'instructor_photo_url' => $instructor->instructor_photo_url,
                    'license_text' => $activeLicense ? ($activeLicense->license_class . ' (' . $activeLicense->license_number . ')') : 'NYS DMV Certified',
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
            Log::error('Error actualizando instructor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
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
                'message' => __('Instructor eliminado con éxito.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }

    private function saveImage($base64, $subfolder, $prefix)
    {
        return ImageUploads::store($base64, $subfolder, $prefix);
    }

    private function deleteOldImage($url)
    {
        ImageUploads::delete($url);
    }

    /** Working schedule columns from the validated request. */
    private static function scheduleFields(array $validated): array
    {
        $days = array_map('intval', $validated['work_days'] ?? []);
        sort($days);

        return [
            'work_days' => $days ? implode(',', $days) : null,
            'work_start' => $days ? $validated['work_start'] : null,
            'work_end' => $days ? $validated['work_end'] : null,
        ];
    }

    /**
     * Personal "Mis clases" link of the instructor: returns it and optionally sends it
     * by email / SMS. "regenerate" creates a new link (the old one stops working).
     */
    public function portalLink(Request $request, $id)
    {
        $instructor = Instructor::findOrFail($id);
        $url = $instructor->portalUrl($request->boolean('regenerate'));

        $sent = [];
        $failed = null;
        if ($request->boolean('send')) {
            $route = Notification::route('mail', $instructor->email)->route('sms', $instructor->phone);
            $notification = (new InstructorPortalLink($instructor, $url))->locale($instructor->language ?: config('app.default_language'));
            $channels = $notification->via($route);

            if (!$channels) {
                $failed = __('El instructor no tiene correo ni teléfono válido.');
            } else {
                try {
                    $route->notifyNow($notification);
                    $sent = array_map(fn ($c) => $c === 'mail' ? __('correo') : 'SMS', $channels);
                } catch (\Throwable $e) {
                    Log::error('Error enviando enlace Mis clases: ' . $e->getMessage());
                    $failed = __('No se pudo enviar el enlace. Revisa la configuración de correo/SMS.');
                }
            }
        }

        return response()->json(['success' => true, 'url' => $url, 'sent' => $sent, 'failed' => $failed]);
    }
}
