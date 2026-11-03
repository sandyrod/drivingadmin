<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Store a newly created vehicle in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:vehicles,plate',
            'transmission' => 'required|string|max:20',
        ]);

        try {
            $vehicle = Vehicle::create([
                'brand' => $validated['brand'],
                'plate' => $validated['plate'],
                'transmission' => $validated['transmission'],
                'status' => 'Disponible',
            ]);

            return response()->json([
                'success' => true,
                'vehicle' => [
                    'id' => 'VEH-' . str_pad($vehicle->id, 2, '0', STR_PAD_LEFT),
                    'db_id' => $vehicle->id,
                    'brand' => $vehicle->brand,
                    'plate' => $vehicle->plate,
                    'transmission' => $vehicle->transmission,
                    'status' => $vehicle->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error registrando vehículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }

    /**
     * Update the specified vehicle in database.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:vehicles,plate,' . $id,
            'transmission' => 'required|string|max:20',
        ]);

        try {
            $vehicle->update([
                'brand' => $validated['brand'],
                'plate' => $validated['plate'],
                'transmission' => $validated['transmission'],
            ]);

            return response()->json([
                'success' => true,
                'vehicle' => [
                    'id' => 'VEH-' . str_pad($vehicle->id, 2, '0', STR_PAD_LEFT),
                    'db_id' => $vehicle->id,
                    'brand' => $vehicle->brand,
                    'plate' => $vehicle->plate,
                    'transmission' => $vehicle->transmission,
                    'status' => $vehicle->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando vehículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }

    /**
     * Remove the specified vehicle (soft-delete).
     */
    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $this->errorMessage($e)
            ], 500);
        }
    }
}
