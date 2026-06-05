<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('student_licenses')) {
            Schema::table('student_licenses', function (Blueprint $table) {
                if (!Schema::hasColumn('student_licenses', 'license_photo_back_url')) {
                    $table->string('license_photo_back_url')->nullable()->after('license_photo_url');
                }
            });
        }

        if (Schema::hasTable('instructor_licenses')) {
            Schema::table('instructor_licenses', function (Blueprint $table) {
                if (!Schema::hasColumn('instructor_licenses', 'license_photo_back_url')) {
                    $table->string('license_photo_back_url')->nullable()->after('license_photo_url');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_licenses')) {
            Schema::table('student_licenses', function (Blueprint $table) {
                if (Schema::hasColumn('student_licenses', 'license_photo_back_url')) {
                    $table->dropColumn('license_photo_back_url');
                }
            });
        }

        if (Schema::hasTable('instructor_licenses')) {
            Schema::table('instructor_licenses', function (Blueprint $table) {
                if (Schema::hasColumn('instructor_licenses', 'license_photo_back_url')) {
                    $table->dropColumn('license_photo_back_url');
                }
            });
        }
    }
};
