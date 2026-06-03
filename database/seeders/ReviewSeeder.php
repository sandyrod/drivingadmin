<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Instructor;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $luis = Instructor::where('first_name', 'Luis')->where('last_name', 'Rodríguez')->first();
        $pedro = Instructor::where('first_name', 'Pedro')->where('last_name', 'Fuentes')->first();
        $sarah = Instructor::where('first_name', 'Sarah')->where('last_name', 'Evans')->first();

        if ($luis) {
            Review::create([
                'instructor_id' => $luis->id,
                'rating' => 5,
                'vehicle_rating' => 5,
                'comment' => 'Excelente paciencia al enseñar, muy profesional en todo.',
            ]);
        }

        if ($pedro) {
            Review::create([
                'instructor_id' => $pedro->id,
                'rating' => 2,
                'vehicle_rating' => 3,
                'comment' => 'El aire acondicionado del carro no enfriaba bien durante la clase práctica.',
            ]);
        }

        if ($sarah) {
            Review::create([
                'instructor_id' => $sarah->id,
                'rating' => 5,
                'vehicle_rating' => 5,
                'comment' => 'La mejor instructora de Nueva York. Aprobé el examen de ruta en mi primer intento.',
            ]);
        }
    }
}
