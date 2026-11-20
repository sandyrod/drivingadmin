<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paquetes adicionales comprados por un estudiante (cargos del Ledger, cada uno con su factura).
 *
 * El paquete de la inscripción sigue en students (package, package_id). students.plan_cost y
 * students.package_lessons guardan el TOTAL (inscripción + adicionales), así la deuda y las
 * clases disponibles se siguen calculando igual que antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            // Copia del paquete al momento de la compra
            $table->string('description', 150);
            $table->unsignedSmallInteger('lessons')->default(0);
            $table->decimal('amount', 8, 2);
            $table->date('charge_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_charges');
    }
};
