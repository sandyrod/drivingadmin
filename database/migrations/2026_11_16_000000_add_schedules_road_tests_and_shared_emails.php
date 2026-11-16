<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - Instructors: editable working schedule (days of the week + start/end time) instead of a fixed shift.
 * - Lessons: "type" (driving lesson or road test) and the road test location.
 * - Students: the email no longer has to be unique (e.g. siblings sharing a parent's email).
 */
return new class extends Migration
{
    /** Old fixed shifts => working hours. */
    private const SHIFTS = [
        'Mañana (8am-12pm)' => ['08:00:00', '12:00:00'],
        'Tarde (1pm-5pm)' => ['13:00:00', '17:00:00'],
        'Completo (8am-5pm)' => ['08:00:00', '17:00:00'],
    ];

    public function up(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            // ISO days of the week the instructor works, e.g. "1,2,3,4,5" (1 = Monday ... 7 = Sunday)
            $table->string('work_days', 20)->nullable()->after('shift');
            $table->time('work_start')->nullable()->after('work_days');
            $table->time('work_end')->nullable()->after('work_start');
        });

        // Keep the hours of the old shift; every day of the week until the school edits it
        foreach (self::SHIFTS as $shift => [$start, $end]) {
            DB::table('instructors')->where('shift', $shift)->update([
                'work_days' => '1,2,3,4,5,6,7',
                'work_start' => $start,
                'work_end' => $end,
            ]);
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('type', 20)->default('lesson')->after('vehicle_id'); // lesson | road_test
            $table->string('location', 255)->nullable()->after('type');       // road test site
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_email_unique');
            $table->index('email');
        });
    }

    public function down(): void
    {
        // Fails if duplicated emails were saved in the meantime (they must be fixed first)
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->unique('email');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['type', 'location']);
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn(['work_days', 'work_start', 'work_end']);
        });
    }
};
