<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clases de manejo agendadas para cada estudiante.
 * Las fechas se guardan en hora local de la escuela (config app.school_timezone).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('duration_minutes');

            // scheduled (programada) | completed (completada) | cancelled (cancelada) | no_show (no asistió)
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'starts_at']);
            $table->index(['student_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
