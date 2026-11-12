<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de paquetes (servicios) que puede comprar un estudiante.
 *
 * - "lessons" = cantidad de clases de manejo incluidas; cada clase (hora de servicio) dura 45 minutos.
 *   0 = el paquete no incluye manejo (ej. curso teórico Pre-Licensing).
 * - Al comprar, el estudiante guarda una copia del nombre, precio y clases del paquete
 *   (students.package, students.plan_cost, students.package_lessons). Así, si luego se cambia
 *   el paquete en el catálogo, solo afecta a las compras nuevas.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('lessons')->default(0);   // Clases de manejo de 45 min
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $row = fn ($name, $lessons, $price, $active, $description = null) => [
            'name' => $name, 'description' => $description, 'lessons' => $lessons, 'price' => $price,
            'is_active' => $active, 'created_at' => $now, 'updated_at' => $now,
        ];

        DB::table('packages')->insert([
            // Catálogo actual de la escuela. Se crean inactivos y sin precio:
            // se activan desde la pantalla "Paquetes" al asignarles su precio.
            $row('1 Driving Lesson', 1, 0, false),
            $row('Single Lesson', 1, 0, false),
            $row('5 Driving Lesson', 5, 0, false),
            $row('EXPERTOS package 03 Lessons', 3, 0, false),
            $row('EXPERTOS package 04 Lessons', 4, 0, false),
            $row('PRINCIPIANTE package 12 Lessons', 12, 0, false),
            $row('PRINCIPIANTE package 15 Lessons', 15, 0, false),
            $row('Pre-Licensing Class 5 HOURS MOVIES', 0, 0, false, 'Curso teórico obligatorio (no incluye clases de manejo)'),

            // Paquetes del formulario anterior: se conservan inactivos para que los
            // estudiantes que ya los tienen sigan vinculados a su paquete.
            $row('Package 1 (5 Hours + Test)', 5, 180, false, '5 Hours Lessons & Road Test'),
            $row('Package 2 (10 Hours + Test)', 10, 350, false, '10 Hours Lessons & Car Rental'),
            $row('Package 3 (15 Hours Full Course)', 15, 500, false, '15 Hours Premium'),
            $row('5-Hour Pre-Licensing Class Only', 0, 40, false, 'Mandatory 5-Hour Course Only'),
        ]);

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('student_photo_url')->constrained('packages')->nullOnDelete();
            // Copia de las clases incluidas al momento de la compra
            $table->unsignedSmallInteger('package_lessons')->nullable()->after('package_id');
            $table->string('package', 100)->nullable()->change();
        });

        // Vincular estudiantes existentes cuyo paquete coincide con uno del catálogo
        foreach (DB::table('packages')->get(['id', 'name', 'lessons']) as $package) {
            DB::table('students')->where('package', $package->name)->update([
                'package_id' => $package->id,
                'package_lessons' => $package->lessons,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
            $table->dropColumn('package_lessons');
        });

        Schema::dropIfExists('packages');
    }
};
