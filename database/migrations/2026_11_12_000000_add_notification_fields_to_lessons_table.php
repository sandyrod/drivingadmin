<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Version of the calendar event (iCalendar SEQUENCE): increases on every reschedule/cancel
            // so calendar apps replace the previous event instead of creating a new one.
            $table->unsignedInteger('calendar_sequence')->default(0)->after('notes');
            // When the "your lesson is coming up" reminder was sent (null = not sent yet)
            $table->timestamp('reminder_sent_at')->nullable()->after('calendar_sequence');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['calendar_sequence', 'reminder_sent_at']);
        });
    }
};
