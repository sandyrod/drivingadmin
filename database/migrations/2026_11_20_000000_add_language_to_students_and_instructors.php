<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Preferred language (es / en) for the emails, SMS and pages each person receives */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('language', 2)->default('es')->after('email');
        });
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('language', 2)->default('es')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('students', fn (Blueprint $table) => $table->dropColumn('language'));
        Schema::table('instructors', fn (Blueprint $table) => $table->dropColumn('language'));
    }
};
