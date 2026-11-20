<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use App\Notifications\LessonNotification;
use App\Services\LessonNotifier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstructor(string $language = 'es'): Instructor
    {
        return Instructor::create([
            'first_name' => 'Ivan', 'last_name' => 'Teacher', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '10001', 'phone' => '646-555-0199',
            'email' => uniqid() . '@test.com', 'status' => 'Disponible', 'language' => $language,
        ]);
    }

    private function makeStudent(string $language = 'es'): Student
    {
        $student = new Student([
            'first_name' => 'Ana', 'last_name' => 'Test', 'address' => 'x', 'city' => 'NY', 'state' => 'NY',
            'zip_code' => '10001', 'phone' => '212-555-0100', 'email' => uniqid() . '@test.com',
            'transmission' => 'Automático', 'paid_amount' => 0, 'language' => $language,
        ]);
        $student->purchasePackage(Package::create(['name' => uniqid(), 'lessons' => 5, 'price' => 100, 'is_active' => true]));
        $student->save();

        return $student;
    }

    public function test_spanish_is_the_default_and_the_switch_remembers_english(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/driving')->assertOk()->assertSee('window.APP_LOCALE = "es"', false);

        $this->from('/driving')->get('/language/en')
            ->assertRedirect('/driving')
            ->assertCookie('nyds-lang', 'en')
            ->assertSessionHas('locale', 'en');

        $this->get('/driving')->assertOk()->assertSee('window.APP_LOCALE = "en"', false);

        // The cookie alone keeps the language (e.g. after logging out)
        $this->flushSession();
        $this->withCookie('nyds-lang', 'en')->get('/driving')->assertOk()->assertSee('window.APP_LOCALE = "en"', false);

        $this->get('/language/fr')->assertNotFound();
    }

    public function test_login_page_has_the_switch_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee("setLanguage('en')", false);
        $this->get('/language/en')->assertRedirect();
        $this->get('/login')->assertSee("setLanguage('es')", false);
    }

    public function test_notifications_use_each_persons_preferred_language(): void
    {
        Notification::fake();
        $start = Carbon::now(config('app.school_timezone'))->addDay()->setTime(10, 0);
        $lesson = Lesson::create([
            'student_id' => $this->makeStudent('en')->id, 'instructor_id' => $this->makeInstructor('es')->id,
            'starts_at' => $start, 'ends_at' => $start->copy()->addMinutes(45), 'duration_minutes' => 45,
            'status' => Lesson::STATUS_SCHEDULED, 'type' => 'lesson',
        ]);

        // The admin uses the panel in Spanish: the student still gets English
        app(LessonNotifier::class)->notify($lesson, LessonNotification::SCHEDULED);

        Notification::assertSentOnDemand(LessonNotification::class, fn ($n, $c, $to) => $to->routes['mail'] === $lesson->student->email && $n->locale === 'en');
        Notification::assertSentOnDemand(LessonNotification::class, fn ($n, $c, $to) => $to->routes['mail'] === $lesson->instructor->email && $n->locale === 'es');
    }

    public function test_language_is_saved_for_instructors_and_shown_in_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());
        $this->makeInstructor('en');
        $this->makeStudent('en');

        $html = $this->get('/driving')->assertOk()->getContent();
        $this->assertStringContainsString('"language":"en"', $html);
        $this->assertSame('es', Student::create([
            'first_name' => 'B', 'last_name' => 'C', 'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '1',
            'phone' => '1', 'email' => 'b@c.com', 'paid_amount' => 0, 'transmission' => 'Automático',
        ])->fresh()->language);   // default
    }

    public function test_mis_clases_uses_the_instructors_language_unless_one_was_picked(): void
    {
        $instructor = $this->makeInstructor('en');
        $url = $instructor->portalUrl();

        $this->get($url)->assertOk()->assertSee('window.APP_LOCALE = "en"', false);

        $this->get('/language/es');
        $this->get($url)->assertOk()->assertSee('window.APP_LOCALE = "es"', false);
    }
}
