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
        // 1. TABLA PRINCIPAL: Ficha y datos del Estudiante
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Nombres y Apellidos
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable(); // Puede ser opcional
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
            $table->string('height', 10)->nullable();       // Almacena formatos como "5'11\"" o "1.80m"
            $table->string('eye_color', 30)->nullable();    // Ej: BLU, BRN, GRN, HAZ
            $table->boolean('is_organ_donor')->default(false);

            // Foto de Perfil del Estudiante (Almacena la ruta en el storage local/público)
            $table->string('student_photo_url')->nullable();

            // Datos de Inscripción y Paquete
            $table->string('package', 100);
            $table->string('transmission', 20); // Automático / Mecánico
            $table->decimal('plan_cost', 8, 2)->default(0);
            $table->decimal('paid_amount', 8, 2)->default(0);
            $table->decimal('debt_amount', 8, 2)->default(0);

            // Control de Estado interno de la academia
            $table->string('status')->default('active'); // active, graduated, suspended
            $table->timestamps();
            $table->softDeletes(); // Borrado lógico para proteger el histórico del alumno
        });

        // 2. TABLA SECUNDARIA: Registro e Historial de Licencias
        Schema::create('student_licenses', function (Blueprint $table) {
            $table->id();
            // Llave foránea vinculada al alumno con borrado en cascada
            $table->foreignId('student_id')->constrained()->onDelete('cascade');

            // Datos del documento físico de manejo
            $table->string('license_number', 50)->unique();
            $table->date('expiration_date');
            $table->string('license_class', 10); // Ej: Class D, Class E, Commercial, etc.

            // Copia Digital / Foto de la Licencia
            $table->string('license_photo_url')->nullable(); // Almacena la ruta en storage

            $table->boolean('is_active')->default(true); // Indica si es la licencia vigente del alumno
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_licenses');
        Schema::dropIfExists('students');
    }
};
