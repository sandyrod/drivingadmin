<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\InstructorLicense;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch seeded vehicles
        $v1 = \App\Models\Vehicle::where('plate', 'AB123CD')->first();
        $v2 = \App\Models\Vehicle::where('plate', 'EF456GH')->first();
        $v3 = \App\Models\Vehicle::where('plate', 'JK789LM')->first();

        // 1. Luis Rodríguez
        $ins1 = Instructor::create([
            'first_name' => 'Luis',
            'last_name' => 'Rodríguez',
            'birth_date' => '1985-05-15',
            'gender' => 'M',
            'address' => '123 Main St',
            'city' => 'Queens',
            'state' => 'NY',
            'zip_code' => '11101',
            'phone' => '+1 914-555-7788',
            'email' => 'luis.rodriguez@example.com',
            'vehicle' => 'Toyota Corolla',
            'plate' => 'AB123CD',
            'transmission' => 'Automático',
            'shift' => 'Mañana (8am-12pm)',
            'status' => 'Disponible',
            'is_organ_donor' => true,
            'vehicle_id' => $v1 ? $v1->id : null,
        ]);

        InstructorLicense::create([
            'instructor_id' => $ins1->id,
            'license_number' => 'INS-LIC-001',
            'expiration_date' => '2030-12-31',
            'license_class' => 'Class D',
            'is_active' => true,
        ]);

        // 2. Pedro Fuentes
        $ins2 = Instructor::create([
            'first_name' => 'Pedro',
            'last_name' => 'Fuentes',
            'birth_date' => '1990-08-20',
            'gender' => 'M',
            'address' => '456 Broadway',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip_code' => '11201',
            'phone' => '+1 914-555-9900',
            'email' => 'pedro.fuentes@example.com',
            'vehicle' => 'Chevrolet Aveo',
            'plate' => 'EF456GH',
            'transmission' => 'Mecánico',
            'shift' => 'Tarde (1pm-5pm)',
            'status' => 'En Clase',
            'is_organ_donor' => false,
            'vehicle_id' => $v2 ? $v2->id : null,
        ]);

        InstructorLicense::create([
            'instructor_id' => $ins2->id,
            'license_number' => 'INS-LIC-002',
            'expiration_date' => '2030-12-31',
            'license_class' => 'Class D',
            'is_active' => true,
        ]);

        // 3. Sarah Evans
        $ins3 = Instructor::create([
            'first_name' => 'Sarah',
            'last_name' => 'Evans',
            'birth_date' => '1988-02-10',
            'gender' => 'F',
            'address' => '789 Elm St',
            'city' => 'Manhattan',
            'state' => 'NY',
            'zip_code' => '10001',
            'phone' => '+1 718-555-1122',
            'email' => 'sarah.evans@example.com',
            'vehicle' => 'Nissan Sentra',
            'plate' => 'JK789LM',
            'transmission' => 'Automático',
            'shift' => 'Mañana (8am-12pm)',
            'status' => 'Disponible',
            'is_organ_donor' => true,
            'vehicle_id' => $v3 ? $v3->id : null,
        ]);

        InstructorLicense::create([
            'instructor_id' => $ins3->id,
            'license_number' => 'INS-LIC-003',
            'expiration_date' => '2030-12-31',
            'license_class' => 'Class D',
            'is_active' => true,
        ]);
    }
}
