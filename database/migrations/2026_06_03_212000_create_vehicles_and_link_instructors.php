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
        // 1. Create vehicles table
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 100);
            $table->string('plate', 20)->unique();
            $table->string('transmission', 20); // Automático / Mecánico
            $table->string('status')->default('Disponible'); // Disponible, En Clase, En Taller
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Link instructors to vehicles via foreign key
        Schema::table('instructors', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });

        Schema::dropIfExists('vehicles');
    }
};
