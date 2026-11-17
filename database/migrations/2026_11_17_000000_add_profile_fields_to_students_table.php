<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('suffix', 10)->nullable()->after('last_name');
            $table->string('apt', 20)->nullable()->after('address');
            $table->string('other_phone', 30)->nullable()->after('phone');
            $table->string('other_phone_desc', 50)->nullable()->after('other_phone');
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('parent_name', 150)->nullable()->after('secondary_email');
            $table->string('parent_phone', 30)->nullable()->after('parent_name');
            $table->string('high_school', 150)->nullable()->after('parent_phone');
            $table->string('restrictions', 100)->nullable()->after('is_organ_donor');
            $table->string('endorsements', 100)->nullable()->after('restrictions');
            // 5-hour pre-licensing course certificate (MV-278)
            $table->string('certificate_number', 50)->nullable()->after('endorsements');
            $table->date('certificate_expiry')->nullable()->after('certificate_number');
            $table->string('pickup_location', 255)->nullable()->after('certificate_expiry');
            $table->foreignId('preferred_instructor_id')->nullable()->after('pickup_location')
                ->constrained('instructors')->nullOnDelete();
            $table->boolean('is_problematic')->default(false)->after('preferred_instructor_id');
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notes');

        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_instructor_id');
            $table->dropColumn([
                'suffix', 'apt', 'other_phone', 'other_phone_desc', 'secondary_email', 'parent_name', 'parent_phone',
                'high_school', 'restrictions', 'endorsements', 'certificate_number', 'certificate_expiry',
                'pickup_location', 'is_problematic',
            ]);
        });
    }
};
