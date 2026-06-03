<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vehicle::create([
            'brand' => 'Toyota Corolla (2022)',
            'plate' => 'AB123CD',
            'transmission' => 'Automático',
            'status' => 'Disponible',
        ]);

        Vehicle::create([
            'brand' => 'Chevrolet Aveo (2021)',
            'plate' => 'EF456GH',
            'transmission' => 'Mecánico',
            'status' => 'En Clase',
        ]);

        Vehicle::create([
            'brand' => 'Nissan Sentra (2023)',
            'plate' => 'JK789LM',
            'transmission' => 'Automático',
            'status' => 'Disponible',
        ]);
    }
}
