<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABLA PRINCIPAL: Ficha y datos del Instructor
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();

            // Nombres y Apellidos
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);

            // Datos Personales
            $table->date('birth_date');
            $table->char('gender', 1); // M / F / O

            // Ubicación y Dirección Física
            $table->string('address', 255);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip_code', 20);

            // Datos de Contacto
            $table->string('phone', 30);
            $table->string('email')->unique();

            // Características Físicas / Médicas (Tipificadas según New York DMV)
            $table->string('height', 10)->nullable();
            $table->string('eye_color', 30)->nullable();
            $table->boolean('is_organ_donor')->default(false);

            // Foto de Perfil del Instructor
            $table->string('instructor_photo_url')->nullable();

            // Datos específicos de Instructores
            $table->string('vehicle', 100)->nullable();
            $table->string('plate', 20)->nullable();
            $table->string('transmission', 20)->nullable(); // Automático / Mecánico
            $table->string('shift', 50)->nullable();        // Turno Horario (ej: Mañana (8am-12pm))

            // Control de Estado interno de la academia
            $table->string('status')->default('Disponible'); // Disponible, En Clase, Suspendido, etc.

            $table->timestamps();
            $table->softDeletes(); // Borrado lógico
        });

        // 2. TABLA SECUNDARIA: Registro e Historial de Licencias de Instructores
        Schema::create('instructor_licenses', function (Blueprint $table) {
            $table->id();
            // Llave foránea vinculada al instructor con borrado en cascada
            $table->foreignId('instructor_id')->constrained()->onDelete('cascade');

            // Datos del documento físico de manejo
            $table->string('license_number', 50)->unique();
            $table->date('expiration_date');
            $table->string('license_class', 10); // Ej: Class D, Class E, CDL, etc.

            // Copia Digital / Foto de la Licencia
            $table->string('license_photo_url')->nullable();
            $table->string('license_photo_back_url')->nullable();

            $table->boolean('is_active')->default(true); // Indica si es la licencia vigente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_licenses');
        Schema::dropIfExists('instructors');
    }
};
