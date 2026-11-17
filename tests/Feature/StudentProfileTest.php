<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'Angela']);
        $this->actingAs($this->user);
        Package::create(['name' => 'INTERMEDIO', 'lessons' => 7, 'price' => 530, 'is_active' => true]);
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'first_name' => 'Michael', 'middle_name' => 'Joel', 'last_name' => 'Baque Cevallos',
            'birth_date' => '2001-11-29', 'gender' => 'M',
            'address' => '6102 23rd Ave', 'city' => 'Brooklyn', 'state' => 'NY', 'zip_code' => '11204',
            'phone' => '347-969-8128', 'email' => 'michael@test.com',
            'package' => 'INTERMEDIO', 'transmission' => 'Automático', 'paid_amount' => 0,
        ];
    }

    public function test_extra_profile_fields_are_saved_and_returned(): void
    {
        $instructor = Instructor::create([
            'first_name' => 'Ivan', 'last_name' => 'T', 'birth_date' => '1980-01-01', 'gender' => 'M',
            'address' => 'x', 'city' => 'NY', 'state' => 'NY', 'zip_code' => '1', 'phone' => '1', 'email' => 'i@test.com',
        ]);

        $extra = [
            'suffix' => 'Jr', 'apt' => '1R', 'other_phone' => '718-555-0000', 'other_phone_desc' => 'Mamá',
            'secondary_email' => 'mike2@test.com', 'parent_name' => 'Rosa Cevallos', 'parent_phone' => '718-555-1111',
            'high_school' => 'Brooklyn High', 'restrictions' => 'Eye glasses', 'endorsements' => 'None',
            'certificate_number' => 'N155750', 'certificate_expiry' => '2027-05-15',
            'pickup_location' => '6102 23rd Ave, Brooklyn, NY', 'preferred_instructor_id' => $instructor->id,
            'is_problematic' => true,
        ];

        $res = $this->postJson('/students', $this->payload($extra))->assertOk();
        foreach ($extra as $key => $value) {
            $this->assertSame($value, $res->json("student.$key"), $key);
        }
        $this->assertSame([], $res->json('student.notes'));

        // Update clears what is not sent
        $id = $res->json('student.db_id');
        $this->putJson("/students/{$id}", $this->payload(['suffix' => 'Sr']))->assertOk()
            ->assertJsonPath('student.suffix', 'Sr')
            ->assertJsonPath('student.parent_name', null)
            ->assertJsonPath('student.preferred_instructor_id', null)
            ->assertJsonPath('student.is_problematic', false);

        $this->postJson('/students', $this->payload(['secondary_email' => 'nope', 'preferred_instructor_id' => 999]))
            ->assertJsonValidationErrors(['secondary_email', 'preferred_instructor_id']);
    }

    public function test_notes_can_be_added_listed_and_deleted(): void
    {
        $id = $this->postJson('/students', $this->payload())->json('student.db_id');

        $this->postJson("/students/{$id}/notes", ['body' => 'Primera nota'])->assertOk();
        $notes = $this->postJson("/students/{$id}/notes", ['body' => 'Llamé por el Road Test'])->assertOk()->json('notes');

        $this->assertCount(2, $notes);
        $this->assertSame('Llamé por el Road Test', $notes[0]['body']);   // newest first
        $this->assertSame('Angela', $notes[0]['author']);

        // The dashboard data includes the notes
        $this->get('/driving')->assertOk()->assertSee('por el Road Test');

        $this->deleteJson("/students/{$id}/notes/{$notes[0]['db_id']}")->assertOk()->assertJsonCount(1, 'notes');

        $this->postJson("/students/{$id}/notes", ['body' => ''])->assertJsonValidationErrors('body');

        // A note of another student can't be deleted through this one
        $other = $this->postJson('/students', $this->payload(['first_name' => 'Ana']))->json('student.db_id');
        $this->deleteJson("/students/{$other}/notes/{$notes[1]['db_id']}")->assertNotFound();
        $this->assertSame(1, Student::find($id)->notes()->count());
    }

    public function test_guests_cannot_add_notes(): void
    {
        $id = $this->postJson('/students', $this->payload())->json('student.db_id');
        auth()->logout();

        $this->postJson("/students/{$id}/notes", ['body' => 'x'])->assertUnauthorized();
    }
}
