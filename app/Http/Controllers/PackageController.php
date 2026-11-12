<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Package catalog management.
 *
 * Editing a package (name, price, lessons) never changes students who already bought it:
 * each student keeps a copy of the name, price and lessons from the moment of purchase.
 */
class PackageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);

        $package = Package::create($validated);

        return $this->ok($package);
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $validated = $this->validatePackage($request, $package);

        $package->update($validated);

        return $this->ok($package);
    }

    /**
     * Activate / deactivate: inactive packages can't be bought, but students who have it keep it.
     */
    public function setActive(Request $request, $id)
    {
        $validated = $request->validate(['is_active' => 'required|boolean']);
        $package = Package::findOrFail($id);

        if ($validated['is_active'] && (float) $package->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Asigna un precio al paquete antes de activarlo.',
            ], 422);
        }

        $package->update(['is_active' => $validated['is_active']]);

        return $this->ok($package);
    }

    /**
     * Only packages nobody has bought can be deleted; otherwise deactivate them.
     */
    public function destroy($id)
    {
        $package = Package::withCount('students')->findOrFail($id);

        if ($package->students_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: ' . $package->students_count . ' estudiante(s) compraron este paquete. Desactívalo para que no se pueda vender.',
            ], 422);
        }

        $package->delete();

        return response()->json(['success' => true]);
    }

    private function validatePackage(Request $request, ?Package $package = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('packages', 'name')->ignore($package?->id)],
            'description' => 'nullable|string|max:255',
            'lessons' => 'required|integer|min:0|max:200',
            'price' => 'required|numeric|min:0|max:99999.99',
            'is_active' => 'required|boolean',
        ], [
            'name.unique' => 'Ya existe un paquete con ese nombre.',
        ]);

        if ($validated['is_active'] && (float) $validated['price'] <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'price' => 'Un paquete activo debe tener un precio mayor a $0.',
            ]);
        }

        $validated['name'] = trim($validated['name']);

        return $validated;
    }

    private function ok(Package $package)
    {
        return response()->json([
            'success' => true,
            'package' => $package->loadCount('students')->toDashboardArray(),
        ]);
    }
}
