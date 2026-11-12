<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->tomorrow = Carbon::now(config('app.school_timezone'))->addDay()->format('Y-m-d');
    }

    private function pkg(int $lessons, float $price = 100): Package
    {
        static $n = 0;
        $n++;

        return Package::create(['name' => "Test package {$n}", 'lessons' => $lessons, 'price' => $price, 'is_active' => true]);
    }

    private function makeStudent(?Package $package = null, float $paid = 0): Student
    {
        static $n = 0;
        $n++;

        $student = new Student([
            'first_name' => 'Ana' . $n, 'last_name' => 'Test', 'address' => 'x', 'city' => 'NY', 'state' => 'NY',
            'zip_code' => '10001', 'phone' => '555', 'email' => "ana{$n}@test.com",
            'package' => 'Importado', 'transmission' => 'Automático', 'paid_amount' => $paid,
        ]);
        if ($package) {
            $student->purchasePackage($package);
        }
        $student->save();

        return $student;
    }

    private function makeInstructor(): Instructor
    {
        static $n = 0;
        $n++;

        return Instructor::create([
            'first_name' => 'Ivan' . $n, 'last_name' => 'Teacher', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '10001', 'phone' => '555',
            'email' => "ivan{$n}@test.com",
        ]);
    }

    private function book(Student $s, Instructor $i, string $time = '10:00', int $minutes = 45, ?string $date = null)
    {
        return $this->postJson('/lessons', [
            'student_id' => $s->id, 'instructor_id' => $i->id,
            'date' => $date ?? $this->tomorrow, 'time' => $time, 'duration_minutes' => $minutes,
        ]);
    }

    private function reschedule(int $lessonId, Instructor $i, string $time, int $minutes = 45, ?string $date = null)
    {
        return $this->putJson("/lessons/{$lessonId}", [
            'instructor_id' => $i->id, 'date' => $date ?? $this->tomorrow, 'time' => $time,
            'duration_minutes' => $minutes, 'notes' => 'Reprogramada',
        ]);
    }

    public function test_school_catalog_is_seeded_inactive_until_priced(): void
    {
        $this->assertSame(12, Package::where('lessons', '>', 0)->where('name', 'PRINCIPIANTE package 12 Lessons')->value('lessons'));
        $this->assertSame(0, Package::where('is_active', true)->count());
    }

    public function test_lesson_is_booked_in_45_minute_blocks(): void
    {
        $student = $this->makeStudent($this->pkg(5));
        $instructor = $this->makeInstructor();

        $this->book($student, $instructor, '10:00', 90)
            ->assertOk()
            ->assertJsonPath('lesson.time', '10:00')
            ->assertJsonPath('lesson.end_time', '11:30')
            ->assertJsonPath('student.used_minutes', 90);

        // Durations that are not multiples of a 45-minute lesson are rejected
        $this->book($student, $instructor, '14:00', 60)->assertStatus(422);
    }

    public function test_student_without_package_cannot_book(): void
    {
        $this->book($this->makeStudent(), $this->makeInstructor())->assertStatus(422);
        $this->assertDatabaseCount('lessons', 0);
    }

    public function test_package_without_driving_lessons_cannot_book(): void
    {
        $this->book($this->makeStudent($this->pkg(0)), $this->makeInstructor())
            ->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'no incluye clases de manejo'));
    }

    public function test_cannot_exceed_package_lessons(): void
    {
        $student = $this->makeStudent($this->pkg(3)); // 3 x 45 = 135 min
        $instructor = $this->makeInstructor();

        $this->book($student, $instructor, '08:00', 90)->assertOk();
        $this->book($student, $instructor, '10:00', 90)
            ->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Queda 1 clase'));
        $this->book($student, $instructor, '10:00', 45)->assertOk();
        $this->book($student, $instructor, '12:00', 45)->assertStatus(422);
    }

    public function test_catalog_changes_do_not_affect_students_who_already_bought(): void
    {
        $package = $this->pkg(3, 150);
        $student = $this->makeStudent($package);

        // The school changes the package afterwards
        $this->putJson("/packages/{$package->id}", [
            'name' => 'Renamed', 'description' => null, 'lessons' => 1, 'price' => 999, 'is_active' => true,
        ])->assertOk();

        $student->refresh();
        $this->assertSame(3, (int) $student->package_lessons);
        $this->assertSame(150.0, (float) $student->plan_cost);

        // Still entitled to the 3 lessons bought
        $instructor = $this->makeInstructor();
        $this->book($student, $instructor, '08:00', 135)->assertOk();

        // A new buyer gets the new conditions
        $newBuyer = $this->makeStudent($package->fresh());
        $this->assertSame(1, (int) $newBuyer->package_lessons);
        $this->assertSame(999.0, (float) $newBuyer->plan_cost);
    }

    public function test_instructor_and_student_cannot_be_double_booked(): void
    {
        $ana = $this->makeStudent($this->pkg(10));
        $bob = $this->makeStudent($this->pkg(10));
        $ivan = $this->makeInstructor();
        $other = $this->makeInstructor();

        $this->book($ana, $ivan, '10:00', 45)->assertOk();

        $this->book($bob, $ivan, '10:30', 45)->assertStatus(422);   // instructor busy
        $this->book($ana, $other, '10:30', 45)->assertStatus(422);  // student busy
        $this->book($bob, $ivan, '10:45', 45)->assertOk();          // back-to-back is fine
    }

    public function test_cannot_book_in_the_past(): void
    {
        $yesterday = Carbon::now(config('app.school_timezone'))->subDay()->format('Y-m-d');

        $this->book($this->makeStudent($this->pkg(5)), $this->makeInstructor(), '10:00', 45, $yesterday)
            ->assertStatus(422);
    }

    public function test_cancelling_frees_lessons_and_slot(): void
    {
        $student = $this->makeStudent($this->pkg(2));
        $instructor = $this->makeInstructor();

        $id = $this->book($student, $instructor, '10:00', 90)->json('lesson.db_id');

        $this->putJson("/lessons/{$id}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('student.used_minutes', 0);

        $this->book($student, $instructor, '10:00', 90)->assertOk();
        $this->putJson("/lessons/{$id}/status", ['status' => 'completed'])->assertStatus(422);
    }

    public function test_completed_and_no_show_lessons_keep_using_lessons(): void
    {
        $student = $this->makeStudent($this->pkg(5));
        $instructor = $this->makeInstructor();

        $a = $this->book($student, $instructor, '08:00', 45)->json('lesson.db_id');
        $b = $this->book($student, $instructor, '09:00', 45)->json('lesson.db_id');

        $this->putJson("/lessons/{$a}/status", ['status' => 'completed'])->assertJsonPath('student.used_minutes', 90);
        $this->putJson("/lessons/{$b}/status", ['status' => 'no_show'])->assertJsonPath('student.used_minutes', 90);
    }

    public function test_lesson_can_be_rescheduled(): void
    {
        $student = $this->makeStudent($this->pkg(5));
        $ivan = $this->makeInstructor();
        $other = $this->makeInstructor();
        $id = $this->book($student, $ivan, '10:00', 45)->json('lesson.db_id');

        $this->reschedule($id, $other, '10:30', 90)
            ->assertOk()
            ->assertJsonPath('lesson.time', '10:30')
            ->assertJsonPath('lesson.end_time', '12:00')
            ->assertJsonPath('lesson.instructor_id', $other->id)
            ->assertJsonPath('lesson.notes', 'Reprogramada')
            ->assertJsonPath('student.used_minutes', 90);

        $this->assertDatabaseCount('lessons', 1);
    }

    public function test_reschedule_respects_conflicts_lessons_and_status(): void
    {
        $ana = $this->makeStudent($this->pkg(4)); // 180 min
        $bob = $this->makeStudent($this->pkg(4));
        $ivan = $this->makeInstructor();

        $a1 = $this->book($ana, $ivan, '08:00', 90)->json('lesson.db_id');
        $this->book($ana, $ivan, '10:00', 90)->assertOk();     // package now fully booked
        $this->book($bob, $ivan, '14:00', 45)->assertOk();

        $this->reschedule($a1, $ivan, '14:30', 45)->assertStatus(422);  // instructor busy with Bob
        $this->reschedule($a1, $ivan, '16:00', 90)->assertOk();         // its own lessons count as available
        $this->reschedule($a1, $ivan, '16:00', 135)->assertStatus(422); // more than bought

        $this->putJson("/lessons/{$a1}/status", ['status' => 'completed'])->assertOk();
        $this->reschedule($a1, $ivan, '18:00', 45)->assertStatus(422);  // not scheduled anymore
    }

    public function test_package_can_be_assigned_to_student_without_one(): void
    {
        $student = $this->makeStudent(null, 100);
        $package = $this->pkg(12, 350);

        $this->putJson("/students/{$student->id}/package", ['package_id' => $package->id])
            ->assertOk()
            ->assertJsonPath('student.package_id', $package->id)
            ->assertJsonPath('student.package_lessons', 12)
            ->assertJsonPath('student.cost', 350)
            ->assertJsonPath('student.debt', 250); // previous payment is kept

        $this->putJson("/students/{$student->id}/package", ['package_id' => $package->id])->assertStatus(422);
        $this->book($student->fresh(), $this->makeInstructor())->assertOk();
    }

    public function test_inactive_package_cannot_be_assigned(): void
    {
        $inactive = Package::create(['name' => 'Old', 'lessons' => 5, 'price' => 50, 'is_active' => false]);

        $this->putJson("/students/{$this->makeStudent()->id}/package", ['package_id' => $inactive->id])->assertStatus(422);
    }

    public function test_dashboard_includes_packages_and_lessons(): void
    {
        $student = $this->makeStudent($this->pkg(5));
        $this->book($student, $this->makeInstructor(), '10:00', 45)->assertOk();

        $this->get('/driving')->assertOk()
            ->assertSee('view-agenda', false)
            ->assertSee('view-paquetes', false)
            ->assertSee('CLS-0001');
    }
}
