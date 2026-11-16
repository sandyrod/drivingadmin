<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\User;
use App\Notifications\LessonNotification;
use App\Support\LessonCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RoadTestAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $day;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Notification::fake();
        // Next Monday, so the day of the week is known
        $this->day = Carbon::now(config('app.school_timezone'))->next(Carbon::MONDAY);
    }

    private function makeStudent(int $lessons = 5): Student
    {
        static $n = 0;
        $n++;
        $student = new Student([
            'first_name' => 'Ana' . $n, 'last_name' => 'Test', 'address' => 'x', 'city' => 'NY', 'state' => 'NY',
            'zip_code' => '10001', 'phone' => '555', 'email' => "ana{$n}@test.com",
            'transmission' => 'Automático', 'paid_amount' => 0,
        ]);
        if ($lessons >= 0) {
            $student->purchasePackage(Package::create(['name' => "Pkg {$n}", 'lessons' => $lessons, 'price' => 300, 'is_active' => true]));
        }
        $student->save();

        return $student;
    }

    private function makeInstructor(array $attrs = []): Instructor
    {
        static $n = 0;
        $n++;

        return Instructor::create($attrs + [
            'first_name' => 'Ivan' . $n, 'last_name' => 'Teacher', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '10001', 'phone' => '555',
            'email' => "ivan{$n}@test.com",
        ]);
    }

    private function book(Student $s, Instructor $i, string $time, int $minutes = 45, array $extra = [], ?Carbon $day = null)
    {
        return $this->postJson('/lessons', $extra + [
            'student_id' => $s->id, 'instructor_id' => $i->id,
            'date' => ($day ?? $this->day)->format('Y-m-d'), 'time' => $time, 'duration_minutes' => $minutes,
        ]);
    }

    private function roadTest(Student $s, Instructor $i, string $time, int $minutes = 60)
    {
        return $this->book($s, $i, $time, $minutes, ['type' => 'road_test', 'location' => 'DMV Road Test Site, 30-56 Whitestone Expy, Queens, NY']);
    }

    // ---------------- Instructor schedule ----------------

    public function test_instructor_schedule_is_saved_and_shown(): void
    {
        $res = $this->postJson('/instructors', [
            'first_name' => 'Luis', 'last_name' => 'Gómez', 'birth_date' => '1985-02-02', 'gender' => 'M',
            'address' => '1 Main', 'city' => 'Queens', 'state' => 'NY', 'zip_code' => '11372',
            'phone' => '555-0101', 'email' => 'luis@test.com',
            'work_days' => [5, 1, 2, 3, 4], 'work_start' => '09:00', 'work_end' => '15:30',
        ])->assertOk();

        $this->assertSame('Lun–Vie · 09:00–15:30', $res->json('instructor.shift'));
        $this->assertSame([1, 2, 3, 4, 5], $res->json('instructor.work_days'));

        $id = $res->json('instructor.db_id');
        $this->putJson("/instructors/{$id}", [
            'first_name' => 'Luis', 'last_name' => 'Gómez', 'birth_date' => '1985-02-02', 'gender' => 'M',
            'address' => '1 Main', 'city' => 'Queens', 'state' => 'NY', 'zip_code' => '11372',
            'phone' => '555-0101', 'email' => 'luis@test.com',
            'work_days' => [1, 3, 6], 'work_start' => '08:00', 'work_end' => '12:00',
        ])->assertOk()->assertJsonPath('instructor.shift', 'Lun, Mié, Sáb · 08:00–12:00');

        $this->postJson('/instructors', [
            'first_name' => 'X', 'last_name' => 'Y', 'birth_date' => '1985-02-02', 'gender' => 'M',
            'address' => '1', 'city' => 'Q', 'state' => 'NY', 'zip_code' => '1', 'phone' => '1', 'email' => 'x@test.com',
            'work_days' => [1], 'work_start' => '15:00', 'work_end' => '09:00',
        ])->assertJsonValidationErrors('work_end');
    }

    public function test_lessons_must_fit_in_the_instructor_schedule(): void
    {
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor(['work_days' => '1,2,3,4,5', 'work_start' => '08:00', 'work_end' => '12:00']);

        $this->book($student, $instructor, '07:30')->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'fuera del horario'));
        $this->book($student, $instructor, '11:30')->assertStatus(422);              // would end at 12:15
        $this->book($student, $instructor, '11:15')->assertOk();                     // ends exactly at 12:00
        $this->book($student, $instructor, '09:00', 45, [], $this->day->copy()->next(Carbon::SATURDAY))->assertStatus(422);

        // Instructors without a schedule can be booked any time
        $this->book($student, $this->makeInstructor(), '20:00')->assertOk();
    }

    // ---------------- Road tests ----------------

    public function test_road_test_needs_location_and_does_not_use_package_lessons(): void
    {
        $student = $this->makeStudent(1);
        $instructor = $this->makeInstructor();

        $this->book($student, $instructor, '09:00', 60, ['type' => 'road_test'])->assertJsonValidationErrors('location');
        $this->book($student, $instructor, '09:00', 45, ['type' => 'road_test', 'location' => 'DMV'])->assertOk(); // 45 is a valid road test length

        $res = $this->roadTest($student, $instructor, '13:00')->assertOk();
        $this->assertSame('road_test', $res->json('lesson.type'));
        $this->assertStringContainsString('Whitestone', $res->json('lesson.location'));
        $this->assertSame(0, $res->json('student.used_minutes'));

        // The single package lesson is still available
        $this->book($student, $instructor, '16:00')->assertOk();

        // Students without a package can still have a road test
        $this->roadTest($this->makeStudent(-1), $instructor, '18:00')->assertOk();
    }

    public function test_road_test_needs_one_hour_free_before_and_after(): void
    {
        $student = $this->makeStudent();
        $other = $this->makeStudent();
        $instructor = $this->makeInstructor();

        $this->roadTest($student, $instructor, '10:00')->assertOk();                 // 10:00 - 11:00

        // Instructor: nothing between 09:00 and 12:00
        $this->book($other, $instructor, '11:30')->assertStatus(422)
            ->assertJsonPath('message', 'El instructor necesita al menos 1 hora libre entre un Road Test y otra clase.');
        $this->book($other, $instructor, '08:30')->assertStatus(422);               // ends 09:15
        $this->book($other, $instructor, '12:00')->assertOk();                       // exactly 1 h after
        $this->book($other, $instructor, '08:15')->assertOk();                       // ends 09:00, exactly 1 h before

        // Student: another instructor doesn't help
        $this->book($student, $this->makeInstructor(), '11:15')->assertStatus(422)
            ->assertJsonPath('message', 'El estudiante necesita al menos 1 hora libre entre un Road Test y otra clase.');

        // A new road test also keeps the gap from regular lessons
        $this->roadTest($this->makeStudent(), $instructor, '13:00')->assertStatus(422); // 12:00-12:45 lesson ends < 1h before
        $this->roadTest($this->makeStudent(), $instructor, '13:45')->assertOk();
    }

    public function test_road_test_reschedule_keeps_type_and_notifications_show_the_site(): void
    {
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        $id = $this->roadTest($student, $instructor, '10:00')->json('lesson.db_id');

        // Reschedule: still a road test, the site can change, durations of road tests apply
        $this->putJson("/lessons/{$id}", [
            'instructor_id' => $instructor->id, 'date' => $this->day->format('Y-m-d'), 'time' => '14:00',
            'duration_minutes' => 90, 'location' => 'DMV Brooklyn',
        ])->assertOk()->assertJsonPath('lesson.type', 'road_test')->assertJsonPath('lesson.location', 'DMV Brooklyn');

        $lesson = Lesson::find($id);
        $ics = str_replace("\r\n ", '', LessonCalendar::ics($lesson, 'student', 'ana@test.com'));
        $this->assertStringContainsString('LOCATION:DMV Brooklyn', $ics);
        $this->assertStringContainsString('ROAD TEST', $ics);
        $this->assertStringContainsString('maps.apple.com', $ics);

        $mail = (new LessonNotification($lesson, LessonNotification::SCHEDULED, 'student'))
            ->toMail(Notification::route('mail', 'ana@test.com'));
        $this->assertSame('Road Test agendado', substr($mail->subject, 0, 18));
        $lines = implode("\n", $mail->introLines);
        $this->assertStringContainsString('google.com/maps', $lines);
        $this->assertStringContainsString('maps.apple.com', $lines);

        $sms = (new LessonNotification($lesson, LessonNotification::REMINDER, 'student'))->toSms(new \Illuminate\Notifications\AnonymousNotifiable);
        $this->assertStringContainsString('Road Test', $sms);
        $this->assertStringContainsString('DMV Brooklyn', $sms);
    }

    // ---------------- Students, invoice, receipt ----------------

    public function test_students_can_share_an_email(): void
    {
        Package::create(['name' => 'Basic 5', 'lessons' => 5, 'price' => 180, 'is_active' => true]);
        $payload = [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'birth_date' => '2000-01-01', 'gender' => 'F',
            'address' => '1 Main St', 'city' => 'Queens', 'state' => 'NY', 'zip_code' => '11372',
            'phone' => '555-0100', 'email' => 'familia@test.com',
            'package' => 'Basic 5', 'transmission' => 'Automático', 'paid_amount' => 0,
        ];

        $this->postJson('/students', $payload)->assertOk();
        $id = $this->postJson('/students', ['first_name' => 'Luis'] + $payload)->assertOk()->json('student.db_id');
        $this->putJson("/students/{$id}", ['first_name' => 'Luis'] + $payload)->assertOk();

        $this->assertSame(2, Student::where('email', 'familia@test.com')->count());
    }

    public function test_invoice_shows_package_payments_and_balance(): void
    {
        $student = $this->makeStudent();            // price 300
        $student->update(['paid_amount' => 100, 'debt_amount' => 200]);  // 50 at enrollment + 50 payment
        $payment = StudentPayment::create([
            'student_id' => $student->id, 'amount' => 50, 'payment_method' => 'Zelle',
            'payment_date' => '2026-11-10', 'remaining_debt' => 200,
        ]);

        $this->get("/students/{$student->id}/invoice")->assertOk()
            ->assertSee('NY Driving Academy')
            ->assertSee('INV-' . str_pad($student->id, 4, '0', STR_PAD_LEFT))
            ->assertSee($student->package)
            ->assertSee('Pago inicial (inscripción)')
            ->assertSee('ABO-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('Balance:')
            ->assertSee('$200.00 USD', false)
            ->assertDontSee('School');

        $this->get("/payments/{$payment->id}/receipt")->assertOk()
            ->assertSee('NY Driving Academy')
            ->assertSee('Payment:')
            ->assertSee('Balance:')
            ->assertSee('#1976d2', false)
            ->assertDontSee('Saldo Restante')
            ->assertDontSee('Monto Abonado')
            ->assertDontSee('NY Driving School');
    }

    public function test_guests_cannot_see_invoices(): void
    {
        $student = $this->makeStudent();
        auth()->logout();

        $this->get("/students/{$student->id}/invoice")->assertRedirect('/login');
    }
}
