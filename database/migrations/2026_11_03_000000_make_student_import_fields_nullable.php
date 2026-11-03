<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los registros históricos importados desde el reporte de estudiantes
 * no siempre traen email, fecha de nacimiento o sexo.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->char('gender', 1)->nullable()->change();
        });

        Schema::table('student_licenses', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_licenses', function (Blueprint $table) {
            $table->date('expiration_date')->nullable(false)->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->char('gender', 1)->nullable(false)->change();
        });
    }
};
