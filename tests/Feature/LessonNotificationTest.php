<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\LessonNotification;
use App\Support\LessonCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LessonNotificationTest extends TestCase
{
    use RefreshDatabase;

    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->tomorrow = Carbon::now(config('app.school_timezone'))->addDay()->format('Y-m-d');
        config(['services.twilio.sid' => null, 'services.twilio.token' => null, 'services.twilio.from' => null]);
    }

    private function makeStudent(string $phone = '(212) 555-0100'): Student
    {
        static $n = 0;
        $n++;
        $package = Package::create(['name' => "Pkg {$n}", 'lessons' => 10, 'price' => 100, 'is_active' => true]);

        $student = new Student([
            'first_name' => 'Ana' . $n, 'last_name' => 'Test', 'address' => 'x', 'city' => 'NY', 'state' => 'NY',
            'zip_code' => '10001', 'phone' => $phone, 'email' => "ana{$n}@test.com",
            'transmission' => 'Automático', 'paid_amount' => 0,
        ]);
        $student->purchasePackage($package);
        $student->save();

        return $student;
    }

    private function makeInstructor(): Instructor
    {
        static $n = 0;
        $n++;

        return Instructor::create([
            'first_name' => 'Ivan' . $n, 'last_name' => 'Teacher', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '10001', 'phone' => '646-555-0199',
            'email' => "ivan{$n}@test.com",
        ]);
    }

    private function book(Student $s, Instructor $i, string $time = '10:00', ?string $date = null)
    {
        return $this->postJson('/lessons', [
            'student_id' => $s->id, 'instructor_id' => $i->id,
            'date' => $date ?? $this->tomorrow, 'time' => $time, 'duration_minutes' => 45,
        ]);
    }

    private function sentTo(string $email, string $event): \Illuminate\Support\Collection
    {
        return Notification::sent(new AnonymousNotifiable, LessonNotification::class)
            ->filter(fn ($n) => $n->event === $event);
    }

    public function test_booking_notifies_student_and_instructor_with_calendar_invitation(): void
    {
        Notification::fake();
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();

        $res = $this->book($student, $instructor)->assertOk();

        $this->assertCount(2, $res->json('notifications.sent'));
        Notification::assertSentOnDemand(LessonNotification::class, function ($n, $channels, $notifiable) use ($student) {
            return $n->event === LessonNotification::SCHEDULED && $n->role === 'student'
                && $notifiable->routes['mail'] === $student->email && $channels === ['mail'];
        });
        Notification::assertSentOnDemand(LessonNotification::class, function ($n, $channels, $notifiable) use ($instructor) {
            return $n->role === 'instructor' && $notifiable->routes['mail'] === $instructor->email;
        });
    }

    public function test_invitation_email_has_ics_in_school_time(): void
    {
        config(['app.school_timezone' => 'America/New_York']);
        $lesson = Lesson::create([
            'student_id' => $this->makeStudent()->id, 'instructor_id' => $this->makeInstructor()->id,
            'starts_at' => '2026-12-01 10:00:00', 'ends_at' => '2026-12-01 10:45:00', 'duration_minutes' => 45,
            'status' => Lesson::STATUS_SCHEDULED, 'notes' => 'Traer licencia; puntual',
        ]);

        $ics = LessonCalendar::ics($lesson, 'student', 'ana@test.com');
        $this->assertStringContainsString("METHOD:REQUEST\r\n", $ics);
        $this->assertStringContainsString('DTSTART:20261201T150000Z', $ics); // 10:00 New York = 15:00 UTC
        $this->assertStringContainsString('DTEND:20261201T154500Z', $ics);
        $this->assertStringContainsString('UID:lesson-' . $lesson->id . '@', $ics);
        $this->assertStringContainsString('TRIGGER:-P1D', $ics);
        $this->assertStringContainsString('TRIGGER:-PT10M', $ics);
        $this->assertStringContainsString('Traer licencia\; puntual', str_replace("\r\n ", '', $ics));
        foreach (explode("\r\n", $ics) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }

        $mail = (new LessonNotification($lesson, LessonNotification::SCHEDULED, 'student'))
            ->toMail(Notification::route('mail', 'ana@test.com'));
        $this->assertStringContainsString('agendada', $mail->subject);
        $this->assertSame('clase.ics', $mail->rawAttachments[0]['name']);
        $this->assertStringContainsString('method=REQUEST', $mail->rawAttachments[0]['options']['mime']);
        $this->assertStringContainsString('calendar.google.com', $mail->actionUrl);

        $lesson->calendar_sequence = 3;
        $cancel = LessonCalendar::ics($lesson, 'student', 'ana@test.com', 'CANCEL');
        $this->assertStringContainsString('METHOD:CANCEL', $cancel);
        $this->assertStringContainsString('STATUS:CANCELLED', $cancel);
        $this->assertStringContainsString('SEQUENCE:3', $cancel);
    }

    public function test_reschedule_updates_event_and_changed_instructor_gets_cancellation(): void
    {
        Notification::fake();
        $student = $this->makeStudent();
        $old = $this->makeInstructor();
        $new = $this->makeInstructor();
        $id = $this->book($student, $old)->json('lesson.db_id');

        $this->putJson("/lessons/{$id}", [
            'instructor_id' => $new->id, 'date' => $this->tomorrow, 'time' => '12:00', 'duration_minutes' => 45,
        ])->assertOk();

        $this->assertSame(1, Lesson::find($id)->calendar_sequence);
        Notification::assertSentOnDemand(LessonNotification::class, fn ($n, $c, $to) =>
            $n->event === LessonNotification::RESCHEDULED && $to->routes['mail'] === $student->email);
        Notification::assertSentOnDemand(LessonNotification::class, fn ($n, $c, $to) =>
            $n->event === LessonNotification::SCHEDULED && $to->routes['mail'] === $new->email);
        Notification::assertSentOnDemand(LessonNotification::class, fn ($n, $c, $to) =>
            $n->event === LessonNotification::CANCELLED && $to->routes['mail'] === $old->email
            && $n->lesson->starts_at->format('H:i') === '10:00' && $n->lesson->calendar_sequence === 1);
    }

    public function test_cancel_sends_cancellation_but_complete_does_not_notify(): void
    {
        Notification::fake();
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        $a = $this->book($student, $instructor, '09:00')->json('lesson.db_id');
        $b = $this->book($student, $instructor, '11:00')->json('lesson.db_id');

        $this->putJson("/lessons/{$a}/status", ['status' => 'cancelled'])->assertOk()->assertJsonCount(2, 'notifications.sent');
        $this->putJson("/lessons/{$b}/status", ['status' => 'completed'])->assertOk()->assertJsonPath('notifications', null);

        $this->assertCount(2, $this->sentTo('', LessonNotification::CANCELLED));
        $this->assertSame(1, Lesson::find($a)->calendar_sequence);
    }

    public function test_sms_is_sent_through_twilio_when_configured(): void
    {
        config(['services.twilio.sid' => 'AC123', 'services.twilio.token' => 'secret', 'services.twilio.from' => '+15550001111']);
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM1'], 201)]);

        $res = $this->book($this->makeStudent(), $this->makeInstructor())->assertOk();

        $this->assertSame(['estudiante (correo + SMS)', 'instructor (correo + SMS)'], $res->json('notifications.sent'));
        Http::assertSent(fn ($req) => str_contains($req->url(), 'Accounts/AC123/Messages.json')
            && $req['To'] === '+12125550100' && str_contains($req['Body'], 'agendada'));
        Http::assertSent(fn ($req) => $req['To'] === '+16465550199');
    }

    public function test_failed_delivery_does_not_block_the_booking(): void
    {
        config(['services.twilio.sid' => 'AC123', 'services.twilio.token' => 'secret', 'services.twilio.from' => '+15550001111']);
        Http::fake(['api.twilio.com/*' => Http::response(['message' => 'invalid number'], 400)]);

        $res = $this->book($this->makeStudent(), $this->makeInstructor())->assertOk();

        $this->assertSame(1, Lesson::count());
        $this->assertSame(['estudiante', 'instructor'], $res->json('notifications.failed'));
    }

    public function test_phone_numbers_are_normalized(): void
    {
        $this->assertSame('+12125550100', SmsChannel::normalize('(212) 555-0100'));
        $this->assertSame('+12125550100', SmsChannel::normalize('1-212-555-0100'));
        $this->assertSame('+584141234567', SmsChannel::normalize('+58 414 123 4567'));
        $this->assertNull(SmsChannel::normalize('555'));
        $this->assertNull(SmsChannel::normalize(null));
    }

    public function test_reminders_are_sent_once_for_lessons_within_the_window(): void
    {
        config(['app.lesson_reminder_hours' => 24]);
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        $now = Carbon::now(config('app.school_timezone'));
        $make = fn (Carbon $start, string $status = Lesson::STATUS_SCHEDULED) => Lesson::create([
            'student_id' => $student->id, 'instructor_id' => $instructor->id,
            'starts_at' => $start->format('Y-m-d H:i:00'), 'ends_at' => $start->copy()->addMinutes(45)->format('Y-m-d H:i:00'),
            'duration_minutes' => 45, 'status' => $status,
        ]);

        $soon = $make($now->copy()->addHours(3));
        $later = $make($now->copy()->addDays(3));
        $cancelled = $make($now->copy()->addHours(5), Lesson::STATUS_CANCELLED);

        Notification::fake();
        $this->artisan('lessons:send-reminders')->assertSuccessful();

        $this->assertNotNull($soon->fresh()->reminder_sent_at);
        $this->assertNull($later->fresh()->reminder_sent_at);
        $this->assertNull($cancelled->fresh()->reminder_sent_at);
        $this->assertCount(2, $this->sentTo('', LessonNotification::REMINDER));

        // Running again doesn't repeat the reminder
        $this->artisan('lessons:send-reminders')->assertSuccessful();
        $this->assertCount(2, $this->sentTo('', LessonNotification::REMINDER));

        $sms = (new LessonNotification($soon, LessonNotification::REMINDER, 'student'))->toSms(new AnonymousNotifiable);
        $this->assertStringContainsString('Recordatorio', $sms);
    }

    public function test_last_minute_reminder_is_sent_10_minutes_before(): void
    {
        config(['app.lesson_reminder_hours' => 24, 'app.lesson_final_reminder_minutes' => 10]);
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        $now = Carbon::now(config('app.school_timezone'))->startOfMinute();
        $make = fn (Carbon $start) => Lesson::create([
            'student_id' => $student->id, 'instructor_id' => $instructor->id,
            'starts_at' => $start->format('Y-m-d H:i:00'), 'ends_at' => $start->copy()->addMinutes(45)->format('Y-m-d H:i:00'),
            'duration_minutes' => 45, 'status' => Lesson::STATUS_SCHEDULED,
        ]);

        // Day-before reminder already sent; now it is 8 minutes before the lesson
        $soon = $make($now->copy()->addMinutes(8));
        $soon->forceFill(['reminder_sent_at' => now()->subDay()])->save();
        // Booked at the last minute: only the last-minute reminder goes out
        $lastMinute = $make($now->copy()->addMinutes(9));
        // Still 2 hours away: day-before reminder only
        $later = $make($now->copy()->addHours(2));

        Notification::fake();
        $this->artisan('lessons:send-reminders')->assertSuccessful();

        $this->assertNotNull($soon->fresh()->final_reminder_sent_at);
        $this->assertNotNull($lastMinute->fresh()->final_reminder_sent_at);
        $this->assertNotNull($lastMinute->fresh()->reminder_sent_at);
        $this->assertNull($later->fresh()->final_reminder_sent_at);
        $this->assertNotNull($later->fresh()->reminder_sent_at);
        $this->assertCount(4, $this->sentTo('', LessonNotification::FINAL_REMINDER)); // 2 lessons x (student + instructor)
        $this->assertCount(2, $this->sentTo('', LessonNotification::REMINDER));       // only $later

        $this->artisan('lessons:send-reminders')->assertSuccessful();
        $this->assertCount(4, $this->sentTo('', LessonNotification::FINAL_REMINDER));

        $notification = new LessonNotification($soon, LessonNotification::FINAL_REMINDER, 'student');
        $this->assertMatchesRegularExpression('/empieza en (7|8) minutos/', $notification->toSms(new AnonymousNotifiable));
        $mail = $notification->toMail(Notification::route('mail', 'ana@test.com'));
        $this->assertStringContainsString('empieza en', $mail->subject);
        $this->assertSame([], $mail->rawAttachments); // no new calendar invitation in reminders

        // Rescheduling resets both reminders
        $this->putJson("/lessons/{$later->id}", [
            'instructor_id' => $instructor->id, 'date' => $now->copy()->addDays(2)->format('Y-m-d'), 'time' => '10:00', 'duration_minutes' => 45,
        ])->assertOk();
        $this->assertNull($later->fresh()->reminder_sent_at);
        $this->assertNull($later->fresh()->final_reminder_sent_at);
    }
}
