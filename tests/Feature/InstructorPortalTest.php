<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use App\Notifications\InstructorPortalLink;
use App\Notifications\LessonNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InstructorPortalTest extends TestCase
{
    use RefreshDatabase;

    private Instructor $instructor;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->instructor = $this->makeInstructor('ivan@test.com');
        $this->student = new Student([
            'first_name' => "Ana", 'last_name' => "O'Brien", 'address' => '6102 23rd Ave', 'city' => 'Brooklyn', 'state' => 'NY',
            'zip_code' => '11204', 'phone' => '347-555-0100', 'email' => 'ana@test.com', 'birth_date' => '2000-01-01', 'gender' => 'F',
            'transmission' => 'Automático', 'paid_amount' => 0, 'pickup_location' => 'Kings Plaza, Brooklyn',
        ]);
        $this->student->purchasePackage(Package::create(['name' => 'P5', 'lessons' => 5, 'price' => 300, 'is_active' => true]));
        $this->student->save();
    }

    private function makeInstructor(string $email): Instructor
    {
        return Instructor::create([
            'first_name' => 'Ivan', 'last_name' => 'Teacher', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '1', 'phone' => '555', 'email' => $email, 'status' => 'Disponible',
        ]);
    }

    /** Lesson starting $minutesFromNow minutes from now (school time) */
    private function lesson(int $minutesFromNow, array $attrs = [], ?Instructor $instructor = null): Lesson
    {
        $start = Carbon::now(config('app.school_timezone'))->addMinutes($minutesFromNow)->format('Y-m-d H:i:00');

        return Lesson::create($attrs + [
            'student_id' => $this->student->id, 'instructor_id' => ($instructor ?? $this->instructor)->id,
            'starts_at' => $start, 'ends_at' => Carbon::parse($start)->addMinutes(45), 'duration_minutes' => 45,
            'status' => Lesson::STATUS_SCHEDULED, 'type' => 'lesson',
        ]);
    }

    private function portal(): string
    {
        return $this->instructor->portalUrl();
    }

    public function test_instructor_sees_their_lessons_without_logging_in(): void
    {
        $this->lesson(-60);                                                 // started: to mark
        $this->lesson(24 * 60 * 2, ['type' => 'road_test', 'location' => 'DMV Red Hook']);
        $this->lesson(-60, [], $this->makeInstructor('other@test.com'));    // someone else's

        $html = $this->get($this->portal())->assertOk()
            ->assertSee('Mis clases')
            ->assertSee('Por marcar')
            ->assertSee('Kings Plaza, Brooklyn')                            // pickup location
            ->assertSee('DMV Red Hook')
            ->assertSee('maps.apple.com', false)
            ->assertSee('Completada')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'name="status" value="completed"'));
    }

    public function test_lessons_are_split_into_pending_completed_and_no_show_tabs(): void
    {
        $this->lesson(-60);
        $this->lesson(-24 * 60, ['status' => Lesson::STATUS_COMPLETED, 'notes' => 'nota-completada']);
        $this->lesson(-48 * 60, ['status' => Lesson::STATUS_NO_SHOW, 'notes' => 'nota-no-show']);
        $this->lesson(-90 * 24 * 60, ['status' => Lesson::STATUS_COMPLETED, 'notes' => 'nota-antigua']);   // old history too
        $url = $this->portal();

        $pending = $this->get($url)->assertOk()->assertDontSee('nota-completada')->assertDontSee('nota-no-show')->getContent();
        $this->assertSame(1, substr_count($pending, 'name="status" value="completed"'));

        $completed = $this->get("{$url}?tab=completadas")->assertOk()
            ->assertSee('nota-completada')->assertSee('nota-antigua')->assertDontSee('nota-no-show')->getContent();
        $this->assertSame(0, substr_count($completed, 'name="status"'));       // no forms: buttons disabled
        $this->assertSame(4, substr_count($completed, 'disabled'));

        $noShow = $this->get("{$url}?tab=no-asistio")->assertOk()
            ->assertSee('nota-no-show')->assertDontSee('nota-completada')->getContent();
        $this->assertSame(0, substr_count($noShow, 'name="status"'));
        $this->assertSame(2, substr_count($noShow, 'disabled'));

        $this->get("{$url}?tab=otra")->assertOk()->assertSee('Por marcar');   // unknown tab: pending
    }

    public function test_search_by_student_data_and_date(): void
    {
        $other = Student::create($this->student->only(['address', 'city', 'state', 'zip_code', 'birth_date', 'gender', 'transmission', 'paid_amount']) + [
            'first_name' => 'Pedro', 'last_name' => 'Gomez', 'phone' => '718-222-3333', 'email' => 'pedro@test.com',
        ]);
        $this->lesson(-60, ['notes' => 'clase-ana']);
        $this->lesson(-120, ['notes' => 'clase-pedro', 'student_id' => $other->id]);
        $this->lesson(-3 * 24 * 60, ['notes' => 'clase-vieja-pedro', 'student_id' => $other->id, 'status' => Lesson::STATUS_COMPLETED]);
        $url = $this->portal();

        $this->get("{$url}?q=pedro+gomez")->assertSee('clase-pedro')->assertDontSee('clase-ana');
        $this->get("{$url}?q=222-3333")->assertSee('clase-pedro')->assertDontSee('clase-ana');
        $this->get("{$url}?q=ana%40test")->assertSee('clase-ana')->assertDontSee('clase-pedro');
        $this->get("{$url}?q=nadie")->assertDontSee('clase-ana')->assertDontSee('clase-pedro')->assertSee('No hay clases');

        $old = Carbon::now(config('app.school_timezone'))->subDays(3)->format('Y-m-d');
        $this->get("{$url}?tab=completadas&date={$old}")->assertSee('clase-vieja-pedro');
        $this->get("{$url}?tab=completadas&q=ana")->assertDontSee('clase-vieja-pedro');
        $this->get("{$url}?date={$old}")->assertDontSee('clase-ana')->assertDontSee('clase-pedro');
        $this->get("{$url}?date=no-es-fecha")->assertOk()->assertSee('clase-ana');
    }

    public function test_instructor_marks_a_started_lesson_as_completed(): void
    {
        $lesson = $this->lesson(-50);
        $url = $this->portal();

        $this->from($url)->post("{$url}/lessons/{$lesson->id}", ['status' => 'completed'])
            ->assertRedirect($url)->assertSessionHas('ok');
        $this->assertSame(Lesson::STATUS_COMPLETED, $lesson->fresh()->status);

        // Can't be changed again from the link
        $this->from($url)->post("{$url}/lessons/{$lesson->id}", ['status' => 'no_show'])->assertSessionHas('error');
        $this->assertSame(Lesson::STATUS_COMPLETED, $lesson->fresh()->status);

        // No show
        $other = $this->lesson(-120);
        $this->from($url)->post("{$url}/lessons/{$other->id}", ['status' => 'no_show'])->assertSessionHas('ok');
        $this->assertSame(Lesson::STATUS_NO_SHOW, $other->fresh()->status);

        // Instructors can't cancel through the link
        $third = $this->lesson(-180);
        $this->from($url)->post("{$url}/lessons/{$third->id}", ['status' => 'cancelled'])->assertSessionHasErrors('status');
    }

    public function test_future_and_other_instructors_lessons_cannot_be_marked(): void
    {
        $url = $this->portal();
        $future = $this->lesson(120);
        $this->from($url)->post("{$url}/lessons/{$future->id}", ['status' => 'completed'])->assertSessionHas('error');
        $this->assertSame(Lesson::STATUS_SCHEDULED, $future->fresh()->status);

        $foreign = $this->lesson(-60, [], $this->makeInstructor('other@test.com'));
        $this->post("{$url}/lessons/{$foreign->id}", ['status' => 'completed'])->assertNotFound();
        $this->assertSame(Lesson::STATUS_SCHEDULED, $foreign->fresh()->status);
    }

    public function test_invalid_regenerated_or_suspended_links_do_not_work(): void
    {
        $this->get('/i/' . str_repeat('x', 48))->assertNotFound();
        $this->get('/i/short')->assertNotFound();

        $old = $this->portal();
        $this->instructor->portalUrl(true);
        $this->get($old)->assertNotFound();

        $this->instructor->update(['status' => 'Suspendido']);
        $this->get($this->instructor->portalUrl())->assertForbidden();
    }

    public function test_admin_gets_and_sends_the_link(): void
    {
        $this->postJson("/instructors/{$this->instructor->id}/portal-link")->assertUnauthorized();

        $this->actingAs(User::factory()->create());

        $url = $this->postJson("/instructors/{$this->instructor->id}/portal-link")->assertOk()->json('url');
        $this->assertStringContainsString('/i/', $url);
        $this->assertSame($url, $this->postJson("/instructors/{$this->instructor->id}/portal-link")->json('url'));   // stable
        Notification::assertNothingSent();

        $this->postJson("/instructors/{$this->instructor->id}/portal-link", ['send' => true])
            ->assertOk()->assertJsonPath('sent', ['correo']);
        Notification::assertSentOnDemand(InstructorPortalLink::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'ivan@test.com');

        $new = $this->postJson("/instructors/{$this->instructor->id}/portal-link", ['regenerate' => true])->json('url');
        $this->assertNotSame($url, $new);

        // The token never goes to the dashboard data
        $this->get('/driving')->assertOk()->assertDontSee(basename($new));
    }

    public function test_instructor_lesson_email_links_to_mis_clases(): void
    {
        $lesson = $this->lesson(24 * 60);
        $mail = (new LessonNotification($lesson, LessonNotification::SCHEDULED, 'instructor'))
            ->toMail(Notification::route('mail', 'ivan@test.com'));
        $this->assertStringContainsString($this->instructor->fresh()->portalUrl(), implode("\n", array_merge($mail->introLines, $mail->outroLines)));

        $studentMail = (new LessonNotification($lesson, LessonNotification::SCHEDULED, 'student'))
            ->toMail(Notification::route('mail', 'ana@test.com'));
        $this->assertStringNotContainsString('/i/', implode("\n", array_merge($studentMail->introLines, $studentMail->outroLines)));
    }
}
